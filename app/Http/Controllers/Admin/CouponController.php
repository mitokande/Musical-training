<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'             => 'required|string|max:50|unique:coupons',
            'description'      => 'nullable|string',
            'type'             => 'required|string|in:percentage,fixed',
            'value'            => 'required|numeric|min:0',
            'max_uses'         => 'nullable|integer|min:1',
            'applicable_plans' => 'nullable|array',
            'applicable_roles' => 'nullable|array',
            'starts_at'        => 'nullable|date',
            'expires_at'       => 'nullable|date|after:starts_at',
            'is_active'        => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Coupon::create($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    public function show(Coupon $coupon)
    {
        return view('admin.coupons.show', compact('coupon'));
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code'             => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'description'      => 'nullable|string',
            'type'             => 'required|string|in:percentage,fixed',
            'value'            => 'required|numeric|min:0',
            'max_uses'         => 'nullable|integer|min:1',
            'applicable_plans' => 'nullable|array',
            'applicable_roles' => 'nullable|array',
            'starts_at'        => 'nullable|date',
            'expires_at'       => 'nullable|date|after:starts_at',
            'is_active'        => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully.');
    }
}
