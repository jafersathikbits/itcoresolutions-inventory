<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialComponent;
use Illuminate\Http\Request;

class MaterialComponentController extends Controller
{
    public function index(Material $material)
    {
        // Only composites should be managed here
        if (!$material->is_composite) {
            return redirect()->route('materials.show', $material)
                ->with('error', 'This material is not marked as composite. Enable "Composite" first.');
        }

        $material->load([
            'unit',
            'components.childMaterial.unit',
            'components.childMaterial.components.childMaterial.unit', // for nested display if needed
        ]);

        // Children options: do not allow selecting the same as parent, and avoid composite children for safety
        $childOptions = Material::with('unit')
            ->where('id', '!=', $material->id)
            ->where('is_composite', false)
            ->orderBy('name')
            ->get();

        return view('materials.components.index', [
            'material' => $material,
            'childOptions' => $childOptions,
        ]);
    }

    public function store(Request $request, Material $material)
    {
        if (!$material->is_composite) {
            return redirect()->route('materials.show', $material)
                ->with('error', 'This material is not marked as composite. Enable "Composite" first.');
        }

        $data = $request->validate([
            'child_material_id' => ['required', 'integer', 'exists:materials,id'],
            'qty_per_parent' => ['required', 'numeric', 'gt:0'],
        ]);

        if ((int)$data['child_material_id'] === (int)$material->id) {
            return back()->with('error', 'Child material cannot be the same as parent.');
        }

        $child = Material::findOrFail($data['child_material_id']);

        // Safety: do not allow composite as child in this version (avoids deep cycles)
        if ($child->is_composite) {
            return back()->with('error', 'Composite child materials are not allowed. Choose a raw material.');
        }

        // Upsert (if exists, update qty)
        MaterialComponent::updateOrCreate(
            [
                'parent_material_id' => $material->id,
                'child_material_id' => $child->id,
            ],
            [
                'qty_per_parent' => $data['qty_per_parent'],
            ]
        );

        return redirect()->route('materials.components.index', $material)
            ->with('success', 'Component saved successfully.');
    }

    public function destroy(Material $material, MaterialComponent $component)
    {
        // Ensure the component belongs to the parent material (avoid deleting others)
        if ((int)$component->parent_material_id !== (int)$material->id) {
            return redirect()->route('materials.components.index', $material)
                ->with('error', 'Invalid component.');
        }

        $component->delete();

        return redirect()->route('materials.components.index', $material)
            ->with('success', 'Component removed successfully.');
    }
}
