<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with(['user', 'subscription.plan'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->date_from, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(20);

        return view('admin.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'subscription.plan']);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status'         => 'required|string|in:pending,paid,failed,refunded',
            'payment_method' => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ]);

        if ($validated['status'] === 'paid' && !$invoice->paid_at) {
            $validated['paid_at'] = now();
        }

        $invoice->update($validated);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }
}
