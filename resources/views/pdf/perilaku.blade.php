<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Perilaku Kerja PDF</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm; /* Gunakan cm agar lebih pasti ukurannya */
        }

        /* Ganti bagian ini */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.5;
            margin: 0; /* Biarkan @page yang mengatur margin luar */
            padding: 0;
        }

        .header {
            border-bottom: 2px solid #0f766e;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .header h1 {
            font-size: 16px;
            color: #115e59;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 10px;
            color: #6b7280;
        }

        .meta-box {
            border: 1px solid #ccfbf1;
            background: #f0fdfa;
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }

        .meta-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-box td {
            padding: 4px 0;
            vertical-align: top;
        }

        .meta-box td:first-child {
            width: 110px;
            font-weight: bold;
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #115e59;
            margin-bottom: 8px;
        }

        .box {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 12px;
            background: #fafafa;
        }

        .narrative {
            white-space: pre-line;
            text-align: justify;
        }

        .muted {
            color: #6b7280;
            font-style: italic;
        }

        .attachment-page {
            page-break-before: always;
        }

        .attachment-title {
            font-size: 14px;
            font-weight: bold;
            color: #115e59;
            margin-bottom: 6px;
        }

        .attachment-subtitle {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .photo-grid {
            font-size: 0;
        }

        .photo-card {
            display: inline-block;
            width: 48%;
            margin: 0 2% 14px 0;
            vertical-align: top;
            font-size: 11px;
        }

        .photo-card:nth-child(2n) {
            margin-right: 0;
        }

        .photo-frame {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 6px;
            background: #fff;
        }

        .photo-frame img {
            width: 100%;
            height: 190px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Perilaku Kerja</h1>
        <p>{{ $perilakuKerja->laporan->user->nama_instansi }} | Periode {{ $perilakuKerja->laporan->periode?->translatedFormat('F Y') }}</p>
    </div>

    <div class="meta-box">
        <table>
            <tr><td>Nama Pegawai</td><td>: {{ $perilakuKerja->laporan->user->nama }}</td></tr>
            <tr><td>NIP</td><td>: {{ $perilakuKerja->laporan->user->nip }}</td></tr>
            <tr><td>Jabatan</td><td>: {{ $perilakuKerja->laporan->user->jabatan }}</td></tr>
            <tr><td>Perilaku Kerja</td><td>: {{ $perilakuKerja->nama }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Deskripsi</div>
        <div class="box">{{ $perilakuKerja->deskripsi ?: 'Deskripsi perilaku kerja belum tersedia.' }}</div>
    </div>

    <div class="section">
        <div class="section-title">Ringkasan Perilaku Kerja</div>
        <div class="box narrative">{{ $perilakuKerja->isi_ai ?: 'Ringkasan perilaku kerja belum tersedia.' }}</div>
    </div>

    @if ($perilakuKerja->buktiPerilaku->isNotEmpty())
        <div class="attachment-page">
            <div class="attachment-title">Lampiran Bukti Foto</div>
            <div class="attachment-subtitle">Lampiran perilaku kerja "{{ $perilakuKerja->nama }}".</div>

            <div class="photo-grid">
                @foreach ($perilakuKerja->buktiPerilaku as $foto)
                    @php
                        $absolutePath = public_path('storage/'.$foto->file_path);
                    @endphp
                    @if (is_file($absolutePath))
                        <div class="photo-card">
                            <div class="photo-frame">
                                <img src="{{ $absolutePath }}" alt="Bukti perilaku kerja">
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</body>
</html>
