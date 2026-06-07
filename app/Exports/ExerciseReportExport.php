<?php

namespace App\Exports;

use App\Models\UserPractice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;

class ExerciseReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = UserPractice::with(['user', 'practice']);

        if ($this->request->filled('practice_id')) {
            $query->where('practice_id', $this->request->practice_id);
        }
        if ($this->request->filled('user_id')) {
            $query->where('user_id', $this->request->user_id);
        }
        if ($this->request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $this->request->date_from);
        }
        if ($this->request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $this->request->date_to);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return ['ID', 'User', 'Practice', 'Total Questions', 'Correct', 'Incorrect', 'Skipped', 'Total Time (s)', 'Avg Time (s)', 'Score %', 'Date'];
    }

    public function map($userPractice): array
    {
        $score = $userPractice->total_questions > 0
            ? round(($userPractice->correct_answers / $userPractice->total_questions) * 100, 1)
            : 0;

        return [
            $userPractice->id,
            $userPractice->user?->name,
            $userPractice->practice?->name,
            $userPractice->total_questions,
            $userPractice->correct_answers,
            $userPractice->incorrect_answers,
            $userPractice->skipped_answers,
            $userPractice->total_time,
            $userPractice->average_time,
            $score,
            $userPractice->created_at?->format('Y-m-d H:i'),
        ];
    }
}
