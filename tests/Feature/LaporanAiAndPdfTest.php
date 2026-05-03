<?php

use App\Models\Laporan;
use App\Models\IndikatorKinerjaMaster;
use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createAiLaporanFor(User $user, array $attributes = []): Laporan {
    return Laporan::create([
        'user_id' => $user->id,
        'periode' => '2026-05-01',
        'bulan' => 5,
        'tahun' => 2026,
        'status' => 'draft',
        ...$attributes,
    ]);
}

test('generate hasil kerja ai stores isi ai text', function () {
    $user = User::factory()->create();
    $laporan = createAiLaporanFor($user);
    $master = IndikatorKinerjaMaster::factory()->create([
        'user_id' => $user->id,
        'nama_indikator' => 'Penyusunan laporan evaluasi',
        'satuan' => 'dokumen',
        'target' => '1',
        'kategori' => 'kualitas',
    ]);
    $hasilKerja = $laporan->hasilKerja()->create([
        'indikator_kinerja_master_id' => $master->id,
    ]);
    $indikator = $hasilKerja->indikatorKinerja()->create([
        'deskripsi' => 'Penyusunan laporan evaluasi',
        'satuan' => 'dokumen',
        'target' => '1',
        'kategori' => 'kualitas',
    ]);
    $indikator->rencanaAksi()->create(['deskripsi' => 'Mengumpulkan bahan evaluasi']);
    $indikator->realisasi()->create([
        'tanggal' => '2026-05-17',
        'output' => 'Draf evaluasi',
        'keterangan' => 'Selesai sesuai jadwal',
    ]);

    $this->actingAs($user)
        ->post(route('hasil-kerja.generate-ai', $hasilKerja))
        ->assertRedirect(route('laporan.show', $laporan));

    expect($hasilKerja->fresh()->isi_ai)
        ->not->toBeNull()
        ->toContain('pelaksanaan kinerja pada indikator');
});

test('generate perilaku ai stores isi ai text', function () {
    $user = User::factory()->create();
    $laporan = createAiLaporanFor($user);
    $perilaku = $laporan->perilakuKerja()->create([
        'nama' => 'Berorientasi Pelayanan',
        'deskripsi' => 'Memberikan layanan cepat dan tepat.',
    ]);
    $perilaku->buktiPerilaku()->create([
        'file_path' => 'skp/perilaku/bukti-1.jpg',
    ]);

    $this->actingAs($user)
        ->post(route('perilaku.generate-ai', $perilaku))
        ->assertRedirect(route('laporan.show', $laporan));

    expect($perilaku->fresh()->isi_ai)
        ->not->toBeNull()
        ->toContain('pegawai telah menunjukkan perilaku kerja');
});

test('hasil kerja pdf export returns a pdf response', function () {
    $user = User::factory()->create();
    $laporan = createAiLaporanFor($user);
    $hasilKerja = $laporan->hasilKerja()->create([
        'indikator_kinerja_master_id' => IndikatorKinerjaMaster::factory()->create([
            'user_id' => $user->id,
            'nama_indikator' => 'Koordinasi program kerja',
            'satuan' => 'kegiatan',
            'target' => '1',
            'kategori' => 'kuantitas',
        ])->id,
        'isi_ai' => 'Narasi hasil kerja.',
    ]);

    $this->actingAs($user)
        ->get(route('hasil-kerja.pdf', $hasilKerja))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('perilaku pdf export returns a pdf response', function () {
    $user = User::factory()->create();
    $laporan = createAiLaporanFor($user);
    $perilaku = $laporan->perilakuKerja()->create([
        'nama' => 'Akuntabel',
        'deskripsi' => 'Menyelesaikan tugas dengan tanggung jawab penuh.',
        'isi_ai' => 'Narasi perilaku kerja.',
    ]);

    $this->actingAs($user)
        ->get(route('perilaku.pdf', $perilaku))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('generate ai does not change hasil kerja and perilaku display order', function () {
    $user = User::factory()->create();
    $laporan = createAiLaporanFor($user);

    $hasilKerjaPertama = $laporan->hasilKerja()->create([
        'indikator_kinerja_master_id' => IndikatorKinerjaMaster::factory()->create([
            'user_id' => $user->id,
            'nama_indikator' => 'Hasil Kerja Pertama',
            'satuan' => 'dokumen',
            'target' => '1',
            'kategori' => 'kualitas',
        ])->id,
    ]);
    $hasilKerjaKedua = $laporan->hasilKerja()->create([
        'indikator_kinerja_master_id' => IndikatorKinerjaMaster::factory()->create([
            'user_id' => $user->id,
            'nama_indikator' => 'Hasil Kerja Kedua',
            'satuan' => 'dokumen',
            'target' => '1',
            'kategori' => 'kualitas',
        ])->id,
    ]);

    $hasilKerjaKedua->indikatorKinerja()->create([
        'deskripsi' => 'Indikator kedua',
        'satuan' => 'dokumen',
        'target' => '1',
        'kategori' => 'kualitas',
    ]);

    $perilakuPertama = $laporan->perilakuKerja()->create([
        'nama' => 'Perilaku Pertama',
        'deskripsi' => 'Deskripsi pertama.',
    ]);
    $perilakuKedua = $laporan->perilakuKerja()->create([
        'nama' => 'Perilaku Kedua',
        'deskripsi' => 'Deskripsi kedua.',
    ]);

    $this->actingAs($user)
        ->post(route('hasil-kerja.generate-ai', $hasilKerjaKedua))
        ->assertRedirect(route('laporan.show', $laporan));

    $this->actingAs($user)
        ->post(route('perilaku.generate-ai', $perilakuKedua))
        ->assertRedirect(route('laporan.show', $laporan));

    $response = $this->actingAs($user)->get(route('laporan.show', $laporan));

    $response->assertOk()
        ->assertSeeInOrder([
            'Hasil Kerja Pertama',
            'Hasil Kerja Kedua',
        ])
        ->assertSeeInOrder([
            'Perilaku Pertama',
            'Perilaku Kedua',
        ]);

    expect($perilakuPertama->id)->toBeLessThan($perilakuKedua->id);
});

test('upload all to drive sends hasil kerja and perilaku pdfs through google drive service', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'google_id' => 'google-user-123',
        'google_refresh_token' => 'refresh-token',
        'google_token' => json_encode([
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
        ], JSON_THROW_ON_ERROR),
    ]);

    $laporan = createAiLaporanFor($user);
    $master = IndikatorKinerjaMaster::factory()->create([
        'user_id' => $user->id,
        'nama_indikator' => 'Koordinasi program kerja',
        'satuan' => 'kegiatan',
        'target' => '1',
        'kategori' => 'kualitas',
    ]);

    $hasilKerja = $laporan->hasilKerja()->create([
        'indikator_kinerja_master_id' => $master->id,
        'isi_ai' => 'Narasi hasil kerja.',
    ]);
    $hasilKerja->indikatorKinerja()->create([
        'deskripsi' => 'Koordinasi program kerja',
        'satuan' => 'kegiatan',
        'target' => '1',
        'kategori' => 'kualitas',
    ]);

    $perilaku = $laporan->perilakuKerja()->create([
        'nama' => 'Akuntabel',
        'deskripsi' => 'Menyelesaikan tugas dengan tanggung jawab penuh.',
        'isi_ai' => 'Narasi perilaku kerja.',
    ]);
    $perilaku->buktiPerilaku()->create([
        'file_path' => 'skp/perilaku/bukti-1.jpg',
    ]);

    $calls = (object) ['items' => []];

    app()->instance(GoogleDriveService::class, new class ($calls) extends GoogleDriveService {
        public function __construct(private object $calls)
        {
        }

        public function uploadHasilKerjaPdf($user, $laporan, $hasilKerja, string $localPath)
        {
            $this->calls->items[] = ['hasil', $user->id, $laporan->id, $hasilKerja->id, is_file($localPath)];

            return 'drive-file-hasil';
        }

        public function uploadPerilakuPdf($user, $laporan, $perilaku, string $localPath): string
        {
            $this->calls->items[] = ['perilaku', $user->id, $laporan->id, $perilaku->id, is_file($localPath)];

            return 'drive-file-perilaku';
        }
    });

    $this->actingAs($user)
        ->post(route('laporan.upload-drive', $laporan))
        ->assertRedirect(route('laporan.show', $laporan));

    expect($calls->items)->toHaveCount(2)
        ->and($calls->items[0][0])->toBe('hasil')
        ->and($calls->items[1][0])->toBe('perilaku')
        ->and($calls->items[0][4])->toBeTrue()
        ->and($calls->items[1][4])->toBeTrue();
});
