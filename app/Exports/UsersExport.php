<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = User::query()->where('role', 'customer');

        if (!empty($this->filters['status'])) {
            if ($this->filters['status'] === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($this->filters['status'] === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $this->filters['search'] . '%');
            });
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Email Verified',
            'Registered At',
            'Total Orders',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone ?? '—',
            $user->email_verified_at ? 'Yes' : 'No',
            $user->created_at->format('Y-m-d'),
            $user->orders()->count(),
        ];
    }
}