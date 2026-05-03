<?php

namespace App\Services;

use App\Models\HasilKerja;
use App\Models\IndikatorKinerja;
use App\Models\IndikatorKinerjaMaster;
use App\Models\BuktiFoto;
use App\Models\RencanaAksi;
use App\Models\Realisasi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class SyncHasilKerjaService
{
    public function __construct(
        protected SkpAssetCleanupService $assetCleanup,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(HasilKerja $hasilKerja, array $payload): HasilKerja
    {
        $master = IndikatorKinerjaMaster::query()->findOrFail($payload['indikator_kinerja_id']);

        DB::transaction(function () use ($hasilKerja, $master, $payload): void {
            $hasilKerja->update([
                'indikator_kinerja_master_id' => $master->id,
            ]);

            $indikator = $this->upsertSnapshotIndikator($hasilKerja, $master);

            $this->syncRencanaAksiHasilKerja(
                $hasilKerja,
                $indikator,
                $payload['rencana_aksi'] ?? [],
            );
            $this->syncBuktiFotoHasilKerja(
                $hasilKerja,
                $indikator,
                $payload['bukti_foto_baru'] ?? [],
                $payload['hapus_bukti_foto'] ?? [],
            );

            $hasilKerja->indikatorKinerja()
                ->whereKeyNot($indikator->id)
                ->get()
                ->each(function (IndikatorKinerja $expiredIndikator): void {
                    $expiredIndikator->loadMissing('realisasi.buktiFoto');

                    foreach ($expiredIndikator->realisasi as $realisasi) {
                        $this->assetCleanup->cleanupRealisasi($realisasi);
                    }

                    $expiredIndikator->delete();
                });
        });

    return $hasilKerja->fresh([
            'indikatorKinerjaMaster',
            'indikatorKinerja.rencanaAksi',
            'indikatorKinerja.realisasi.buktiFoto',
            ...($this->supportsDirectHasilKerjaRelations() ? ['rencanaAksiHasilKerja', 'buktiFotoHasilKerja'] : []),
        ]);
    }

    protected function supportsDirectHasilKerjaRelations(): bool
    {
        return Schema::hasColumn('rencana_aksi', 'hasil_kerja_id')
            && Schema::hasColumn('bukti_foto', 'hasil_kerja_id');
    }

    protected function upsertSnapshotIndikator(HasilKerja $hasilKerja, IndikatorKinerjaMaster $master): IndikatorKinerja
    {
        $indikator = $hasilKerja->indikatorKinerja()->first();

        $attributes = [
            'deskripsi' => $master->nama_indikator,
            'satuan' => $master->satuan,
            'target' => $master->target,
            'kategori' => $master->kategori,
        ];

        if ($indikator) {
            $indikator->update($attributes);

            return $indikator;
        }

        return $hasilKerja->indikatorKinerja()->create($attributes);
    }

    /**
     * @param  array<int, array<string, mixed>>  $payloads
     */
    protected function syncRealisasi(IndikatorKinerja $indikator, array $payloads): void
    {
        $retainedIds = [];

        foreach ($payloads as $payload) {
            $realisasi = isset($payload['id'])
                ? $indikator->realisasi()->findOrFail($payload['id'])
                : $indikator->realisasi()->make();

            $realisasi->fill([
                'tanggal' => $payload['tanggal'],
                'output' => $payload['output'],
                'keterangan' => $payload['keterangan'],
            ]);
            $realisasi->save();

            $retainedIds[] = $realisasi->id;

            $deleteIds = collect($payload['hapus_bukti_foto'] ?? [])->filter()->all();

            if ($deleteIds !== []) {
                $buktiToDelete = $realisasi->buktiFoto()->whereKey($deleteIds)->get();
                Storage::disk('public')->delete($buktiToDelete->pluck('file_path')->filter()->all());
                $realisasi->buktiFoto()->whereKey($deleteIds)->delete();
            }

            foreach ($payload['bukti_foto_baru'] ?? [] as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $realisasi->buktiFoto()->create([
                    'file_path' => $file->store('skp/realisasi', 'public'),
                ]);
            }
        }

        $indikator->realisasi()
            ->whereNotIn('id', $retainedIds)
            ->get()
            ->each(function (Realisasi $realisasi): void {
                $this->assetCleanup->cleanupRealisasi($realisasi);
                $realisasi->delete();
            });
    }

    /**
     * @param  array<int, string>  $payloads
     */
    protected function syncRencanaAksiHasilKerja(
        HasilKerja $hasilKerja,
        IndikatorKinerja $indikator,
        array $payloads,
    ): void
    {
        $retainedIds = [];
        $useDirectRelation = $this->supportsDirectHasilKerjaRelations();

        foreach ($payloads as $payload) {
            $deskripsi = is_string($payload) ? $payload : (string) ($payload['deskripsi'] ?? '');

            if (trim($deskripsi) === '') {
                continue;
            }

            $attributes = [
                'deskripsi' => $deskripsi,
                'indikator_id' => $indikator->id,
            ];

            if ($useDirectRelation) {
                $attributes['hasil_kerja_id'] = $hasilKerja->id;
            }

            $rencanaAksi = RencanaAksi::query()->create($attributes);

            $retainedIds[] = $rencanaAksi->id;
        }

        if ($useDirectRelation) {
            $hasilKerja->rencanaAksiHasilKerja()
                ->whereNotIn('id', $retainedIds)
                ->delete();

            return;
        }

        $indikator->rencanaAksi()
            ->whereNotIn('id', $retainedIds)
            ->delete();
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @param  array<int, int>  $deletedIds
     */
    protected function syncBuktiFotoHasilKerja(
        HasilKerja $hasilKerja,
        IndikatorKinerja $indikator,
        array $files,
        array $deletedIds,
    ): void
    {
        $useDirectRelation = $this->supportsDirectHasilKerjaRelations();
        $realisasi = $indikator->realisasi()
            ->firstOrCreate(
                ['output' => 'Dokumentasi hasil kerja'],
                [
                    'tanggal' => now()->toDateString(),
                    'keterangan' => '',
                ],
            );

        if ($deletedIds !== []) {
            if ($useDirectRelation) {
                $buktiToDelete = $hasilKerja->buktiFotoHasilKerja()->whereKey($deletedIds)->get();
                Storage::disk('public')->delete($buktiToDelete->pluck('file_path')->filter()->all());
                $hasilKerja->buktiFotoHasilKerja()->whereKey($deletedIds)->delete();
            } else {
                $buktiToDelete = $realisasi?->buktiFoto()->whereKey($deletedIds)->get() ?? collect();
                Storage::disk('public')->delete($buktiToDelete->pluck('file_path')->filter()->all());
                $realisasi?->buktiFoto()->whereKey($deletedIds)->delete();
            }
        }

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $filePath = $file->store('skp/hasil-kerja', 'public');

            if (! $useDirectRelation) {
                $attributes = [
                    'realisasi_id' => $realisasi->id,
                    'file_path' => $filePath,
                ];

                BuktiFoto::query()->create($attributes);

                continue;
            }

            $attributes = [
                'realisasi_id' => $realisasi->id,
                'hasil_kerja_id' => $hasilKerja->id,
                'file_path' => $filePath,
            ];
            BuktiFoto::query()->create($attributes);
        }
    }
}
