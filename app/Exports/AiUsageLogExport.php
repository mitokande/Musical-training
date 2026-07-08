<?php

namespace App\Exports;

use App\Models\AiUsageLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AiUsageLogExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = AiUsageLog::with('user:id,name,email');

        if ($this->request->filled('from')) {
            $query->whereDate('created_at', '>=', $this->request->from);
        }
        if ($this->request->filled('to')) {
            $query->whereDate('created_at', '<=', $this->request->to);
        }
        if ($this->request->filled('feature')) {
            $query->where('feature', $this->request->feature);
        }
        if ($this->request->filled('model')) {
            $query->where('model', $this->request->model);
        }
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        return $query->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return ['ID', 'Date', 'User', 'Feature', 'Model', 'Prompt Tokens', 'Completion Tokens', 'Total Tokens', 'Cost (USD)', 'Status', 'Duration (ms)', 'Error'];
    }

    public function map($log): array
    {
        return [
            $log->id,
            $log->created_at?->format('Y-m-d H:i'),
            $log->user?->name ?? 'N/A',
            $log->feature,
            $log->model,
            $log->prompt_tokens,
            $log->completion_tokens,
            $log->total_tokens,
            $log->cost_usd,
            $log->status,
            $log->duration_ms,
            $log->error_message,
        ];
    }
}
