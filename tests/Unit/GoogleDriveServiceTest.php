<?php

use App\Models\HasilKerja;
use App\Models\IndikatorKinerjaMaster;
use App\Models\Laporan;
use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

function createGoogleDriveLaporanFor(User $user): HasilKerja
{
    $laporan = Laporan::create([
        'user_id' => $user->id,
        'periode' => '2026-05-01',
        'bulan' => 5,
        'tahun' => 2026,
        'status' => 'draft',
    ]);

    $master = IndikatorKinerjaMaster::factory()->create([
        'user_id' => $user->id,
        'nama_indikator' => 'Koordinasi program kerja',
        'satuan' => 'kegiatan',
        'target' => '1',
        'kategori' => 'kualitas',
    ]);

    return $laporan->hasilKerja()->create([
        'indikator_kinerja_master_id' => $master->id,
    ]);
}

test('google drive service reads upload files as strings', function () {
    $service = app(GoogleDriveService::class);
    $filePath = tempnam(sys_get_temp_dir(), 'google-drive-test-');

    try {
        file_put_contents($filePath, 'hello google drive');

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('readFileContents');
        $method->setAccessible(true);

        $contents = $method->invoke($service, $filePath);

        expect($contents)->toBeString()
            ->and($contents)->toBe('hello google drive');
    } finally {
        @unlink($filePath);
    }
});

test('google drive service uploads hasil kerja pdf and lampiran files with the expected names', function () {
    $user = User::factory()->create([
        'google_drive_root' => 'SKP Laporan',
    ]);
    $hasilKerja = createGoogleDriveLaporanFor($user);

    $hasilKerja->lampiranFiles()->createMany([
        [
            'nama_file' => 'lampiran-1.pdf',
            'file_path' => 'lampiran/'.$hasilKerja->id.'/lampiran-1.pdf',
        ],
        [
            'nama_file' => 'lampiran-2.pdf',
            'file_path' => 'lampiran/'.$hasilKerja->id.'/lampiran-2.pdf',
        ],
    ]);

    $service = new class extends GoogleDriveService
    {
        public array $calls = [];

        public function uploadFile(User $user, string $filePath, string $fileName, string $folderPath): string
        {
            $this->calls[] = [
                'file_path' => $filePath,
                'file_name' => $fileName,
                'folder_path' => $folderPath,
            ];

            return 'drive-file-id';
        }
    };

    app()->instance(GoogleDriveService::class, $service);

    $temporaryPath = tempnam(sys_get_temp_dir(), 'hasil-kerja-');
    file_put_contents($temporaryPath, 'pdf content');

    try {
        $service->uploadHasilKerjaPdf($user, $hasilKerja->laporan, $hasilKerja, $temporaryPath);
        $service->uploadHasilKerjaLampiranFiles($user, $hasilKerja->laporan, $hasilKerja);
    } finally {
        @unlink($temporaryPath);
    }

    expect($service->calls)->toHaveCount(3)
        ->and($service->calls[0]['file_name'])->toBe('laporan.pdf')
        ->and($service->calls[0]['folder_path'])->toContain('SKP Laporan/')
        ->and($service->calls[0]['folder_path'])->toContain('Koordinasi program kerja')
        ->and($service->calls[1]['file_name'])->toBe('lampiran-1.pdf')
        ->and($service->calls[2]['file_name'])->toBe('lampiran-2.pdf')
        ->and($service->calls[1]['folder_path'])->toBe($service->calls[0]['folder_path'])
        ->and($service->calls[2]['folder_path'])->toBe($service->calls[0]['folder_path']);
});
