<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('skp tables and user fields are created', function () {
    expect(Schema::hasTable('laporan'))->toBeTrue()
        ->and(Schema::hasTable('hasil_kerja'))->toBeTrue()
        ->and(Schema::hasTable('indikator_kinerja_masters'))->toBeTrue()
        ->and(Schema::hasTable('indikator_kinerja'))->toBeTrue()
        ->and(Schema::hasTable('rencana_aksi'))->toBeTrue()
        ->and(Schema::hasTable('realisasi'))->toBeTrue()
        ->and(Schema::hasTable('bukti_foto'))->toBeTrue()
        ->and(Schema::hasTable('perilaku_kerja'))->toBeTrue()
        ->and(Schema::hasTable('bukti_perilaku'))->toBeTrue()
        ->and(Schema::hasColumns('users', ['nama_instansi', 'nama', 'nip', 'jabatan', 'google_token', 'google_refresh_token', 'google_id']))->toBeTrue()
        ->and(Schema::hasColumns('laporan', [
            'user_id',
            'periode',
            'bulan',
            'tahun',
            'status',
            'file_pdf',
            'deleted_at',
            'isi_laporan',
        ]))->toBeTrue();

    expect(Schema::hasColumns('hasil_kerja', ['indikator_kinerja_master_id', 'isi_ai']))->toBeTrue()
        ->and(Schema::hasColumns('indikator_kinerja_masters', ['user_id', 'nama_indikator', 'satuan', 'target', 'kategori']))->toBeTrue()
        ->and(Schema::hasColumns('perilaku_kerja', ['isi_ai']))->toBeTrue();

    expect(Schema::hasColumn('hasil_kerja', 'judul'))->toBeFalse();
});

test('laporan enforces unique user month year combination', function () {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Test User',
        'nama_instansi' => 'BKD',
        'nama' => 'Test User',
        'nip' => '198901012026051001',
        'jabatan' => 'Analis SDM',
        'email' => 'pegawai@example.com',
        'password' => 'password',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('laporan')->insert([
        'user_id' => $userId,
        'periode' => '2026-04-01',
        'bulan' => 4,
        'tahun' => 2026,
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('laporan')->insert([
        'user_id' => $userId,
        'periode' => '2026-04-15',
        'bulan' => 4,
        'tahun' => 2026,
        'status' => 'submit',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

test('deleting laporan cascades to all skp child tables', function () {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Cascade User',
        'nama_instansi' => 'Inspektorat',
        'nama' => 'Cascade User',
        'nip' => '198901012026051002',
        'jabatan' => 'Auditor',
        'email' => 'cascade@example.com',
        'password' => 'password',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $laporanId = DB::table('laporan')->insertGetId([
        'user_id' => $userId,
        'periode' => '2026-05-01',
        'bulan' => 5,
        'tahun' => 2026,
        'status' => 'final',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $hasilKerjaId = DB::table('hasil_kerja')->insertGetId([
        'laporan_id' => $laporanId,
        'indikator_kinerja_master_id' => DB::table('indikator_kinerja_masters')->insertGetId([
            'user_id' => $userId,
            'nama_indikator' => 'Koordinasi program',
            'satuan' => 'dokumen',
            'target' => '1',
            'kategori' => 'kualitas',
            'created_at' => now(),
            'updated_at' => now(),
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $indikatorId = DB::table('indikator_kinerja')->insertGetId([
        'hasil_kerja_id' => $hasilKerjaId,
        'deskripsi' => 'Laporan koordinasi tersusun',
        'satuan' => 'dokumen',
        'target' => '1 dokumen',
        'kategori' => 'kualitas',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('rencana_aksi')->insert([
        'indikator_id' => $indikatorId,
        'deskripsi' => 'Menyusun agenda koordinasi',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $realisasiId = DB::table('realisasi')->insertGetId([
        'indikator_id' => $indikatorId,
        'tanggal' => '2026-05-10',
        'output' => 'Notulen rapat',
        'keterangan' => 'Koordinasi berjalan sesuai rencana',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('bukti_foto')->insert([
        'realisasi_id' => $realisasiId,
        'file_path' => 'bukti-foto/rapat-1.jpg',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $perilakuId = DB::table('perilaku_kerja')->insertGetId([
        'laporan_id' => $laporanId,
        'nama' => 'Berorientasi Pelayanan',
        'deskripsi' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('bukti_perilaku')->insert([
        'perilaku_id' => $perilakuId,
        'file_path' => 'bukti-perilaku/pelayanan-1.jpg',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('laporan')->where('id', $laporanId)->delete();

    expect(DB::table('hasil_kerja')->count())->toBe(0)
        ->and(DB::table('indikator_kinerja')->count())->toBe(0)
        ->and(DB::table('rencana_aksi')->count())->toBe(0)
        ->and(DB::table('realisasi')->count())->toBe(0)
        ->and(DB::table('bukti_foto')->count())->toBe(0)
        ->and(DB::table('perilaku_kerja')->count())->toBe(0)
        ->and(DB::table('bukti_perilaku')->count())->toBe(0);
});
