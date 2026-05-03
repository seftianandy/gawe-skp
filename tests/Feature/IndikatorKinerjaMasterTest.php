<?php

use App\Models\IndikatorKinerjaMaster;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('users can create update and delete indikator master records', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('indikator-kinerja-master.store'), [
            'nama_indikator' => 'Penyusunan laporan tepat waktu',
            'satuan' => 'dokumen',
            'target' => '1',
            'kategori' => 'kualitas',
        ])
        ->assertRedirect(route('indikator-kinerja-master.index'));

    $master = IndikatorKinerjaMaster::query()->firstOrFail();

    $this->actingAs($user)
        ->put(route('indikator-kinerja-master.update', $master), [
            'nama_indikator' => 'Penyusunan laporan bulanan',
            'satuan' => 'dokumen',
            'target' => '2',
            'kategori' => 'kuantitas',
        ])
        ->assertRedirect(route('indikator-kinerja-master.index'));

    expect($master->fresh()->nama_indikator)->toBe('Penyusunan laporan bulanan')
        ->and($master->fresh()->target)->toBe('2');

    $this->actingAs($user)
        ->delete(route('indikator-kinerja-master.destroy', $master))
        ->assertRedirect(route('indikator-kinerja-master.index'));

    $this->assertDatabaseMissing('indikator_kinerja_masters', [
        'id' => $master->id,
    ]);
});

test('indikator master cannot be deleted while used by hasil kerja', function () {
    $user = User::factory()->create();
    $master = IndikatorKinerjaMaster::factory()->create([
        'user_id' => $user->id,
    ]);
    $laporan = Laporan::create([
        'user_id' => $user->id,
        'periode' => '2026-05-01',
        'bulan' => 5,
        'tahun' => 2026,
        'status' => 'draft',
    ]);

    $laporan->hasilKerja()->create([
        'indikator_kinerja_master_id' => $master->id,
    ]);

    $this->actingAs($user)
        ->delete(route('indikator-kinerja-master.destroy', $master))
        ->assertRedirect(route('indikator-kinerja-master.index'));

    $this->assertDatabaseHas('indikator_kinerja_masters', [
        'id' => $master->id,
    ]);
});
