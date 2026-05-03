<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Services\GenerateLaporanAiService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class GenerateLaporanAiController extends Controller
{
    public function __construct(
        protected GenerateLaporanAiService $generateLaporanAi,
    ) {
    }

    public function __invoke(Laporan $laporan): RedirectResponse
    {
        $this->authorize('update', $laporan);

        $laporan->update([
            'isi_laporan' => $this->generateLaporanAi->handle($laporan),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Isi laporan berhasil digenerate oleh AI mock.']);

        return to_route('laporan.show', $laporan);
    }
}
