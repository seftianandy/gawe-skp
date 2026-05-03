<?php

namespace App\Http\Controllers;

use App\Models\HasilKerja;
use App\Services\AI\GenerateLaporanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class GenerateHasilKerjaAiController extends Controller
{
    public function __construct(
        protected GenerateLaporanService $generateLaporan,
    ) {
    }

    public function __invoke(HasilKerja $hasilKerja): RedirectResponse
    {
        $load = [
            'laporan',
            'indikatorKinerjaMaster',
            'indikatorKinerja.rencanaAksi',
            'indikatorKinerja.realisasi',
        ];

        if (Schema::hasColumn('rencana_aksi', 'hasil_kerja_id')) {
            $load[] = 'rencanaAksiHasilKerja';
        }

        if (Schema::hasColumn('bukti_foto', 'hasil_kerja_id')) {
            $load[] = 'buktiFotoHasilKerja';
        }

        $hasilKerja->load($load);

        $this->authorize('update', $hasilKerja->laporan);

        $hasilKerja->update([
            'isi_ai' => $this->generateLaporan->generateHasilKerja($hasilKerja),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Narasi AI hasil kerja berhasil dibuat.']);

        return to_route('laporan.show', $hasilKerja->laporan);
    }
}
