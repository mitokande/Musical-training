<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get()->groupBy('role');

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'slug'          => 'required|string|max:255|unique:plans',
            'description'   => 'nullable|string',
            'role'          => 'required|string|in:user,teacher,school',
            'type'          => 'required|string',
            'price_monthly' => 'nullable|numeric|min:0',
            'price_yearly'  => 'nullable|numeric|min:0',
            'currency'      => 'required|string|max:3',
            'features'      => 'nullable|array',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Plan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function show(Plan $plan)
    {
        return view('admin.plans.show', compact('plan'));
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'slug'          => 'required|string|max:255|unique:plans,slug,' . $plan->id,
            'description'   => 'nullable|string',
            'role'          => 'required|string|in:user,teacher,school',
            'type'          => 'required|string',
            'price_monthly' => 'nullable|numeric|min:0',
            'price_yearly'  => 'nullable|numeric|min:0',
            'currency'      => 'required|string|max:3',
            'features'      => 'nullable|array',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}
