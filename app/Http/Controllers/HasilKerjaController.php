<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertHasilKerjaRequest;
use App\Models\HasilKerja;
use App\Models\Laporan;
use App\Services\SkpAssetCleanupService;
use App\Services\SyncHasilKerjaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class HasilKerjaController extends Controller
{
    public function __construct(
        protected SyncHasilKerjaService $syncHasilKerja,
        protected SkpAssetCleanupService $assetCleanup,
    ) {}

    public function store(UpsertHasilKerjaRequest $request, Laporan $laporan): RedirectResponse
    {
        $this->authorize('update', $laporan);

        if (! Schema::hasTable('indikator_kinerja_masters')) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Tabel master indikator belum tersedia. Jalankan migrasi terlebih dahulu.']);

            return to_route('laporan.show', $laporan);
        }

        $hasilKerja = $laporan->hasilKerja()->create([
            'indikator_kinerja_master_id' => $request->validated('indikator_kinerja_id'),
        ]);

        $this->syncHasilKerja->handle($hasilKerja, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Hasil kerja berhasil disimpan.']);

        return to_route('laporan.show', $laporan);
    }

    public function update(UpsertHasilKerjaRequest $request, Laporan $laporan, HasilKerja $hasilKerja): RedirectResponse
    {
        $this->authorize('update', $laporan);
        abort_unless($hasilKerja->laporan_id === $laporan->id, 404);

        if (! Schema::hasTable('indikator_kinerja_masters')) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Tabel master indikator belum tersedia. Jalankan migrasi terlebih dahulu.']);

            return to_route('laporan.show', $laporan);
        }

        $this->syncHasilKerja->handle($hasilKerja, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Hasil kerja berhasil diperbarui.']);

        return to_route('laporan.show', $laporan);
    }

    public function destroy(Laporan $laporan, HasilKerja $hasilKerja): RedirectResponse
    {
        $this->authorize('update', $laporan);
        abort_unless($hasilKerja->laporan_id === $laporan->id, 404);

        $this->assetCleanup->cleanupHasilKerja($hasilKerja);
        $hasilKerja->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Hasil kerja berhasil dihapus.']);

        return to_route('laporan.show', $laporan);
    }

    public function uploadLampiran(Request $request, string|int $id): RedirectResponse
    {
        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $hasilKerja = HasilKerja::query()->with('laporan')->findOrFail($id);

        $this->authorize('update', $hasilKerja->laporan);

        $existingCount = $hasilKerja->lampiranFiles()->count();

        foreach ($request->file('files', []) as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $sequence = $existingCount + $index + 1;
            $filename = sprintf('lampiran-%d.pdf', $sequence);

            $path = $file->storeAs(
                'lampiran/'.$hasilKerja->id,
                $filename,
                'public',
            );

            $hasilKerja->lampiranFiles()->create([
                'nama_file' => $filename,
                'file_path' => $path,
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lampiran berhasil diupload.']);

        return to_route('laporan.show', $hasilKerja->laporan);
    }
}
