<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;

class UsersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = User::query();

        if ($this->request->filled('role')) {
            $query->where('role', $this->request->role);
        }
        if ($this->request->filled('plan')) {
            $query->where('plan', $this->request->plan);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Username', 'Email', 'Role', 'Plan', 'Country', 'City', 'Created At', 'Last Active'];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->username,
            $user->email,
            $user->role,
            $user->plan,
            $user->country,
            $user->city,
            $user->created_at?->format('Y-m-d H:i'),
            $user->last_active_at?->format('Y-m-d H:i'),
        ];
    }
}
