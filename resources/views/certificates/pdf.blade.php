<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Sertifikat {{ $nomor }}</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; color: #10231c; }
        .page { position: relative; width: 297mm; height: 209mm; margin: 0; padding: 0; background: #fdfdf8; overflow: hidden; }
        .frame { position: absolute; top: 8mm; left: 8mm; right: 8mm; bottom: 8mm; border: 2.2mm solid #145c43; }
        .inner { position: absolute; top: 13mm; left: 13mm; right: 13mm; bottom: 13mm; border: 0.35mm solid #b58a4b; }
        .content { margin: 26mm 30mm 0; text-align: center; }
        .seal { width: 20mm; margin: 0 auto; border: 0.6mm solid #b58a4b; background: #145c43; color: #fdfdf8; font-family: 'DejaVu Serif', serif; font-size: 15pt; font-weight: bold; line-height: 1.2; letter-spacing: 1px; text-align: center; padding: 3.5mm 0; }
        .kicker { margin: 6mm 0 0; font-size: 10pt; letter-spacing: 4px; text-transform: uppercase; color: #145c43; }
        h1 { margin: 3mm 0 0; font-family: 'DejaVu Serif', serif; font-size: 34pt; letter-spacing: 2px; color: #10231c; }
        .rule { width: 46mm; margin: 4mm auto 0; border-top: 0.5mm solid #b58a4b; }
        .subtitle { margin: 4mm 0 0; font-size: 12pt; color: #4d6157; }
        .name { margin: 7mm 0 0; font-family: 'DejaVu Serif', serif; font-size: 26pt; font-weight: bold; color: #10231c; }
        .program { margin: 4mm 0 0; font-size: 15pt; color: #145c43; }
        .meta { margin: 3mm 0 0; font-size: 11pt; color: #4d6157; }
        .number { margin-top: 5mm; font-size: 9pt; color: #6b7b72; letter-spacing: 0.4px; }
        .footer { margin: 5mm 30mm 0; page-break-inside: avoid; }
        .footer table { width: 100%; }
        .footer td { vertical-align: bottom; }
        .signature { width: 70mm; text-align: center; font-size: 10pt; color: #4d6157; }
        .signature .image { height: 16mm; margin-bottom: 2mm; }
        .signature .line { border-top: 0.35mm solid #10231c; padding-top: 2mm; font-weight: bold; font-size: 11pt; color: #10231c; }
        .qr { text-align: right; font-size: 8pt; color: #6b7b72; }
        .qr img { width: 22mm; height: 22mm; }
    </style>
</head>
<body>
<div class="page">
    <div class="frame"></div>
    <div class="inner"></div>

    <div class="content">
        <div class="seal">eA</div>
        <p class="kicker">Sertifikat Pelatihan</p>
        <h1>SERTIFIKAT</h1>
        <div class="rule"></div>
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
