<?php

namespace App\Services\AI;

use App\Models\HasilKerja;
use App\Models\PerilakuKerja;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class GenerateLaporanService
{
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Generate narasi laporan kinerja untuk HasilKerja.
     */
    public function generateHasilKerja(HasilKerja $hasilKerja): string
    {
        $useDirectRelations = Schema::hasColumn('rencana_aksi', 'hasil_kerja_id')
            && Schema::hasColumn('bukti_foto', 'hasil_kerja_id');

        $payload = [
            'indikator_kinerja_master' => $hasilKerja->indikatorKinerjaMaster ? [
                'nama_indikator' => $hasilKerja->indikatorKinerjaMaster->nama_indikator,
                'satuan' => $hasilKerja->indikatorKinerjaMaster->satuan,
                'target' => $hasilKerja->indikatorKinerjaMaster->target,
                'kategori' => $hasilKerja->indikatorKinerjaMaster->kategori,
            ] : null,
            'rencana_aksi' => $useDirectRelations
                ? $hasilKerja->rencanaAksiHasilKerja->pluck('deskripsi')->values()->all()
                : $hasilKerja->indikatorKinerja->flatMap(fn ($indikator) => $indikator->rencanaAksi->pluck('deskripsi'))->values()->all(),
            'bukti_foto' => $useDirectRelations
                ? $hasilKerja->buktiFotoHasilKerja->map(fn ($bukti) => [
                    'file_path' => $bukti->file_path,
                ])->all()
                : $hasilKerja->indikatorKinerja->flatMap(fn ($indikator) => $indikator->realisasi->flatMap(fn ($realisasi) => $realisasi->buktiFoto->map(fn ($bukti) => [
                    'file_path' => $bukti->file_path,
                ])))->all(),
            'indikator_kinerja' => $hasilKerja->indikatorKinerja->map(fn ($indikator) => [
                'deskripsi'    => $indikator->deskripsi,
                'satuan'       => $indikator->satuan,
                'target'       => $indikator->target,
                'kategori'     => $indikator->kategori,
                'rencana_aksi' => $indikator->rencanaAksi->pluck('deskripsi')->all(),
                'realisasi'    => $indikator->realisasi->map(fn ($realisasi) => [
                    'tanggal'    => $realisasi->tanggal?->toDateString(),
                    'output'     => $realisasi->output,
                    'keterangan' => $realisasi->keterangan,
                ])->all(),
            ])->all(),
        ];

        return $this->generateWithGemini($payload, 'hasil_kerja')
            ?? $this->mockFormalNarrativeForHasilKerja($payload);
    }

    /**
     * Generate narasi laporan kinerja untuk PerilakuKerja.
     */
    public function generatePerilaku(PerilakuKerja $perilaku): string
    {
        $payload = [
            'nama'      => $perilaku->nama,
            'deskripsi' => $perilaku->deskripsi,
            'realisasi' => $perilaku->buktiPerilaku->map(fn ($bukti) => [
                'bukti' => $bukti->file_path,
            ])->all(),
        ];

        return $this->generateWithGemini($payload, 'perilaku')
            ?? $this->mockFormalNarrativeForPerilaku($payload);
    }

    // -------------------------------------------------------------------------
    // Gemini Integration
    // -------------------------------------------------------------------------

    /**
     * Kirim payload ke Gemini API dan kembalikan teks narasi.
     * Mengembalikan null apabila API tidak tersedia atau mengalami kegagalan.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function generateWithGemini(array $payload, string $type): ?string
    {
        $apiKey = config('services.gemini.key');

        if (blank($apiKey)) {
            Log::warning('GEMINI_API_KEY belum dikonfigurasi. Menggunakan narasi fallback.', ['type' => $type]);
            return null;
        }

        $prompt = match ($type) {
            'hasil_kerja' => $this->buildPromptHasilKerja($payload),
            'perilaku'    => $this->buildPromptPerilaku($payload),
        };

        try {
            $response = Http::timeout(30)->post(self::GEMINI_API_URL . "?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
            ]);

            if ($response->failed()) {
                Log::error('Gemini API mengembalikan respons gagal.', [
                    'type'   => $type,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return null;
            }

            $teks = data_get($response->json(), 'candidates.0.content.parts.0.text');

            if (blank($teks)) {
                Log::warning('Gemini API mengembalikan teks kosong.', [
                    'type'     => $type,
                    'response' => $response->json(),
                ]);

                return null;
            }

            return trim($teks);

        } catch (\Throwable $e) {
            Log::error('Terjadi pengecualian saat memanggil Gemini API.', [
                'type'    => $type,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    // -------------------------------------------------------------------------
    // Prompt Builder
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function buildPromptHasilKerja(array $payload): string
    {
        $data = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $namaIndikator = data_get($payload, 'indikator_kinerja_master.nama_indikator', 'hasil kerja');

        return <<<PROMPT
        Anda adalah asisten penyusun laporan kinerja aparatur sipil negara.

        Susunlah narasi laporan hasil kerja pegawai berdasarkan data berikut dalam bahasa Indonesia formal sesuai kaidah bahasa kedinasan dan Ejaan Yang Disempurnakan (EYD).

        Data laporan:
        {$data}

        Ketentuan penulisan:
        - Gunakan ragam bahasa resmi pemerintahan Indonesia.
        - Gunakan konstruksi kalimat pasif formal: "telah dilaksanakan", "berdasarkan hasil pelaksanaan", "kegiatan ini bertujuan", "dapat dilaporkan bahwa".
        - Hindari penggunaan kata ganti orang pertama ("saya", "kami").
        - Sajikan narasi dalam dua hingga tiga paragraf tanpa daftar butir maupun penomoran.
        - Paragraf pertama: gambaran umum kegiatan dan tujuannya pada indikator "{$namaIndikator}".
        - Paragraf kedua: uraian proses pelaksanaan berdasarkan rencana aksi dan bukti foto yang terdokumentasi.
        - Paragraf ketiga: simpulan capaian realisasi secara keseluruhan.
        - Hindari pengulangan frasa atau informasi yang sama.
        - Narasi bersifat objektif, terukur, dan dapat dipertanggungjawabkan.

        Keluaran hanya berupa teks narasi laporan, tanpa judul dan tanpa keterangan tambahan.
        PROMPT;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function buildPromptPerilaku(array $payload): string
    {
        $data = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
        Anda adalah asisten penyusun laporan kinerja aparatur sipil negara.

        Susunlah narasi laporan perilaku kerja pegawai berdasarkan data berikut dalam bahasa Indonesia formal sesuai kaidah bahasa kedinasan dan Ejaan Yang Disempurnakan (EYD).

        Data laporan:
        {$data}

        Ketentuan penulisan:
        - Gunakan ragam bahasa resmi pemerintahan Indonesia.
        - Gunakan konstruksi kalimat pasif formal: "telah ditunjukkan", "tercermin dalam", "dapat dinyatakan bahwa", "sesuai dengan nilai organisasi".
        - Hindari penggunaan kata ganti orang pertama ("saya", "kami").
        - Sajikan narasi dalam dua hingga tiga paragraf tanpa daftar butir maupun penomoran.
        - Paragraf pertama: gambaran umum perilaku kerja yang ditampilkan.
        - Paragraf kedua: uraian bukti-bukti pendukung yang mendokumentasikan perilaku tersebut.
        - Paragraf ketiga: relevansi perilaku kerja terhadap nilai dan tujuan organisasi.
        - Hindari pengulangan frasa atau informasi yang sama.
        - Narasi bersifat objektif dan dapat dipertanggungjawabkan.

        Keluaran hanya berupa teks narasi laporan, tanpa judul dan tanpa keterangan tambahan.
        PROMPT;
    }

    // -------------------------------------------------------------------------
    // Mock Fallback (digunakan saat Gemini API tidak tersedia)
    // -------------------------------------------------------------------------

    /**
     * @param  array{
     *     indikator_kinerja_master: array{nama_indikator: string, satuan: string, target: string, kategori: string}|null,
     *     rencana_aksi: array<int, string>,
     *     bukti_foto: array<int, array{file_path: string}>,
     *     indikator_kinerja: array<int, array{
     *         deskripsi: string,
     *         satuan: string,
     *         target: string,
     *         kategori: string,
     *         rencana_aksi: array<int, string>,
     *         realisasi: array<int, array{
     *             tanggal: string|null,
     *             output: string,
     *             keterangan: string|null
     *         }>
     *     }>
     * }  $payload
     */
    protected function mockFormalNarrativeForHasilKerja(array $payload): string
    {
        $namaIndikator = data_get($payload, 'indikator_kinerja_master.nama_indikator', 'hasil kerja');

        $indikatorNarasi = collect($payload['indikator_kinerja'])
            ->map(function (array $indikator, int $index): string {
                $rencanaAksi = $indikator['rencana_aksi'] === []
                    ? 'Rencana aksi belum terdokumentasi secara rinci.'
                    : 'Rencana aksi meliputi ' . collect($indikator['rencana_aksi'])->implode('; ') . '.';

                $realisasi = $indikator['realisasi'] === []
                    ? 'Belum terdapat realisasi yang dicatat pada periode ini.'
                    : 'Realisasi yang telah dicapai antara lain ' . collect($indikator['realisasi'])
                        ->map(fn (array $item) => trim(sprintf(
                            '%s berupa %s%s',
                            $item['tanggal'] ?? 'tanpa tanggal',
                            $item['output'],
                            $item['keterangan'] ? ' dengan catatan ' . $item['keterangan'] : ''
                        )))
                        ->implode('; ') . '.';

                return sprintf(
                    '%d. Indikator %s berfokus pada %s dengan target %s %s. %s %s',
                    $index + 1,
                    $indikator['kategori'],
                    $indikator['deskripsi'],
                    $indikator['target'],
                    $indikator['satuan'],
                    $rencanaAksi,
                    $realisasi,
                );
            })
            ->implode("\n");

        $rencanaAksi = collect($payload['rencana_aksi'] ?? [])
            ->filter()
            ->implode('; ');

        $buktiFotoCount = count($payload['bukti_foto'] ?? []);

        return trim(<<<TEXT
        Berdasarkan data yang telah terdokumentasi, pelaksanaan kinerja pada indikator "{$namaIndikator}" telah diarahkan secara terukur sesuai dengan target yang ditetapkan.

        {$indikatorNarasi}

        Rencana aksi yang telah disusun meliputi {$rencanaAksi}. Dokumentasi pelaksanaan didukung oleh {$buktiFotoCount} file bukti foto yang telah diunggah.

        Secara keseluruhan, kegiatan pada hasil kerja ini mencerminkan upaya yang sistematis dalam mencapai target kinerja, menjaga kualitas pelaksanaan, serta memastikan setiap realisasi memiliki dasar tindak lanjut yang dapat dipertanggungjawabkan.
        TEXT);
    }

    /**
     * @param  array{
     *     nama: string,
     *     deskripsi: string|null,
     *     realisasi: array<int, array{bukti: string}>
     * }  $payload
     */
    protected function mockFormalNarrativeForPerilaku(array $payload): string
    {
        $jumlahBukti = count($payload['realisasi']);
        $buktiNarasi = $jumlahBukti > 0
            ? "Perilaku kerja tersebut didukung oleh {$jumlahBukti} bukti pendukung yang menunjukkan konsistensi penerapan di lapangan."
            : 'Belum terdapat bukti pendukung yang diunggah pada periode ini, sehingga dokumentasi perilaku masih perlu diperkuat.';

        return trim(<<<TEXT
        Berdasarkan data yang tercatat, pegawai telah menunjukkan perilaku kerja "{$payload['nama']}" yang selaras dengan nilai organisasi melalui praktik kerja sehari-hari.

        Deskripsi perilaku yang terdokumentasi adalah sebagai berikut: {$payload['deskripsi']}. {$buktiNarasi}

        Secara keseluruhan, perilaku kerja yang ditampilkan mencerminkan komitmen profesional, kemampuan menjaga kualitas layanan, serta kesediaan menjalankan tugas secara bertanggung jawab dan berorientasi pada hasil yang optimal.
        TEXT);
    }
}
