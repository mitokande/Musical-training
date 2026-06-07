<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $subscriptions = Subscription::with(['user', 'plan'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->plan_id, fn($q, $planId) => $q->where('plan_id', $planId))
            ->when($request->search, function ($q, $search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);

        $plans = Plan::where('is_active', true)->get();

        return view('admin.subscriptions.index', compact('subscriptions', 'plans'));
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['user', 'plan', 'invoices']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        $subscription->load(['user', 'plan']);
        $plans = Plan::where('is_active', true)->get();

        return view('admin.subscriptions.edit', compact('subscription', 'plans'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'plan_id'      => 'required|exists:plans,id',
            'status'       => 'required|string|in:active,cancelled,expired,paused,trial',
            'starts_at'    => 'required|date',
            'ends_at'      => 'nullable|date|after:starts_at',
            'cancelled_at' => 'nullable|date',
            'trial_ends_at'=> 'nullable|date',
        ]);

        $subscription->update($validated);

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription updated successfully.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription cancelled successfully.');
    }
}
