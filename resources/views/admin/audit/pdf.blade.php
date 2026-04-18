<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Log Export</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; padding: 20px; }
        h1 { font-size: 16px; font-weight: bold; color: #4f46e5; margin-bottom: 4px; }
        p.subtitle { font-size: 10px; color: #888; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f3f4f6; padding: 6px 8px; text-align: left; font-size: 9px; color: #555; font-weight: bold; border-bottom: 2px solid #e5e7eb; }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; font-size: 9px; vertical-align: top; }
        tbody tr:nth-child(even) { background: #fafafa; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 8px; font-weight: bold; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #aaa; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>

    <h1>PageTurner — Audit Log Export</h1>
    <p class="subtitle">Generated on {{ now()->format('F d, Y g:i A') }} · {{ $audits->count() }} records</p>

    <table>
        <thead>
            <tr>
                <th>Event</th>
                <th>Model</th>
                <th>Record</th>
                <th>User</th>
                <th>IP Address</th>
                <th>Old Values</th>
                <th>New Values</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($audits as $audit)
                <tr>
                    <td>
                        @php
                            $badgeClass = match(true) {
                                in_array($audit->event, ['deleted','login_failed','backup_failed']) => 'badge-red',
                                in_array($audit->event, ['created','login','backup_success'])       => 'badge-green',
                                in_array($audit->event, ['updated','status_changed'])               => 'badge-blue',
                                default => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst(str_replace('_', ' ', $audit->event)) }}
                        </span>
                    </td>
                    <td>{{ class_basename($audit->auditable_type) }}</td>
                    <td>{{ $audit->auditable_id ?? '—' }}</td>
                    <td>
                        {{ $audit->user_name ?? 'System' }}
                        @if($audit->user_email)
                            <br><span style="color:#888">{{ $audit->user_email }}</span>
                        @endif
                    </td>
                    <td>{{ $audit->ip_address ?? '—' }}</td>
                    <td style="max-width:120px; word-break:break-all;">
                        {{ $audit->old_values ? substr($audit->old_values, 0, 80) . (strlen($audit->old_values) > 80 ? '...' : '') : '—' }}
                    </td>
                    <td style="max-width:120px; word-break:break-all;">
                        {{ $audit->new_values ? substr($audit->new_values, 0, 80) . (strlen($audit->new_values) > 80 ? '...' : '') : '—' }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($audit->created_at)->format('M d, Y g:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:20px; color:#aaa;">No audit logs found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        PageTurner Audit Log — Confidential — For compliance use only
    </div>

</body>
</html>