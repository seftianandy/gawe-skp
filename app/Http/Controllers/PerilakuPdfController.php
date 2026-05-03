<?php

namespace App\Http\Controllers;

use App\Models\PerilakuKerja;
use App\Services\GoogleDriveService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Throwable;
use Inertia\Inertia;

class PerilakuPdfController extends Controller
{
    public function export(PerilakuKerja $perilakuKerja): Response|RedirectResponse
    {
        $perilakuKerja->load([
            'laporan.user',
            'buktiPerilaku',
        ]);

        $this->authorize('view', $perilakuKerja->laporan);

        if (! class_exists(Pdf::class)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Paket DOMPDF belum terpasang.']);

            return to_route('laporan.show', $perilakuKerja->laporan);
        }

        $pdf = Pdf::loadView('pdf.perilaku', [
            'perilakuKerja' => $perilakuKerja,
        ])->setPaper('a4');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="perilaku-'.$perilakuKerja->id.'.pdf"',
        ]);
    }

    public function upload(PerilakuKerja $perilakuKerja, GoogleDriveService $googleDrive): RedirectResponse
    {
        $perilakuKerja->load([
            'laporan.user',
            'buktiPerilaku',
        ]);

        $this->authorize('update', $perilakuKerja->laporan);

        if (! $perilakuKerja->laporan->user->google_id) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Google Drive belum terhubung.']);

            return to_route('laporan.show', $perilakuKerja->laporan);
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'perilaku-');

        file_put_contents($temporaryPath, Pdf::loadView('pdf.perilaku', [
            'perilakuKerja' => $perilakuKerja,
        ])->setPaper('a4')->output());

        try {
            $googleDrive->uploadPerilakuPdf($perilakuKerja->laporan->user, $perilakuKerja->laporan, $perilakuKerja, $temporaryPath);
        } catch (Throwable $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Upload Google Drive gagal: '.$exception->getMessage()]);

            return to_route('laporan.show', $perilakuKerja->laporan);
        } finally {
            @unlink($temporaryPath);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'PDF perilaku kerja berhasil diunggah ke Google Drive.']);

        return to_route('laporan.show', $perilakuKerja->laporan);
    }
}
