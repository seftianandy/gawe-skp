<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertPerilakuKerjaRequest;
use App\Models\Laporan;
use App\Models\PerilakuKerja;
use App\Services\SkpAssetCleanupService;
use App\Services\SyncPerilakuKerjaService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class PerilakuKerjaController extends Controller
{
    public function __construct(
        protected SyncPerilakuKerjaService $syncPerilakuKerja,
        protected SkpAssetCleanupService $assetCleanup,
    ) {
    }

    public function store(UpsertPerilakuKerjaRequest $request, Laporan $laporan): RedirectResponse
    {
        $this->authorize('update', $laporan);

        $perilakuKerja = $laporan->perilakuKerja()->create([
            'nama' => $request->validated('nama'),
            'deskripsi' => $request->validated('deskripsi'),
        ]);

        $this->syncPerilakuKerja->handle($perilakuKerja, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Perilaku kerja berhasil disimpan.']);

        return to_route('laporan.show', $laporan);
    }

    public function update(UpsertPerilakuKerjaRequest $request, Laporan $laporan, PerilakuKerja $perilakuKerja): RedirectResponse
    {
        $this->authorize('update', $laporan);
        abort_unless($perilakuKerja->laporan_id === $laporan->id, 404);

        $this->syncPerilakuKerja->handle($perilakuKerja, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Perilaku kerja berhasil diperbarui.']);

        return to_route('laporan.show', $laporan);
    }

    public function destroy(Laporan $laporan, PerilakuKerja $perilakuKerja): RedirectResponse
    {
        $this->authorize('update', $laporan);
        abort_unless($perilakuKerja->laporan_id === $laporan->id, 404);

        $this->assetCleanup->cleanupPerilakuKerja($perilakuKerja);
        $perilakuKerja->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Perilaku kerja berhasil dihapus.']);

        return to_route('laporan.show', $laporan);
    }
}
