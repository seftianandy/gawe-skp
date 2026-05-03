<?php

use App\Models\Laporan;
use App\Models\IndikatorKinerjaMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function createLaporanFor(User $user, array $attributes = []): Laporan {
    return Laporan::create([
        'user_id' => $user->id,
        'periode' => '2026-05-01',
        'bulan' => 5,
        'tahun' => 2026,
        'status' => 'draft',
        ...$attributes,
    ]);
}

test('authenticated users can create laporan and duplicate month is rejected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('laporan.store'), [
            'periode' => '2026-05-01',
            'bulan' => 5,
            'tahun' => 2026,
            'status' => 'draft',
        ])
        ->assertRedirect();

    $laporan = Laporan::query()->firstOrFail();

    expect(DB::table('perilaku_kerja')->where('laporan_id', $laporan->id)->count())->toBe(7)
        ->and(DB::table('perilaku_kerja')->where('laporan_id', $laporan->id)->orderBy('id')->pluck('nama')->all())->toBe([
            'Berorientasi Pelayanan',
            'Akuntabel',
            'Kompeten',
            'Harmonis',
            'Loyal',
            'Adaptif',
            'Kolaboratif',
        ]);

    $this->actingAs($user)
        ->post(route('laporan.store'), [
            'periode' => '2026-05-15',
            'bulan' => 5,
            'tahun' => 2026,
            'status' => 'submit',
        ])
        ->assertSessionHasErrors('bulan');

    $this->assertDatabaseCount('laporan', 1);

    $this->actingAs($user)
        ->put(route('laporan.update', $laporan), [
            'periode' => '2026-05-01',
            'bulan' => 5,
            'tahun' => 2026,
            'status' => 'final',
        ])
        ->assertRedirect(route('laporan.show', $laporan));

    expect(DB::table('perilaku_kerja')->where('laporan_id', $laporan->id)->count())->toBe(7);
});

test('users can store hasil kerja with a selected indikator master', function () {
    $user = User::factory()->create();
    $laporan = createLaporanFor($user);
    $master = IndikatorKinerjaMaster::factory()->create([
        'user_id' => $user->id,
        'nama_indikator' => 'Penyusunan laporan bulanan',
        'satuan' => 'dokumen',
        'target' => '1',
        'kategori' => 'kualitas',
    ]);
    Storage::fake('public');

    $response = $this->actingAs($user)->post(
        route('laporan.hasil-kerja.store', $laporan),
        [
            'indikator_kinerja_id' => $master->id,
            'rencana_aksi' => [
                'Menyusun draft laporan',
                'Melakukan validasi data',
            ],
            'bukti_foto_baru' => [
                UploadedFile::fake()->image('bukti-1.jpg'),
                UploadedFile::fake()->image('bukti-2.jpg'),
            ],
        ],
    );

    $response->assertRedirect(route('laporan.show', $laporan));

    $hasilKerjaId = DB::table('hasil_kerja')->where('laporan_id', $laporan->id)->value('id');

    $this->assertNotNull($hasilKerjaId);
    $this->assertDatabaseHas('hasil_kerja', [
        'laporan_id' => $laporan->id,
        'indikator_kinerja_master_id' => $master->id,
    ]);

    if (Schema::hasColumn('rencana_aksi', 'hasil_kerja_id')) {
        expect(DB::table('rencana_aksi')->where('hasil_kerja_id', $hasilKerjaId)->pluck('deskripsi')->all())->toBe([
            'Menyusun draft laporan',
            'Melakukan validasi data',
        ]);
    } else {
        $indikatorId = DB::table('indikator_kinerja')->where('hasil_kerja_id', $hasilKerjaId)->value('id');

        expect(DB::table('rencana_aksi')->where('indikator_id', $indikatorId)->pluck('deskripsi')->all())->toBe([
            'Menyusun draft laporan',
            'Melakukan validasi data',
        ]);
    }

    if (Schema::hasColumn('bukti_foto', 'hasil_kerja_id')) {
        $buktiFotoPaths = DB::table('bukti_foto')
            ->where('hasil_kerja_id', $hasilKerjaId)
            ->orderBy('id')
            ->pluck('file_path')
            ->all();
    } else {
        $realisasiId = DB::table('realisasi')
            ->where('indikator_id', DB::table('indikator_kinerja')->where('hasil_kerja_id', $hasilKerjaId)->value('id'))
            ->value('id');

        $buktiFotoPaths = DB::table('bukti_foto')
            ->where('realisasi_id', $realisasiId)
            ->orderBy('id')
            ->pluck('file_path')
            ->all();
    }

    expect($buktiFotoPaths)->toHaveCount(2);

    foreach ($buktiFotoPaths as $path) {
        Storage::disk('public')->assertExists($path);
    }

    $this->assertDatabaseHas('indikator_kinerja', [
        'hasil_kerja_id' => $hasilKerjaId,
        'deskripsi' => 'Penyusunan laporan bulanan',
        'satuan' => 'dokumen',
        'target' => '1',
        'kategori' => 'kualitas',
    ]);
});

test('generate laporan ai stores isi laporan text', function () {
    $user = User::factory()->create([
        'nama' => 'Pegawai Uji',
        'nama_instansi' => 'BKD',
    ]);

    $laporan = createLaporanFor($user);
    $master = IndikatorKinerjaMaster::factory()->create([
        'user_id' => $user->id,
        'nama_indikator' => 'Koordinasi program',
        'satuan' => 'kegiatan',
        'target' => '2',
        'kategori' => 'kuantitas',
    ]);
    $hasilKerja = $laporan->hasilKerja()->create(['indikator_kinerja_master_id' => $master->id]);
    $indikator = $hasilKerja->indikatorKinerja()->create([
        'deskripsi' => 'Koordinasi program',
        'satuan' => 'kegiatan',
        'target' => '2',
        'kategori' => 'kuantitas',
    ]);
    $indikator->rencanaAksi()->create(['deskripsi' => 'Menjadwalkan rapat']);
    $indikator->realisasi()->create([
        'tanggal' => '2026-05-11',
        'output' => 'Notulen',
        'keterangan' => 'Rapat berjalan baik',
    ]);

    $this->actingAs($user)
        ->post(route('laporan.generate-ai', $laporan))
        ->assertRedirect(route('laporan.show', $laporan));

    expect($laporan->fresh()->isi_laporan)->toContain('Laporan SKP AI (mock)');
});

test('export pdf returns a pdf response', function () {
    $user = User::factory()->create();
    $laporan = createLaporanFor($user);

    $this->actingAs($user)
        ->get(route('laporan.export-pdf', $laporan))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
