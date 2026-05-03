<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan SKP</title>
    <style>
        @page {
            margin: 1.5cm; /* Margin standar laporan resmi */
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif; /* Font lebih bersih untuk laporan */
            font-size: 11px;
            color: #1f2937;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* Header Styling */
        .header {
            border-bottom: 2.5px solid #111827;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            text-transform: uppercase;
            margin-bottom: 5px;
            color: #111827;
        }

        .header p {
            font-size: 11px;
            color: #4b5563;
            margin: 0;
        }

        /* Meta Information Table */
        .meta-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .meta-table td.label {
            width: 130px;
            font-weight: bold;
            color: #374151;
        }

        /* Section Styling */
        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            border-left: 4px solid #111827;
            padding-left: 10px;
            margin-bottom: 12px;
            background: #f3f4f6;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        /* Content Boxes */
        .content-box {
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: #ffffff;
            text-align: left; /* Mencegah teks melompat seperti di gambar */
        }

        .narrative {
            /* Hapus white-space: pre-line karena ini pemicu utama spasi berantakan jika datanya kotor */
            white-space: normal;

            /* Gunakan text-align left saja agar lebih aman untuk laporan teknis */
            text-align: left;

            /* Tambahkan margin untuk pemisah antar paragraf */
            margin-bottom: 10px;

            /* Agar kata yang terlalu panjang tidak memotong layout */
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Card for Hasil Kerja */
        .card {
            border: 1px solid #e5e7eb;
            margin-bottom: 15px;
            /* UBAH INI: Izinkan konten terpotong dan lanjut ke halaman berikutnya */
            page-break-inside: auto;
        }

        /* Pastikan header atau judul section tidak sendirian di bawah halaman (orphans) */
        .section-title, .card-header {
            page-break-after: avoid;
        }

        .card-header {
            background: #f9fafb;
            padding: 10px;
            font-weight: bold;
            border-bottom: 1px solid #e5e7eb;
            color: #111827;
        }

        .card-body {
            padding: 12px;
        }

        .sub-section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 10px;
            margin-bottom: 5px;
            display: block;
        }

        ul {
            margin: 5px 0;
            padding-left: 20px;
        }

        li {
            margin-bottom: 3px;
        }

        /* Photo Grid */
        .photo-grid {
            width: 100%;
        }

        .photo-item {
            display: inline-block;
            width: 48%;
            margin-right: 1%;
            margin-bottom: 20px;
            vertical-align: top;
        }

        .photo-frame {
            border: 1px solid #d1d5db;
            padding: 5px;
            background: #fff;
        }

        .photo-frame img {
            width: 100%;
            height: 180px;
            object-fit: contain; /* Agar foto tidak terdistorsi */
        }

        .photo-caption {
            font-size: 9px;
            color: #374151;
            margin-top: 5px;
            line-height: 1.3;
        }

        .attachment-page {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Sasaran Kinerja Pegawai</h1>
        <p>{{ $laporan->user->nama_instansi }} | Periode {{ $laporan->periode?->translatedFormat('F Y') }}</p>
    </div>

    <div class="meta-box">
        <table class="meta-table">
            <tr><td class="label">Nama Pegawai</td><td>: {{ $laporan->user->nama }}</td></tr>
            <tr><td class="label">NIP</td><td>: {{ $laporan->user->nip }}</td></tr>
            <tr><td class="label">Jabatan</td><td>: {{ $laporan->user->jabatan }}</td></tr>
            <tr><td class="label">Status Laporan</td><td>: <strong>{{ strtoupper($laporan->status) }}</strong></td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Ringkasan Utama</div>
        <div class="content-box narrative">
            {{ $laporan->isi_laporan ?: 'Belum ada ringkasan laporan.' }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Rincian Hasil Kerja</div>
        @forelse ($laporan->hasilKerja as $hasil)
            @php
                $namaIndikator = $hasil->indikatorKinerjaMaster?->nama_indikator
                    ?? $hasil->indikatorKinerja->first()?->deskripsi
                    ?? '-';
            @endphp
            <div class="card">
                <div class="card-header">{{ $namaIndikator }}</div>
                <div class="card-body">
                    @if ($hasil->isi_ai)
                        <div class="sub-section-title">Ringkasan Capaian:</div>
                        <div class="narrative" style="margin-bottom: 15px;">{{ $hasil->isi_ai }}</div>
                    @endif

                    @forelse ($hasil->indikatorKinerja as $indikator)
                        <div style="border-top: 1px dashed #e5e7eb; padding-top: 8px; margin-top: 8px;">
                            <strong>Indikator:</strong> {{ $indikator->deskripsi }}
                            <small>({{ $indikator->target }} {{ $indikator->satuan }})</small>

                            @if ($indikator->realisasi->isNotEmpty())
                                <div class="sub-section-title">Realisasi:</div>
                                <ul>
                                    @foreach ($indikator->realisasi as $realisasi)
                                        <li>
                                            <strong>{{ $realisasi->tanggal?->translatedFormat('d M Y') }}</strong>:
                                            {{ $realisasi->output }}
                                            @if($realisasi->keterangan) <span style="color: #6b7280;">({{ $realisasi->keterangan }})</span> @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @empty
                        <p style="color: #9ca3af;">Tidak ada indikator kinerja.</p>
                    @endforelse
                </div>
            </div>
        @empty
            <p>Tidak ada data hasil kerja.</p>
        @endforelse
    </div>

    <div class="section">
        <div class="section-title">Perilaku Kerja</div>
        @forelse ($laporan->perilakuKerja as $perilaku)
            <div class="card">
                <div class="card-header">{{ $perilaku->nama }}</div>
                <div class="card-body">
                    <p>{{ $perilaku->deskripsi }}</p>
                    @if ($perilaku->isi_ai)
                        <div class="sub-section-title">Catatan Perilaku:</div>
                        <div class="narrative">{{ $perilaku->isi_ai }}</div>
                    @endif
                </div>
            </div>
        @empty
            <p>Tidak ada data perilaku kerja.</p>
        @endforelse
    </div>

    <!-- Halaman Lampiran -->
    @php
        $hasilKerjaPhotos = $laporan->hasilKerja->flatMap->indikatorKinerja->flatMap->realisasi->filter(fn($r) => $r->buktiFoto->isNotEmpty());
        $perilakuPhotos = $laporan->perilakuKerja->filter(fn($p) => $p->buktiPerilaku->isNotEmpty());
    @endphp

    @if ($hasilKerjaPhotos->isNotEmpty() || $perilakuPhotos->isNotEmpty())
        <div class="attachment-page">
            <div class="section-title">Lampiran Bukti Foto Kegiatan</div>
            <div class="photo-grid">
                @foreach ($hasilKerjaPhotos as $realisasi)
                    @foreach ($realisasi->buktiFoto as $foto)
                        @php $path = public_path('storage/'.$foto->file_path); @endphp
                        @if (file_exists($path))
                            <div class="photo-item">
                                <div class="photo-frame">
                                    <img src="{{ $path }}">
                                </div>
                                <div class="photo-caption">
                                    <strong>Hasil Kerja:</strong> {{ $realisasi->output }}<br>
                                    <strong>Tanggal:</strong> {{ $realisasi->tanggal?->translatedFormat('d F Y') }}
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
