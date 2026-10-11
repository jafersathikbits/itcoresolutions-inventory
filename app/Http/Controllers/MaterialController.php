<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\StockLog;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::with('unit')->orderBy('name');

        $search = trim((string) $request->get('q', ''));
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->get('filter') === 'low') {
            // low-stock = stock <= threshold
            $query->whereColumn('stock', '<=', 'threshold');
        }

        $materials = $query->paginate(15)->withQueryString();

        $lowCount = Material::whereColumn('stock', '<=', 'threshold')->count();

        return view('materials.index', compact('materials', 'lowCount', 'search'));
    }

    public function create()
    {
        $units = Unit::orderBy('name')->get();

        return view('materials.create', compact('units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:materials,name'],
            'unit_id' => ['required', 'exists:units,id'],
            'stock' => ['required', 'numeric', 'min:0'],
            'threshold' => ['required', 'numeric', 'min:0'],
            'is_composite' => ['nullable', 'boolean'],

            // Additional client requirement: images must be uploaded
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data['is_composite'] = (bool) ($data['is_composite'] ?? false);

        // Save image
        $path = $request->file('image')->store('materials', 'public');
        $data['image_path'] = $path;
        unset($data['image']);

        Material::create($data);

        return redirect()->route('materials.index')->with('success', 'Material created successfully.');
    }

    public function show(Material $material)
    {
        $material->load(['unit', 'components.childMaterial.unit']);

        // Recent stock movements (manual adds + order deductions)
        $stockLogs = $material->stockLogs()
            ->with(['user', 'order'])
            ->latest()
            ->limit(15)
            ->get();

        return view('materials.show', compact('material', 'stockLogs'));
    }

    public function edit(Material $material)
    {
        $units = Unit::orderBy('name')->get();

        return view('materials.edit', compact('material', 'units'));
    }

    public function update(Request $request, Material $material)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:materials,name,' . $material->id],
            'unit_id' => ['required', 'exists:units,id'],
            'stock' => ['required', 'numeric', 'min:0'],
            'threshold' => ['required', 'numeric', 'min:0'],
            'is_composite' => ['nullable', 'boolean'],

            // update image optional
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data['is_composite'] = (bool) ($data['is_composite'] ?? false);

        // A material that is already a component of another composite cannot itself become
        // composite: stock calculation only expands one level (composite -> raw).
        if ($data['is_composite'] && !$material->is_composite && $material->usedInComposites()->exists()) {
            return back()->withInput()->withErrors([
                'is_composite' => 'This material is used as a component of another composite, so it cannot be marked as composite.',
            ]);
        }

        // Replace image if provided (old file is removed only after the new one is saved)
        $oldImage = null;
        if ($request->hasFile('image')) {
            $oldImage = $material->image_path;
            $data['image_path'] = $request->file('image')->store('materials', 'public');
        }
        unset($data['image']);

        $beforeStock = (float) $material->stock;

        DB::transaction(function () use ($material, $data, $beforeStock, $request) {
            $material->update($data);

            // Direct stock edits are recorded as an 'adjust' entry so stock history stays complete
            $afterStock = (float) $material->stock;
            if (round($afterStock - $beforeStock, 3) != 0.0) {
                StockLog::create([
                    'material_id' => $material->id,
                    'user_id' => $request->user()->id,
                    'type' => 'adjust',
                    'qty' => round(abs($afterStock - $beforeStock), 3),
                    'before_stock' => $beforeStock,
                    'after_stock' => $afterStock,
                    'order_id' => null,
                    'reason' => 'Stock edited on material form',
                ]);
            }
        });

        if ($oldImage && Storage::disk('public')->exists($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('materials.show', $material)->with('success', 'Material updated successfully.');
    }

    public function destroy(Material $material)
    {
        try {
            $imagePath = $material->image_path;

            // delete the row first: if it is still referenced (FK restrict) we keep the image
            $material->delete();

            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return redirect()->route('materials.index')->with('success', 'Material deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('materials.index')->with('error', 'Cannot delete this material because it is referenced by products/composites/orders.');
        }
    }
}
