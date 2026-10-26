<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Sertifikat {{ $nomor }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; color: #0f172a; }
        .page { position: relative; width: 297mm; height: 210mm; padding: 12mm; }
        .frame { position: absolute; top: 8mm; left: 8mm; right: 8mm; bottom: 8mm; border: 2.5mm solid #047857; }
        .inner { position: absolute; top: 13mm; left: 13mm; right: 13mm; bottom: 13mm; border: 0.4mm solid #94a3b8; }
        .content { position: absolute; top: 26mm; left: 30mm; right: 30mm; text-align: center; }
        .kicker { margin: 0; font-size: 11pt; letter-spacing: 4px; text-transform: uppercase; color: #047857; }
        h1 { margin: 5mm 0 0; font-size: 34pt; letter-spacing: 2px; }
        .subtitle { margin: 3mm 0 0; font-size: 12pt; color: #475569; }
        .name { margin: 7mm 0 0; font-size: 26pt; font-weight: bold; }
        .program { margin: 4mm 0 0; font-size: 15pt; }
        .meta { margin: 3mm 0 0; font-size: 11pt; color: #475569; }
        .number { margin-top: 4mm; font-size: 9pt; color: #64748b; }
        .footer { position: absolute; left: 30mm; right: 30mm; bottom: 26mm; }
        .footer table { width: 100%; }
        .footer td { vertical-align: bottom; }
        .signature { width: 70mm; text-align: center; font-size: 10pt; color: #334155; }
        .signature .image { height: 20mm; margin-bottom: 2mm; }
        .signature .line { border-top: 0.4mm solid #334155; padding-top: 2mm; font-weight: bold; font-size: 11pt; color: #0f172a; }
        .qr { text-align: right; font-size: 8pt; color: #64748b; }
        .qr img { width: 28mm; height: 28mm; }
    </style>
</head>
<body>
<div class="page">
    <div class="frame"></div>
    <div class="inner"></div>

    <div class="content">
        <p class="kicker">Sertifikat Pelatihan</p>
        <h1>SERTIFIKAT</h1>
        <p class="subtitle">Diberikan kepada</p>
        <p class="name">{{ $nama_peserta }}</p>
        <p class="subtitle">atas keberhasilan menyelesaikan</p>
        <p class="program">{{ $program }}</p>
        @if (filled($standar_kompetensi))
            <p class="meta">Standar kompetensi: {{ $standar_kompetensi }}</p>
        @endif
        <p class="meta">{{ $batch }} &middot; Tahun {{ $tahun }}</p>
        <p class="number">Nomor: {{ $nomor }} &middot; Diterbitkan {{ $tanggal_terbit }}</p>
    </div>

    <div class="footer">
        <table>
            <tr>
                <td class="signature">
                    @if (filled($signature_data_uri))
                        <img class="image" src="{{ $signature_data_uri }}" alt="Tanda tangan">
                    @endif
                    @if (filled($nama_penandatangan))
                        <div class="line">{{ $nama_penandatangan }}</div>
                        @if (filled($jabatan_penandatangan))
                            <div>{{ $jabatan_penandatangan }}</div>
                        @endif
                    @endif
                </td>
                <td class="qr">
                    @if (filled($qr_data_uri))
                        <img src="{{ $qr_data_uri }}" alt="QR verifikasi">
                        <div>Pindai untuk verifikasi</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
