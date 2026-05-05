<?php

use App\Models\HasilKerja;
use App\Models\IndikatorKinerjaMaster;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createLampiranLaporanFor(User $user, array $attributes = []): Laporan
{
    return Laporan::create([
        'user_id' => $user->id,
        'periode' => '2026-05-01',
        'bulan' => 5,
        'tahun' => 2026,
        'status' => 'draft',
        ...$attributes,
    ]);
}

function createHasilKerjaForLampiran(User $user): HasilKerja
{
    $laporan = createLampiranLaporanFor($user);

    $master = IndikatorKinerjaMaster::factory()->create([
        'user_id' => $user->id,
        'nama_indikator' => 'Penyusunan laporan bulanan',
        'satuan' => 'dokumen',
        'target' => '1',
        'kategori' => 'kualitas',
    ]);

    return $laporan->hasilKerja()->create([
        'indikator_kinerja_master_id' => $master->id,
    ]);
}

test('users can upload multiple pdf lampiran for hasil kerja', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $hasilKerja = createHasilKerjaForLampiran($user);
    $laporan = $hasilKerja->laporan;

    $response = $this->actingAs($user)->post(route('hasil-kerja.upload-lampiran', $hasilKerja->id), [
        'files' => [
            UploadedFile::fake()->create('lampiran-a.pdf', 120, 'application/pdf'),
            UploadedFile::fake()->create('lampiran-b.pdf', 220, 'application/pdf'),
        ],
    ]);

    $response->assertRedirect(route('laporan.show', $laporan));

    $this->assertDatabaseHas('lampiran_files', [
        'hasil_kerja_id' => $hasilKerja->id,
        'nama_file' => 'lampiran-1.pdf',
    ]);

    $this->assertDatabaseHas('lampiran_files', [
        'hasil_kerja_id' => $hasilKerja->id,
        'nama_file' => 'lampiran-2.pdf',
    ]);

    Storage::disk('public')->assertExists('lampiran/'.$hasilKerja->id.'/lampiran-1.pdf');
    Storage::disk('public')->assertExists('lampiran/'.$hasilKerja->id.'/lampiran-2.pdf');

    expect(DB::table('lampiran_files')->where('hasil_kerja_id', $hasilKerja->id)->count())->toBe(2);
});

test('non pdf lampiran are rejected', function () {
    $user = User::factory()->create();
    $hasilKerja = createHasilKerjaForLampiran($user);

    $this->actingAs($user)
        ->post(route('hasil-kerja.upload-lampiran', $hasilKerja->id), [
            'files' => [
                UploadedFile::fake()->create('lampiran.txt', 20, 'text/plain'),
            ],
        ])
        ->assertSessionHasErrors('files.0');
});
