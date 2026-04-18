<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AuditExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = DB::table('audits')
            ->leftJoin('users', function ($join) {
                $join->on('audits.user_id', '=', 'users.id')
                     ->where('audits.user_type', '=', 'App\\Models\\User');
            })
            ->select('audits.*', 'users.name as user_name', 'users.email as user_email');

        if (!empty($this->filters['event'])) {
            $query->where('audits.event', $this->filters['event']);
        }

        if (!empty($this->filters['auditable_type'])) {
            $query->where('audits.auditable_type', 'like', '%' . $this->filters['auditable_type'] . '%');
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('audits.created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('audits.created_at', '<=', $this->filters['date_to']);
        }

        return $query->orderByDesc('audits.created_at');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Event',
            'Model',
            'Record ID',
            'User',
            'Email',
            'Old Values',
            'New Values',
            'IP Address',
            'URL',
            'Date',
        ];
    }

    public function map($audit): array
    {
        return [
            $audit->id,
            $audit->event,
            class_basename($audit->auditable_type),
            $audit->auditable_id ?? '—',
            $audit->user_name ?? 'System',
            $audit->user_email ?? '—',
            $audit->old_values ?? '—',
            $audit->new_values ?? '—',
            $audit->ip_address ?? '—',
            $audit->url ?? '—',
            $audit->created_at,
        ];
    }
}