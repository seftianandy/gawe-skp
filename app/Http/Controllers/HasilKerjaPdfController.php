<?php

namespace App\Http\Controllers;

use App\Models\HasilKerja;
use App\Services\GoogleDriveService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Throwable;

class HasilKerjaPdfController extends Controller
{
    public function export(HasilKerja $hasilKerja): Response|RedirectResponse
    {
        $load = [
            'laporan.user',
            'indikatorKinerjaMaster',
            'indikatorKinerja.rencanaAksi',
            'indikatorKinerja.realisasi.buktiFoto',
            'lampiranFiles',
        ];

        if (Schema::hasColumn('rencana_aksi', 'hasil_kerja_id')) {
            $load[] = 'rencanaAksiHasilKerja';
        }

        if (Schema::hasColumn('bukti_foto', 'hasil_kerja_id')) {
            $load[] = 'buktiFotoHasilKerja';
        }

        $hasilKerja->load($load);

        $this->authorize('view', $hasilKerja->laporan);

        if (! class_exists(Pdf::class)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Paket DOMPDF belum terpasang.']);

            return to_route('laporan.show', $hasilKerja->laporan);
        }

        $pdf = Pdf::loadView('pdf.hasil_kerja', [
            'hasilKerja' => $hasilKerja,
        ])->setPaper('a4');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="hasil-kerja-'.$hasilKerja->id.'.pdf"',
        ]);
    }

    public function upload(HasilKerja $hasilKerja, GoogleDriveService $googleDrive): RedirectResponse
    {
        $load = [
            'laporan.user',
            'indikatorKinerjaMaster',
            'indikatorKinerja.rencanaAksi',
            'indikatorKinerja.realisasi.buktiFoto',
            'lampiranFiles',
        ];

        if (Schema::hasColumn('rencana_aksi', 'hasil_kerja_id')) {
            $load[] = 'rencanaAksiHasilKerja';
        }

        if (Schema::hasColumn('bukti_foto', 'hasil_kerja_id')) {
            $load[] = 'buktiFotoHasilKerja';
        }

        $hasilKerja->load($load);

        $this->authorize('update', $hasilKerja->laporan);

        if (! $hasilKerja->laporan->user->google_id) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Google Drive belum terhubung.']);

            return to_route('laporan.show', $hasilKerja->laporan);
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'hasil-kerja-');

        file_put_contents($temporaryPath, Pdf::loadView('pdf.hasil_kerja', [
            'hasilKerja' => $hasilKerja,
        ])->setPaper('a4')->output());

        try {
            $googleDrive->uploadHasilKerjaPdf($hasilKerja->laporan->user, $hasilKerja->laporan, $hasilKerja, $temporaryPath);
            $googleDrive->uploadHasilKerjaLampiranFiles($hasilKerja->laporan->user, $hasilKerja->laporan, $hasilKerja);
        } catch (Throwable $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Upload Google Drive gagal: '.$exception->getMessage()]);

            return to_route('laporan.show', $hasilKerja->laporan);
        } finally {
            @unlink($temporaryPath);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'PDF hasil kerja berhasil diunggah ke Google Drive.']);

        return to_route('laporan.show', $hasilKerja->laporan);
    }
}
