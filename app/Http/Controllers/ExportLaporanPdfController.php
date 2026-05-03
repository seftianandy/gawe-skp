<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class ExportLaporanPdfController extends Controller
{
    public function __invoke(Laporan $laporan): Response|RedirectResponse
    {
        $this->authorize('view', $laporan);

        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Paket DOMPDF belum terpasang.']);

            return to_route('laporan.show', $laporan);
        }

        $load = [
            'user',
            'hasilKerja.indikatorKinerjaMaster',
            'hasilKerja.indikatorKinerja.rencanaAksi',
            'hasilKerja.indikatorKinerja.realisasi.buktiFoto',
            'perilakuKerja.buktiPerilaku',
        ];

        if (Schema::hasColumn('rencana_aksi', 'hasil_kerja_id')) {
            $load[] = 'hasilKerja.rencanaAksiHasilKerja';
        }

        if (Schema::hasColumn('bukti_foto', 'hasil_kerja_id')) {
            $load[] = 'hasilKerja.buktiFotoHasilKerja';
        }

        $laporan->load($load);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf', [
            'laporan' => $laporan,
        ])->setPaper('a4');

        $filePath = sprintf('skp/pdf/laporan-%d.pdf', $laporan->id);

        Storage::disk('public')->put($filePath, $pdf->output());

        $laporan->update([
            'file_pdf' => $filePath,
        ]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="laporan-skp-'.$laporan->id.'.pdf"',
        ]);
    }
}
