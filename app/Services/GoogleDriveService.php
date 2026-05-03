<?php

namespace App\Services;

use App\Models\HasilKerja;
use App\Models\Laporan;
use App\Models\PerilakuKerja;
use App\Models\User;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Carbon;
use RuntimeException;

class GoogleDriveService
{
    public function uploadFile(User $user, string $filePath, string $fileName, string $folderPath): string
    {
        $drive = $this->makeDriveService($user);
        $folderId = $this->ensureFolderPath($drive, $folderPath);
        $fileContents = $this->readFileContents($filePath);

        $file = new DriveFile([
            'name' => $fileName,
            'parents' => [$folderId],
        ]);

        $uploadedFile = $drive->files->create($file, [
            'data' => $fileContents,
            'mimeType' => $this->resolveMimeType($filePath),
            'uploadType' => 'multipart',
            'fields' => 'id',
            'supportsAllDrives' => true,
        ]);

        return (string) $uploadedFile->id;
    }

    public function uploadHasilKerjaPdf(User $user, Laporan $laporan, $hasilKerja, string $localPath)
    {
        // Mengambil root folder dari kolom yang baru kita buat
        $rootFolder = $user->google_drive_root ?? 'SKP Laporan';

        $basePath = $this->baseFolderPath($laporan, $rootFolder);
        $bookNumber = min(max($this->resolveHasilKerjaSequence($laporan, $hasilKerja), 1), 3);

        $folderPath = implode('/', array_merge($basePath, ["Buku Kerja $bookNumber"]));

        return $this->uploadFile($user, $localPath, $this->hasilKerjaFileName($laporan, $hasilKerja), $folderPath);
    }

    public function uploadPerilakuPdf(User $user, Laporan $laporan, PerilakuKerja $perilaku, string $localPath): string
    {
        $rootFolder = $user->google_drive_root ?? 'SKP Laporan';

        $basePath = $this->baseFolderPath($laporan, $rootFolder);
        $folderPath = implode('/', array_merge($basePath, [
            'Perilaku BerAKHLAK',
            $this->sanitizeFolderName($perilaku->nama)
        ]));

        return $this->uploadFile($user, $localPath, $this->perilakuFileName($laporan, $perilaku), $folderPath);
    }

    /**
     * Mendapatkan path dasar: [Nama Folder Custom]/[Nama Bulan]
     */
    protected function baseFolderPath(Laporan $laporan, string $rootFolderName): array
    {
        // Cek apakah 'periode' ada, jika null gunakan waktu sekarang sebagai cadangan
        $tanggal = $laporan->periode ?? now();

        return [
            $rootFolderName,
            $tanggal->translatedFormat('F') // Contoh: "Mei"
        ];
    }

    public function makeDriveService(User $user): GoogleDrive
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline');
        $client->setScopes(['https://www.googleapis.com/auth/drive']);

        $tokenPayload = $this->resolveTokenPayload($user);
        $client->setAccessToken($tokenPayload);

        if ($client->isAccessTokenExpired()) {
            $refreshToken = $user->google_refresh_token ?: ($tokenPayload['refresh_token'] ?? null);

            if (! $refreshToken) {
                throw new RuntimeException('Google refresh token tidak tersedia.');
            }

            $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

            if (isset($newToken['error'])) {
                throw new RuntimeException('Gagal me-refresh token Google Drive.');
            }

            $tokenPayload = [
                ...$tokenPayload,
                ...$newToken,
                'refresh_token' => $refreshToken,
                'created' => now()->timestamp,
            ];

            $user->forceFill([
                'google_token' => json_encode($tokenPayload, JSON_THROW_ON_ERROR),
                'google_refresh_token' => $refreshToken,
            ])->save();

            $client->setAccessToken($tokenPayload);
        }

        return new GoogleDrive($client);
    }

    public function ensureFolderPath(GoogleDrive $drive, string $folderPath): string
    {
        $segments = collect(explode('/', trim($folderPath, '/')))
            ->filter()
            ->values();

        $parentId = 'root';

        foreach ($segments as $segment) {
            $parentId = $this->findOrCreateFolder($drive, (string) $segment, $parentId);
        }

        return $parentId;
    }

    protected function findOrCreateFolder(GoogleDrive $drive, string $folderName, string $parentId): string
    {
        $existing = $drive->files->listFiles([
            'q' => sprintf(
                "mimeType = 'application/vnd.google-apps.folder' and name = '%s' and '%s' in parents and trashed = false",
                $this->escapeDriveQueryValue($folderName),
                $parentId
            ),
            'fields' => 'files(id, name)',
            'pageSize' => 1,
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        if ($existing->getFiles() !== []) {
            return (string) $existing->getFiles()[0]->getId();
        }

        $folder = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId],
        ]);

        return (string) $drive->files->create($folder, [
            'fields' => 'id',
            'supportsAllDrives' => true,
        ])->id;
    }

    protected function resolveTokenPayload(User $user): array
    {
        if (! $user->google_token) {
            throw new RuntimeException('Google token belum tersedia untuk user ini.');
        }

        $decoded = json_decode($user->google_token, true);

        if (is_array($decoded) && isset($decoded['access_token'])) {
            return $decoded;
        }

        return [
            'access_token' => $user->google_token,
            'refresh_token' => $user->google_refresh_token,
            'created' => now()->timestamp,
        ];
    }

    protected function resolveHasilKerjaSequence(Laporan $laporan, HasilKerja $hasilKerja): int
    {
        $orderedIds = $laporan->hasilKerja()
            ->orderBy('id')
            ->pluck('id')
            ->values();

        $index = $orderedIds->search($hasilKerja->id);

        return $index === false ? 1 : $index + 1;
    }

    protected function hasilKerjaFileName(Laporan $laporan, HasilKerja $hasilKerja): string
    {
        $namaIndikator = $hasilKerja->indikatorKinerjaMaster?->nama_indikator
            ?? $hasilKerja->indikatorKinerja->first()?->deskripsi
            ?? sprintf('hasil-kerja-%d', $hasilKerja->id);

        return sprintf('hasil-kerja-%d-%s.pdf', $laporan->id, str($namaIndikator)->slug());
    }

    protected function perilakuFileName(Laporan $laporan, PerilakuKerja $perilakuKerja): string
    {
        return sprintf('perilaku-%d-%s.pdf', $laporan->id, str($perilakuKerja->nama)->slug());
    }

    protected function sanitizeFolderName(string $folderName): string
    {
        return str($folderName)
            ->replace(['/', '\\'], '-')
            ->trim()
            ->toString();
    }

    protected function escapeDriveQueryValue(string $value): string
    {
        return addcslashes($value, "\\'");
    }

    protected function readFileContents(string $filePath): string
    {
        $contents = file_get_contents($filePath);

        if ($contents === false) {
            throw new RuntimeException(sprintf('Gagal membaca file untuk upload Google Drive: %s', $filePath));
        }

        return $contents;
    }

    protected function resolveMimeType(string $filePath): string
    {
        $mimeType = mime_content_type($filePath);

        return is_string($mimeType) && $mimeType !== '' ? $mimeType : 'application/octet-stream';
    }
}
