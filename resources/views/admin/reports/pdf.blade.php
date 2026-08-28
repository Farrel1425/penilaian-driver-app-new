<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { color: #172b4d; font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 28px; }
        h1 { color: #10284d; font-size: 20px; margin: 0 0 5px; }
        .subtitle { color: #64748b; margin: 0 0 22px; }
        .meta { background: #f3f6fb; border-left: 3px solid #2859df; color: #50627f; margin-bottom: 18px; padding: 9px 11px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #2859df; color: #fff; font-size: 9px; padding: 9px 7px; text-align: left; }
        td { border-bottom: 1px solid #dce5f0; padding: 9px 7px; vertical-align: top; }
        tr:nth-child(even) td { background: #f8faff; }
        .footer { bottom: 0; color: #8290a8; font-size: 8px; left: 0; position: fixed; right: 0; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="subtitle">Ringkasan hasil penilaian berdasarkan data yang dipilih.</p>
    <div class="meta">Dicetak pada {{ now(config('app.display_timezone'))->translatedFormat('d F Y, H:i') }}</div>
    <table>
        <thead>
            <tr>
                <th>{{ $type === 'branch' ? 'Unit Kerja' : ($type === 'driver' ? 'Driver' : 'Kendaraan') }}</th>
                <th>Unit Kerja</th>
                <th>Total Penilaian</th>
                <th>Rating Rata-rata</th>
                @if($type === 'branch')<th>Top Driver</th><th>Top Kendaraan</th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['name'] ?? $row['branch'] }}</td>
                    <td>{{ $row['branch'] ?? '-' }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['average'] ?? '-' }}</td>
                    @if($type === 'branch')<td>{{ $row['top_driver'] }}</td><td>{{ $row['top_vehicle'] }}</td>@endif
                </tr>
            @empty
                <tr><td colspan="6">Belum ada data rekap.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">Sistem Penilaian Driver</div>
</body>
</html>
