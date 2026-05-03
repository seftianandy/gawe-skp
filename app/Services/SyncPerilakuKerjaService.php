<?php

namespace App\Services;

use App\Models\PerilakuKerja;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SyncPerilakuKerjaService
{
    public function handle(PerilakuKerja $perilakuKerja, array $payload): PerilakuKerja
    {
        DB::transaction(function () use ($perilakuKerja, $payload): void {
            $perilakuKerja->update([
                'nama' => $payload['nama'],
                'deskripsi' => $payload['deskripsi'],
            ]);

            $deleteIds = collect($payload['hapus_bukti_perilaku'] ?? [])->filter()->all();

            if ($deleteIds !== []) {
                $buktiToDelete = $perilakuKerja->buktiPerilaku()->whereKey($deleteIds)->get();
                Storage::disk('public')->delete($buktiToDelete->pluck('file_path')->filter()->all());
                $perilakuKerja->buktiPerilaku()->whereKey($deleteIds)->delete();
            }

            foreach ($payload['bukti_perilaku_baru'] ?? [] as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $perilakuKerja->buktiPerilaku()->create([
                    'file_path' => $file->store('skp/perilaku', 'public'),
                ]);
            }
        });

        return $perilakuKerja->fresh('buktiPerilaku');
    }
}
