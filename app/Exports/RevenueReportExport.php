<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;

class RevenueReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Invoice::with(['user', 'subscription']);

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }
        if ($this->request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $this->request->date_from);
        }
        if ($this->request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $this->request->date_to);
        }
        if ($this->request->filled('payment_method')) {
            $query->where('payment_method', $this->request->payment_method);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return ['ID', 'Invoice #', 'User', 'Email', 'Amount', 'Tax', 'Total', 'Currency', 'Status', 'Payment Method', 'Paid At', 'Created At'];
    }

    public function map($invoice): array
    {
        return [
            $invoice->id,
            $invoice->invoice_number,
            $invoice->user?->name,
            $invoice->user?->email,
            $invoice->amount,
            $invoice->tax_amount,
            $invoice->total_amount,
            $invoice->currency,
            $invoice->status,
            $invoice->payment_method,
            $invoice->paid_at?->format('Y-m-d H:i'),
            $invoice->created_at?->format('Y-m-d H:i'),
        ];
    }
}
