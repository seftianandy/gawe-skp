<?php

namespace App\Services;

use App\Models\HasilKerja;
use App\Models\Laporan;
use App\Models\PerilakuKerja;
use App\Models\Realisasi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class SkpAssetCleanupService
{
    public function cleanupLaporan(Laporan $laporan): void
    {
        $load = [
            'hasilKerja.indikatorKinerja.realisasi.buktiFoto',
            'perilakuKerja.buktiPerilaku',
        ];

        if (Schema::hasColumn('bukti_foto', 'hasil_kerja_id')) {
            $load[] = 'hasilKerja.buktiFotoHasilKerja';
        }

        $laporan->loadMissing($load);

        foreach ($laporan->hasilKerja as $hasilKerja) {
            $this->cleanupHasilKerja($hasilKerja);
        }

        foreach ($laporan->perilakuKerja as $perilakuKerja) {
            $this->cleanupPerilakuKerja($perilakuKerja);
        }

        if ($laporan->file_pdf) {
            Storage::disk('public')->delete($laporan->file_pdf);
        }
    }

    public function cleanupHasilKerja(HasilKerja $hasilKerja): void
    {
        $load = ['indikatorKinerja.realisasi.buktiFoto'];

        if (Schema::hasColumn('bukti_foto', 'hasil_kerja_id')) {
            $load[] = 'buktiFotoHasilKerja';
        }

        $hasilKerja->loadMissing($load);

        foreach ($hasilKerja->indikatorKinerja as $indikator) {
            foreach ($indikator->realisasi as $realisasi) {
                $this->cleanupRealisasi($realisasi);
            }
        }

        if (Schema::hasColumn('bukti_foto', 'hasil_kerja_id')) {
            Storage::disk('public')->delete(
                $hasilKerja->buktiFotoHasilKerja->pluck('file_path')->filter()->all(),
            );
        }
    }

    public function cleanupPerilakuKerja(PerilakuKerja $perilakuKerja): void
    {
        $perilakuKerja->loadMissing('buktiPerilaku');

        Storage::disk('public')->delete(
            $perilakuKerja->buktiPerilaku->pluck('file_path')->filter()->all(),
        );
    }

    public function cleanupRealisasi(Realisasi $realisasi): void
    {
        $realisasi->loadMissing('buktiFoto');

        Storage::disk('public')->delete(
            $realisasi->buktiFoto->pluck('file_path')->filter()->all(),
        );
    }
}
