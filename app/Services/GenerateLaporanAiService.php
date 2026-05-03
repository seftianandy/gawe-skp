<?php

namespace App\Services;

use App\Models\Laporan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class GenerateLaporanAiService
{
    public function handle(Laporan $laporan): string
    {
        $useDirectRelations = Schema::hasColumn('rencana_aksi', 'hasil_kerja_id')
            && Schema::hasColumn('bukti_foto', 'hasil_kerja_id');

        $load = [
            'hasilKerja.indikatorKinerjaMaster',
            'hasilKerja.indikatorKinerja.rencanaAksi',
            'hasilKerja.indikatorKinerja.realisasi',
            'perilakuKerja',
            'user',
        ];

        if ($useDirectRelations) {
            $load[] = 'hasilKerja.rencanaAksiHasilKerja';
            $load[] = 'hasilKerja.buktiFotoHasilKerja';
        }

        $laporan->loadMissing($load);

        $hasilKerja = $laporan->hasilKerja->map(function ($item, int $index) use ($useDirectRelations): string {
            $indikatorText = $item->indikatorKinerja
                ->map(function ($indikator, int $indikatorIndex) use ($index): string {
                    $rencanaAksi = $this->implodeOrFallback(
                        $indikator->rencanaAksi->pluck('deskripsi'),
                        'tidak ada rencana aksi',
                    );

                    $realisasi = $this->implodeOrFallback(
                        $indikator->realisasi->map(
                            fn ($item) => sprintf(
                                '%s (%s) - %s',
                                $item->tanggal?->translatedFormat('d F Y') ?? '-',
                                $item->output,
                                $item->keterangan,
                            ),
                        ),
                        'belum ada realisasi',
                    );

                    return sprintf(
                        "%d.%d %s | target %s %s | kategori %s | rencana: %s | realisasi: %s",
                        $index + 1,
                        $indikatorIndex + 1,
                        $indikator->deskripsi,
                        $indikator->target,
                        $indikator->satuan,
                        $indikator->kategori,
                        $rencanaAksi,
                        $realisasi,
                    );
                })
                ->implode("\n");

            $namaIndikator = $item->indikatorKinerjaMaster?->nama_indikator
                ?? $item->indikatorKinerja->first()?->deskripsi
                ?? 'Hasil kerja';

            $rencanaAksi = $useDirectRelations
                ? $this->implodeOrFallback($item->rencanaAksiHasilKerja->pluck('deskripsi'), 'tidak ada rencana aksi')
                : $this->implodeOrFallback(
                    $item->indikatorKinerja->flatMap(fn ($indikator) => $indikator->rencanaAksi->pluck('deskripsi')),
                    'tidak ada rencana aksi',
                );

            $buktiFotoCount = $useDirectRelations
                ? $item->buktiFotoHasilKerja->count()
                : $item->indikatorKinerja->sum(fn ($indikator) => $indikator->realisasi->sum(fn ($realisasi) => $realisasi->buktiFoto->count()));

            return sprintf("%d. %s\n%s\nRencana aksi: %s\nBukti foto: %d file", $index + 1, $namaIndikator, $indikatorText, $rencanaAksi, $buktiFotoCount);
        })->implode("\n\n");

        $perilaku = $this->implodeOrFallback(
            $laporan->perilakuKerja->map(
                fn ($item) => sprintf('%s: %s', $item->nama, $item->deskripsi),
            ),
            'Belum ada perilaku kerja yang diinput.',
        );

        return <<<TEXT
Laporan SKP AI (mock)

Pegawai: {$laporan->user->nama}
Instansi: {$laporan->user->nama_instansi}
Periode: {$laporan->periode?->translatedFormat('F Y')}
Status: {$laporan->status}

Ringkasan hasil kerja:
{$hasilKerja}

Ringkasan perilaku kerja:
{$perilaku}

Kesimpulan:
Secara umum aktivitas pada periode ini menunjukkan capaian kerja yang terstruktur, dengan indikator, rencana aksi, dan realisasi yang telah terdokumentasi. Naskah ini dihasilkan oleh mock AI service dan siap diganti ke provider AI nyata.
TEXT;
    }

    /**
     * @param  Collection<int, string>  $items
     */
    protected function implodeOrFallback(Collection $items, string $fallback): string
    {
        $text = $items->filter()->implode('; ');

        return $text !== '' ? $text : $fallback;
    }
}
