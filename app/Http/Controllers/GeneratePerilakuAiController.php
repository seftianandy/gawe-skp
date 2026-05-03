<?php

namespace App\Http\Controllers;

use App\Models\PerilakuKerja;
use App\Services\AI\GenerateLaporanService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class GeneratePerilakuAiController extends Controller
{
    public function __construct(
        protected GenerateLaporanService $generateLaporan,
    ) {
    }

    public function __invoke(PerilakuKerja $perilakuKerja): RedirectResponse
    {
        $perilakuKerja->load([
            'laporan',
            'buktiPerilaku',
        ]);

        $this->authorize('update', $perilakuKerja->laporan);

        $perilakuKerja->update([
            'isi_ai' => $this->generateLaporan->generatePerilaku($perilakuKerja),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Narasi AI perilaku kerja berhasil dibuat.']);

        return to_route('laporan.show', $perilakuKerja->laporan);
    }
}
