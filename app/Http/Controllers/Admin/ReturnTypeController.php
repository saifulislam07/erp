<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReturnTypeRequest;
use App\Models\ReturnType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReturnTypeController extends Controller
{
    public function index(): View
    {
        $returnTypes = ReturnType::withCount('returns')->orderBy('name')->get();

        return view('admin.return-types.index', compact('returnTypes'));
    }

    public function create(): View
    {
        return view('admin.return-types.create');
    }

    public function store(ReturnTypeRequest $request): RedirectResponse
    {
        ReturnType::create($request->validated());

        return redirect()->route('admin.return-types.index')->with('success', 'Return type created successfully.');
    }

    public function edit(ReturnType $returnType): View
    {
        return view('admin.return-types.edit', compact('returnType'));
    }

    public function update(ReturnTypeRequest $request, ReturnType $returnType): RedirectResponse
    {
        $returnType->update($request->validated());

        return redirect()->route('admin.return-types.index')->with('success', 'Return type updated successfully.');
    }

    public function destroy(ReturnType $returnType): RedirectResponse
    {
        if ($returnType->returns()->exists()) {
            return redirect()->route('admin.return-types.index')->with('error', 'Cannot delete a return type that has been used.');
        }

        $returnType->delete();

        return redirect()->route('admin.return-types.index')->with('success', 'Return type deleted successfully.');
    }
}
