<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Kelulusan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #10231c; }
        .eyebrow { margin: 0; font-size: 8px; letter-spacing: 2px; text-transform: uppercase; color: #145c43; }
        h1 { margin: 4px 0 0; font-family: 'DejaVu Serif', serif; font-size: 20px; color: #10231c; }
        .rule { margin: 10px 0 8px; border-top: 1.5px solid #b58a4b; }
        p { color: #4d6157; margin: 0; line-height: 1.5; }
        .summary { margin-top: 12px; }
        .summary span { display: inline-block; margin-right: 18px; color: #10231c; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border-bottom: 0.5px solid #d7ded8; padding: 7px 6px; text-align: left; }
        th { background: #145c43; color: #ffffff; font-size: 8px; letter-spacing: 0.6px; text-transform: uppercase; }
        tr:nth-child(even) td { background: #f5f8f3; }
        .number { text-align: right; }
    </style>
</head>
<body>
    <p class="eyebrow">e-ARSIP · Laporan internal</p>
    <h1>Rekap Kelulusan</h1>
    <div class="rule"></div>
    <p>
        Periode tanggal selesai:
        {{ $filters['from'] ? \Illuminate\Support\Carbon::parse($filters['from'])->format('d-m-Y') : 'Semua' }}
        sampai
        {{ $filters['to'] ? \Illuminate\Support\Carbon::parse($filters['to'])->format('d-m-Y') : 'Semua' }}
        — Dibuat {{ $generatedAt->format('d-m-Y H:i') }}
    </p>
    <p class="summary">
        <span>Total peserta: {{ $rows->sum('total_peserta') }}</span>
        <span>Lulus: {{ $rows->sum('lulus') }}</span>
        <span>Tidak lulus: {{ $rows->sum('tidak_lulus') }}</span>
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
