<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::withCount('materials')->orderBy('name')->get();

        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:units,name'],
            'symbol' => ['required', 'string', 'max:30', 'unique:units,symbol'],
            'allow_decimal' => ['nullable', 'boolean'],
        ]);

        $data['allow_decimal'] = (bool) ($data['allow_decimal'] ?? false);

        Unit::create($data);

        return redirect()->route('units.index')->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:units,name,' . $unit->id],
            'symbol' => ['required', 'string', 'max:30', 'unique:units,symbol,' . $unit->id],
            'allow_decimal' => ['nullable', 'boolean'],
        ]);

        $data['allow_decimal'] = (bool) ($data['allow_decimal'] ?? false);

        $unit->update($data);

        return redirect()->route('units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        // If any materials use this unit, restrict by DB (restrictOnDelete).
        try {
            $unit->delete();
            return redirect()->route('units.index')->with('success', 'Unit deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('units.index')->with('error', 'Cannot delete this unit because it is in use by materials.');
        }
    }
}
