<?php

namespace App\Exports;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubscriptionsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Subscription::with(['user', 'plan']);

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }
        if ($this->request->filled('plan_id')) {
            $query->where('plan_id', $this->request->plan_id);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return ['ID', 'User', 'Email', 'Plan', 'Status', 'Amount', 'Currency', 'Starts At', 'Ends At', 'Cancelled At', 'Created At'];
    }

    public function map($subscription): array
    {
        return [
            $subscription->id,
            $subscription->user?->name,
            $subscription->user?->email,
            $subscription->plan?->name,
            $subscription->status,
            $subscription->amount,
            $subscription->currency,
            $subscription->starts_at?->format('Y-m-d H:i'),
            $subscription->ends_at?->format('Y-m-d H:i'),
            $subscription->cancelled_at?->format('Y-m-d H:i'),
            $subscription->created_at?->format('Y-m-d H:i'),
        ];
    }
}
