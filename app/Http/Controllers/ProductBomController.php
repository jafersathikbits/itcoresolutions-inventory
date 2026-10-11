<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Material;
use App\Services\OrderStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBomController extends Controller
{
    public function index(Product $product)
    {
        $product->load(['materials.unit', 'materials.components.childMaterial.unit']);

        $allMaterials = Material::with('unit')
            ->orderBy('name')
            ->get();

        // Expanded requirements preview (flatten composite children into raw list)
        // Output format: material_id => ['name'=>..., 'unit'=>..., 'qty'=>...]
        $expanded = $this->computeExpandedRequirements($product);

        return view('products.bom.index', [
            'product' => $product,
            'allMaterials' => $allMaterials,
            'expanded' => $expanded,
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'material_id' => ['required', 'integer', 'exists:materials,id'],
            'qty_per_product' => ['required', 'numeric', 'gt:0'],
        ]);

        DB::transaction(function () use ($product, $data) {
            // Upsert pivot row
            $product->materials()->syncWithoutDetaching([
                (int)$data['material_id'] => ['qty_per_product' => $data['qty_per_product']],
            ]);
        });

        return redirect()->route('products.bom.index', $product)->with('success', 'BOM material saved successfully.');
    }

    public function destroy(Product $product, $materialId)
    {
        $product->materials()->detach((int)$materialId);

        return redirect()->route('products.bom.index', $product)->with('success', 'BOM material removed successfully.');
    }

    /**
     * Expand BOM for composite materials into raw materials (for 1 product unit).
     * Re-uses the same logic the order placement uses, so the preview always matches
     * what will actually be deducted.
     */
    private function computeExpandedRequirements(Product $product): array
    {
        $expanded = [];

        foreach (app(OrderStockService::class)->computeRequirements($product, 1) as $id => $row) {
            $expanded[$id] = [
                'id' => $id,
                'name' => $row['material']->name,
                'unit' => $row['material']->unit?->symbol ?? '',
                'qty' => round($row['required'], 3),
            ];
        }

        // sort by name for display
        uasort($expanded, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $expanded;
    }
}
