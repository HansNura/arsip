<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Kelulusan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { color: #475569; margin-top: 0; }
        table { border-collapse: collapse; width: 100%; margin-top: 18px; }
        th, td { border: 1px solid #cbd5e1; padding: 7px; text-align: left; }
        th { background: #f1f5f9; }
        .number { text-align: right; }
    </style>
</head>
<body>
    <h1>Rekap Kelulusan</h1>
    <p>Periode tanggal selesai:
        {{ $filters['from'] ? \Illuminate\Support\Carbon::parse($filters['from'])->format('d-m-Y') : 'Semua' }}
        sampai
        {{ $filters['to'] ? \Illuminate\Support\Carbon::parse($filters['to'])->format('d-m-Y') : 'Semua' }}
        — Dibuat {{ $generatedAt->format('d-m-Y H:i') }}
    </p>
    <table>
        <thead>
            <tr>
                <th>Program</th>
                <th>Periode (Tahun)</th>
                <th class="number">Total Peserta</th>
                <th class="number">Lulus</th>
                <th class="number">Tidak Lulus</th>
                <th class="number">Belum Dinilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->program }}</td>
                    <td>{{ $row->periode }}</td>
                    <td class="number">{{ $row->total_peserta }}</td>
                    <td class="number">{{ $row->lulus }}</td>
                    <td class="number">{{ $row->tidak_lulus }}</td>
                    <td class="number">{{ $row->belum_dinilai }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada data kelulusan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
