<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLaporanRequest;
use App\Http\Requests\UpdateLaporanRequest;
use App\Models\IndikatorKinerjaMaster;
use App\Models\Laporan;
use App\Services\GoogleDriveService;
use App\Services\SkpAssetCleanupService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class LaporanController extends Controller
{
    public function __construct(
        protected SkpAssetCleanupService $assetCleanup,
    ) {
        $this->authorizeResource(Laporan::class, 'laporan');
    }

    public function index(): Response
    {
        $laporan = Auth::user()->laporan()
            ->withCount(['hasilKerja', 'perilakuKerja'])
            ->latest('periode')
            ->get();

        return Inertia::render('Laporan/Index', [
            'laporan' => $laporan->map(fn (Laporan $item) => [
                'id' => $item->id,
                'periode' => $item->periode?->toDateString(),
                'periode_label' => $item->periode?->translatedFormat('F Y'),
                'bulan' => $item->bulan,
                'tahun' => $item->tahun,
                'status' => $item->status,
                'hasil_kerja_count' => $item->hasil_kerja_count,
                'perilaku_kerja_count' => $item->perilaku_kerja_count,
                'isi_laporan' => $item->isi_laporan,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Laporan/Create', [
            'form' => [
                'periode' => now()->startOfMonth()->toDateString(),
                'bulan' => now()->month,
                'tahun' => now()->year,
                'status' => 'draft',
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StoreLaporanRequest $request): RedirectResponse
    {
        $laporan = DB::transaction(function () use ($request): Laporan {
            $laporan = $request->user()->laporan()->create($request->validated());

            $this->createDefaultPerilakuKerja($laporan);

            return $laporan;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Laporan berhasil dibuat.']);

        return to_route('laporan.show', $laporan);
    }

    public function show(Laporan $laporan): Response
    {
        $load = [
            'user',
            'hasilKerja.indikatorKinerjaMaster',
            'hasilKerja' => fn (HasMany $query) => $query->orderBy('id'),
            'hasilKerja.indikatorKinerja' => fn (HasMany $query) => $query->orderBy('id'),
            'hasilKerja.indikatorKinerja.rencanaAksi' => fn (HasMany $query) => $query->orderBy('id'),
            'hasilKerja.indikatorKinerja.realisasi' => fn (HasMany $query) => $query->orderBy('id'),
            'hasilKerja.indikatorKinerja.realisasi.buktiFoto' => fn (HasMany $query) => $query->orderBy('id'),
            'hasilKerja.lampiranFiles' => fn (HasMany $query) => $query->orderBy('id'),
            'perilakuKerja' => fn (HasMany $query) => $query->orderBy('id'),
            'perilakuKerja.buktiPerilaku' => fn (HasMany $query) => $query->orderBy('id'),
        ];

        if ($this->supportsDirectHasilKerjaRelations()) {
            $load[] = 'hasilKerja.rencanaAksiHasilKerja';
            $load[] = 'hasilKerja.buktiFotoHasilKerja';
        }

        $laporan->load($load);

        $indikatorKinerjaMasters = $this->indikatorKinerjaMastersFor($laporan);

        return Inertia::render('Laporan/Show', [
            'laporan' => $this->presentLaporan($laporan),
            'statusOptions' => $this->statusOptions(),
            'indikatorKinerjaMasters' => $indikatorKinerjaMasters->map(fn (IndikatorKinerjaMaster $item) => [
                'id' => $item->id,
                'nama_indikator' => $item->nama_indikator,
                'satuan' => $item->satuan,
                'target' => $item->target,
                'kategori' => $item->kategori,
            ])->values(),
        ]);
    }

    public function edit(Laporan $laporan): Response
    {
        return Inertia::render('Laporan/Edit', [
            'laporan' => [
                'id' => $laporan->id,
                'periode' => $laporan->periode?->toDateString(),
                'bulan' => $laporan->bulan,
                'tahun' => $laporan->tahun,
                'status' => $laporan->status,
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(UpdateLaporanRequest $request, Laporan $laporan): RedirectResponse
    {
        $laporan->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Laporan berhasil diperbarui.']);

        return to_route('laporan.show', $laporan);
    }

    public function destroy(Laporan $laporan): RedirectResponse
    {
        $this->assetCleanup->cleanupLaporan($laporan);
        $laporan->forceDelete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Laporan berhasil dihapus.']);

        return to_route('laporan.index');
    }

    public function uploadAllToDrive(Laporan $laporan, GoogleDriveService $googleDrive): RedirectResponse
    {
        $this->authorize('update', $laporan);

        if (! $laporan->user->google_id) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Google Drive belum terhubung.']);

            return to_route('laporan.show', $laporan);
        }

        $laporan->load([
            'user',
            'hasilKerja.indikatorKinerjaMaster',
            'hasilKerja.indikatorKinerja.rencanaAksi',
            'hasilKerja.indikatorKinerja.realisasi.buktiFoto',
            'hasilKerja.lampiranFiles',
            'perilakuKerja.buktiPerilaku',
        ]);

        try {
            foreach ($laporan->hasilKerja as $hasilKerja) {
                $temporaryPath = tempnam(sys_get_temp_dir(), 'hasil-kerja-');

                file_put_contents($temporaryPath, Pdf::loadView('pdf.hasil_kerja', [
                    'hasilKerja' => $hasilKerja,
                ])->setPaper('a4')->output());

                try {
                    $googleDrive->uploadHasilKerjaPdf($laporan->user, $laporan, $hasilKerja, $temporaryPath);
                    $googleDrive->uploadHasilKerjaLampiranFiles($laporan->user, $laporan, $hasilKerja);
                } finally {
                    @unlink($temporaryPath);
                }
            }

            foreach ($laporan->perilakuKerja as $perilakuKerja) {
                $temporaryPath = tempnam(sys_get_temp_dir(), 'perilaku-');

                file_put_contents($temporaryPath, Pdf::loadView('pdf.perilaku', [
                    'perilakuKerja' => $perilakuKerja,
                ])->setPaper('a4')->output());

                try {
                    $googleDrive->uploadPerilakuPdf($laporan->user, $laporan, $perilakuKerja, $temporaryPath);
                } finally {
                    @unlink($temporaryPath);
                }
            }
        } catch (Throwable $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Upload Google Drive gagal: '.$exception->getMessage()]);

            return to_route('laporan.show', $laporan);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Semua PDF laporan berhasil diunggah ke Google Drive.']);

        return to_route('laporan.show', $laporan);
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    protected function statusOptions(): array
    {
        return [
            ['label' => 'Draft', 'value' => 'draft'],
            ['label' => 'Submit', 'value' => 'submit'],
            ['label' => 'Final', 'value' => 'final'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentLaporan(Laporan $laporan): array
    {
        $useDirectRelations = $this->supportsDirectHasilKerjaRelations();

        return [
            'id' => $laporan->id,
            'periode' => $laporan->periode?->toDateString(),
            'periode_label' => $laporan->periode?->translatedFormat('F Y'),
            'bulan' => $laporan->bulan,
            'tahun' => $laporan->tahun,
            'status' => $laporan->status,
            'isi_laporan' => $laporan->isi_laporan,
            'file_pdf' => $laporan->file_pdf ? Storage::disk('public')->url($laporan->file_pdf) : null,
            'user' => [
                'nama' => $laporan->user->nama,
                'nama_instansi' => $laporan->user->nama_instansi,
                'jabatan' => $laporan->user->jabatan,
                'nip' => $laporan->user->nip,
                'email' => $laporan->user->email,
            ],
            'hasil_kerja' => $laporan->hasilKerja->map(function ($hasilKerja) use ($useDirectRelations) {
                $rencanaAksi = $useDirectRelations
                    ? $hasilKerja->rencanaAksiHasilKerja
                    : $hasilKerja->indikatorKinerja->flatMap->rencanaAksi;

                $buktiFoto = $useDirectRelations
                    ? $hasilKerja->buktiFotoHasilKerja
                    : $hasilKerja->indikatorKinerja->flatMap(function ($indikator) {
                        return $indikator->realisasi->flatMap->buktiFoto;
                    });

                return [
                    'id' => $hasilKerja->id,
                    'indikator_kinerja_id' => $hasilKerja->indikator_kinerja_master_id,
                    'indikator_kinerja_master' => $hasilKerja->indikatorKinerjaMaster ? [
                        'id' => $hasilKerja->indikatorKinerjaMaster->id,
                        'nama_indikator' => $hasilKerja->indikatorKinerjaMaster->nama_indikator,
                        'satuan' => $hasilKerja->indikatorKinerjaMaster->satuan,
                        'target' => $hasilKerja->indikatorKinerjaMaster->target,
                        'kategori' => $hasilKerja->indikatorKinerjaMaster->kategori,
                    ] : null,
                    'isi_ai' => $hasilKerja->isi_ai,
                    'rencana_aksi' => $rencanaAksi->map(fn ($rencana) => [
                        'id' => $rencana->id,
                        'deskripsi' => $rencana->deskripsi,
                    ])->values(),
                    'bukti_foto' => $buktiFoto->map(fn ($bukti) => [
                        'id' => $bukti->id,
                        'file_path' => $bukti->file_path,
                        'url' => Storage::disk('public')->url($bukti->file_path),
                    ])->values(),
                    'lampiran_files' => $hasilKerja->lampiranFiles->map(fn ($lampiran) => [
                        'id' => $lampiran->id,
                        'nama_file' => $lampiran->nama_file,
                        'file_path' => $lampiran->file_path,
                        'url' => Storage::disk('public')->url($lampiran->file_path),
                    ])->values(),
                    'indikators' => $hasilKerja->indikatorKinerja->map(fn ($indikator) => [
                        'id' => $indikator->id,
                        'deskripsi' => $indikator->deskripsi,
                        'satuan' => $indikator->satuan,
                        'target' => $indikator->target,
                        'kategori' => $indikator->kategori,
                        'rencana_aksi' => $indikator->rencanaAksi->map(fn ($rencana) => [
                            'id' => $rencana->id,
                            'deskripsi' => $rencana->deskripsi,
                        ])->values(),
                        'realisasi' => $indikator->realisasi->map(fn ($realisasi) => [
                            'id' => $realisasi->id,
                            'tanggal' => $realisasi->tanggal?->toDateString(),
                            'output' => $realisasi->output,
                            'keterangan' => $realisasi->keterangan,
                            'bukti_foto' => $realisasi->buktiFoto->map(fn ($bukti) => [
                                'id' => $bukti->id,
                                'file_path' => $bukti->file_path,
                                'url' => Storage::disk('public')->url($bukti->file_path),
                            ])->values(),
                        ])->values(),
                    ])->values(),
                ];
            })->values(),
            'perilaku_kerja' => $laporan->perilakuKerja->map(fn ($perilaku) => [
                'id' => $perilaku->id,
                'nama' => $perilaku->nama,
                'deskripsi' => $perilaku->deskripsi,
                'isi_ai' => $perilaku->isi_ai,
                'bukti_perilaku' => $perilaku->buktiPerilaku->map(fn ($bukti) => [
                    'id' => $bukti->id,
                    'file_path' => $bukti->file_path,
                    'url' => Storage::disk('public')->url($bukti->file_path),
                ])->values(),
            ])->values(),
        ];
    }

    protected function supportsDirectHasilKerjaRelations(): bool
    {
        return Schema::hasColumn('rencana_aksi', 'hasil_kerja_id')
            && Schema::hasColumn('bukti_foto', 'hasil_kerja_id');
    }

    /**
     * @return array<int, string>
     */
    protected function defaultPerilakuNames(): array
    {
        return [
            'Berorientasi Pelayanan',
            'Akuntabel',
            'Kompeten',
            'Harmonis',
            'Loyal',
            'Adaptif',
            'Kolaboratif',
        ];
    }

    protected function createDefaultPerilakuKerja(Laporan $laporan): void
    {
        foreach ($this->defaultPerilakuNames() as $nama) {
            $laporan->perilakuKerja()->firstOrCreate(
                ['nama' => $nama],
                ['deskripsi' => ''],
            );
        }
    }

    /**
     * @return Collection<int, IndikatorKinerjaMaster>
     */
    protected function indikatorKinerjaMastersFor(Laporan $laporan)
    {
        if (! Schema::hasTable('indikator_kinerja_masters')) {
            return collect();
        }

        return IndikatorKinerjaMaster::query()
            ->where(function ($query) use ($laporan): void {
                $query->whereNull('user_id')
                    ->orWhere('user_id', $laporan->user_id);
            })
            ->orderBy('nama_indikator')
            ->get();
    }
}
