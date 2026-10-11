<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Product;
use App\Models\StockLog;

class OrderStockService
{
    /**
     * Expand BOM into RAW material requirements per product quantity.
     * Current implementation supports composite -> raw children (1 level).
     *
     * Returns:
     * [
     *   material_id => ['material' => Material, 'required' => float]
     * ]
     */
    public function computeRequirements(Product $product, float $orderQty): array
    {
        $product->loadMissing(['materials.unit', 'materials.components.childMaterial.unit']);

        $req = [];

        foreach ($product->materials as $bomMaterial) {
            $qtyPerProduct = (float) $bomMaterial->pivot->qty_per_product;
            $neededForOrder = $qtyPerProduct * $orderQty;

            if (!$bomMaterial->is_composite) {
                $this->accumulate($req, $bomMaterial, $neededForOrder);
                continue;
            }

            // Composite expands into children
            foreach ($bomMaterial->components as $comp) {
                $child = $comp->childMaterial;
                if (!$child) continue;

                $childNeeded = $neededForOrder * (float) $comp->qty_per_parent;
                $this->accumulate($req, $child, $childNeeded);
            }
        }

        return $req;
    }

    private function accumulate(array &$req, Material $material, float $qty): void
    {
        if (!isset($req[$material->id])) {
            $req[$material->id] = [
                'material' => $material,
                'required' => 0.0,
            ];
        }

        $req[$material->id]['required'] = round(((float)$req[$material->id]['required']) + $qty, 3);
    }

    /**
     * Check stock based on rules:
     * - block if any insufficient (stock < required)
     * - warn if sufficient but afterStock <= threshold for any item
     * - success if all afterStock > threshold
     *
     * Returns:
     * [
     *   'status' => 'success'|'warning'|'blocked',
     *   'insufficient' => [ ... ],
     *   'low_after' => [ ... ],
     *   'after_map' => [material_id => afterStock]
     * ]
     */
    public function checkStock(array $requirements, bool $lockForUpdate = false): array
    {
        $insufficient = [];
        $lowAfter = [];
        $afterMap = [];

        // Refresh materials from DB to ensure latest stock.
        // When called inside a transaction with $lockForUpdate, the rows stay locked
        // until commit so two simultaneous orders cannot both pass the check.
        $materialIds = array_keys($requirements);
        $query = Material::with('unit')->whereIn('id', $materialIds);
        if ($lockForUpdate) {
            $query->lockForUpdate();
        }
        $materials = $query->get()->keyBy('id');

        foreach ($requirements as $materialId => $row) {
            /** @var Material $m */
            $m = $materials[$materialId] ?? null;
            if (!$m) continue;

            $required = (float) $row['required'];
            $stock = (float) $m->stock;
            $threshold = (float) $m->threshold;

            if ($stock < $required) {
                $insufficient[] = [
                    'material_id' => $m->id,
                    'name' => $m->name,
                    'unit' => $m->unit?->symbol ?? '',
                    'required' => round($required, 3),
                    'stock' => round($stock, 3),
                    'short_by' => round($required - $stock, 3),
                ];
                continue;
            }

            $after = round($stock - $required, 3);
            $afterMap[$m->id] = $after;

            if ($after <= $threshold) {
                $lowAfter[] = [
                    'material_id' => $m->id,
                    'name' => $m->name,
                    'unit' => $m->unit?->symbol ?? '',
                    'after_stock' => $after,
                    'threshold' => round($threshold, 3),
                ];
            }
        }

        $status = match (true) {
            count($insufficient) > 0 => 'blocked',
            count($lowAfter) > 0 => 'warning',
            default => 'success',
        };

        return [
            'status' => $status,
            'insufficient' => $insufficient,
            'low_after' => $lowAfter,
            'after_map' => $afterMap,
        ];
    }

    /**
     * Deduct stock and create stock logs (transaction handled outside).
     */
    public function deductStock(array $requirements, int $userId, ?int $orderId = null, string $reason = 'Order placed'): void
    {
        $materialIds = array_keys($requirements);

        // Lock rows for update to avoid race
        $materials = Material::whereIn('id', $materialIds)->lockForUpdate()->get()->keyBy('id');

        foreach ($requirements as $materialId => $row) {
            /** @var Material $m */
            $m = $materials[$materialId] ?? null;
            if (!$m) continue;

            $required = (float) $row['required'];
            $before = (float) $m->stock;
            $after = round($before - $required, 3);

            // Safety
            if ($after < 0) {
                $after = 0;
            }

            $m->stock = $after;
            $m->save();

            StockLog::create([
                'material_id' => $m->id,
                'user_id' => $userId,
                'type' => 'deduct',
                'qty' => round($required, 3),
                'before_stock' => round($before, 3),
                'after_stock' => round($after, 3),
                'order_id' => $orderId,
                'reason' => $reason,
            ]);
        }
    }
}
