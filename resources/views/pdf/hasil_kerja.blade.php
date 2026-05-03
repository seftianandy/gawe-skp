<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Kerja</title>
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

        /* Tambahkan wrapper jika margin @page dirasa kurang konsisten */
        .document {
            width: 100%;
        }

        .header {
            border-bottom: 2px solid #1d4ed8;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .header h1 {
            font-size: 16px;
            color: #1e3a8a;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 10px;
            color: #6b7280;
        }

        .meta-box {
            border: 1px solid #dbeafe;
            background: #f8fbff;
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
            color: #1e3a8a;
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
            text-justify: inter-word;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
            text-align: left;
        }

        .data-table th {
            background: #eff6ff;
            color: #1e3a8a;
        }

        .data-table ul {
            padding-left: 15px;
        }

        .data-table li {
            margin-bottom: 4px;
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
            color: #1e3a8a;
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
            height: 180px;
            object-fit: cover;
            border-radius: 4px;
        }

        .photo-caption {
            margin-top: 6px;
            font-size: 10px;
            color: #4b5563;
        }
    </style>
</head>
<body>
    @php
        $photos = $hasilKerja->indikatorKinerja
            ->flatMap(fn ($indikator) => $indikator->realisasi->map(fn ($realisasi) => [
                'realisasi' => $realisasi,
                'files' => $realisasi->buktiFoto,
            ]))
            ->filter(fn (array $item) => $item['files']->isNotEmpty());

        $namaIndikator = $hasilKerja->indikatorKinerjaMaster?->nama_indikator
            ?? $hasilKerja->indikatorKinerja->first()?->deskripsi
            ?? '-';
    @endphp

    <div class="document">
        <div class="header">
            <h1>Laporan Hasil Kerja</h1>
            <p>{{ $hasilKerja->laporan->user->nama_instansi }} | Periode {{ $hasilKerja->laporan->periode?->translatedFormat('F Y') }}</p>
        </div>

        <div class="meta-box">
            <table>
                <tr><td>Nama Pegawai</td><td>: {{ $hasilKerja->laporan->user->nama }}</td></tr>
                <tr><td>NIP</td><td>: {{ $hasilKerja->laporan->user->nip }}</td></tr>
                <tr><td>Jabatan</td><td>: {{ $hasilKerja->laporan->user->jabatan }}</td></tr>
                <tr><td>Indikator Kinerja</td><td>: {{ $namaIndikator }}</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Ringkasan Hasil Kerja</div>
            <div class="box narrative">{{ $hasilKerja->isi_ai ?: 'Ringkasan hasil kerja belum tersedia.' }}</div>
        </div>

        <div class="section">
            <div class="section-title">Indikator Kinerja</div>
            @if ($hasilKerja->indikatorKinerja->isEmpty())
                <p class="muted">Belum ada indikator kinerja.</p>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Indikator</th>
                            <th>Target</th>
                            <th>Rencana Aksi</th>
                            <th>Realisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hasilKerja->indikatorKinerja as $indikator)
                            <tr>
                                <td>{{ $indikator->deskripsi }}<br><span class="muted">{{ ucfirst($indikator->kategori) }}</span></td>
                                <td>{{ $indikator->target }} {{ $indikator->satuan }}</td>
                                <td>
                                    @if ($indikator->rencanaAksi->isEmpty())
                                        <span class="muted">Belum ada rencana aksi.</span>
                                    @else
                                        <ul>
                                            @foreach ($indikator->rencanaAksi as $rencana)
                                                <li>{{ $rencana->deskripsi }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td>
                                    @if ($indikator->realisasi->isEmpty())
                                        <span class="muted">Belum ada realisasi.</span>
                                    @else
                                        <ul>
                                            @foreach ($indikator->realisasi as $realisasi)
                                                <li>
                                                    {{ $realisasi->tanggal?->translatedFormat('d F Y') ?? '-' }} - {{ $realisasi->output }}
                                                    @if ($realisasi->keterangan)
                                                        ({{ $realisasi->keterangan }})
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    @if ($photos->isNotEmpty())
        <div class="attachment-page">
            <div class="attachment-title">Lampiran Bukti Foto Pelaksanaan</div>
            <div class="attachment-subtitle">Lampiran ini memuat bukti foto realisasi untuk indikator "{{ $namaIndikator }}".</div>

            <div class="photo-grid">
                @foreach ($photos as $group)
                    @foreach ($group['files'] as $foto)
                        @php
                            $absolutePath = public_path('storage/'.$foto->file_path);
                        @endphp
                        @if (is_file($absolutePath))
                            <div class="photo-card">
                                <div class="photo-frame">
                                    <img src="{{ $absolutePath }}" alt="Bukti foto pelaksanaan">
                                </div>
                                <div class="photo-caption">
                                    {{ $group['realisasi']->tanggal?->translatedFormat('d F Y') ?? '-' }} - {{ $group['realisasi']->output }}
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif
</body>
</html>
