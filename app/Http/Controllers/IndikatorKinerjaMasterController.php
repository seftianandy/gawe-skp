<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIndikatorKinerjaMasterRequest;
use App\Http\Requests\UpdateIndikatorKinerjaMasterRequest;
use App\Models\IndikatorKinerjaMaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class IndikatorKinerjaMasterController extends Controller
{
    public function index(): Response
    {
        if (! Schema::hasTable('indikator_kinerja_masters')) {
            return Inertia::render('IndikatorKinerjaMaster/Index', [
                'indikatorKinerjaMasters' => [],
            ]);
        }

        $masters = IndikatorKinerjaMaster::query()
            ->where(function ($query): void {
                $query->whereNull('user_id')
                    ->orWhere('user_id', Auth::id());
            })
            ->orderBy('kategori')
            ->orderBy('nama_indikator')
            ->get();

        return Inertia::render('IndikatorKinerjaMaster/Index', [
            'indikatorKinerjaMasters' => $masters->map(fn (IndikatorKinerjaMaster $item) => [
                'id' => $item->id,
                'nama_indikator' => $item->nama_indikator,
                'satuan' => $item->satuan,
                'target' => $item->target,
                'kategori' => $item->kategori,
                'created_at' => $item->created_at?->toDateTimeString(),
            ])->values(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('IndikatorKinerjaMaster/Create', [
            'form' => [
                'nama_indikator' => '',
                'satuan' => '',
                'target' => '',
                'kategori' => '',
            ],
        ]);
    }

    public function store(StoreIndikatorKinerjaMasterRequest $request): RedirectResponse
    {
        if (! Schema::hasTable('indikator_kinerja_masters')) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Tabel master indikator belum tersedia. Jalankan migrasi terlebih dahulu.']);

            return to_route('indikator-kinerja-master.index');
        }

        IndikatorKinerjaMaster::query()->create([
            ...$request->validated(),
            'user_id' => Auth::id(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Indikator kinerja master berhasil dibuat.']);

        return to_route('indikator-kinerja-master.index');
    }

    public function edit(IndikatorKinerjaMaster $indikatorKinerjaMaster): Response
    {
        if (! Schema::hasTable('indikator_kinerja_masters')) {
            abort(404);
        }

        abort_unless($indikatorKinerjaMaster->user_id === Auth::id(), 404);

        return Inertia::render('IndikatorKinerjaMaster/Edit', [
            'indikatorKinerjaMaster' => [
                'id' => $indikatorKinerjaMaster->id,
                'nama_indikator' => $indikatorKinerjaMaster->nama_indikator,
                'satuan' => $indikatorKinerjaMaster->satuan,
                'target' => $indikatorKinerjaMaster->target,
                'kategori' => $indikatorKinerjaMaster->kategori,
            ],
        ]);
    }

    public function update(UpdateIndikatorKinerjaMasterRequest $request, IndikatorKinerjaMaster $indikatorKinerjaMaster): RedirectResponse
    {
        if (! Schema::hasTable('indikator_kinerja_masters')) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Tabel master indikator belum tersedia. Jalankan migrasi terlebih dahulu.']);

            return to_route('indikator-kinerja-master.index');
        }

        abort_unless($indikatorKinerjaMaster->user_id === Auth::id(), 404);

        $indikatorKinerjaMaster->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Indikator kinerja master berhasil diperbarui.']);

        return to_route('indikator-kinerja-master.index');
    }

    public function destroy(IndikatorKinerjaMaster $indikatorKinerjaMaster): RedirectResponse
    {
        if (! Schema::hasTable('indikator_kinerja_masters')) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Tabel master indikator belum tersedia. Jalankan migrasi terlebih dahulu.']);

            return to_route('indikator-kinerja-master.index');
        }

        abort_unless($indikatorKinerjaMaster->user_id === Auth::id(), 404);

        if ($indikatorKinerjaMaster->hasilKerja()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Master indikator sedang dipakai oleh hasil kerja.']);

            return to_route('indikator-kinerja-master.index');
        }

        $indikatorKinerjaMaster->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Indikator kinerja master berhasil dihapus.']);

        return to_route('indikator-kinerja-master.index');
    }
}
