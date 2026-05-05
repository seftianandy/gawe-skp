<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportLaporanPdfController;
use App\Http\Controllers\GenerateHasilKerjaAiController;
use App\Http\Controllers\GenerateLaporanAiController;
use App\Http\Controllers\GeneratePerilakuAiController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\HasilKerjaController;
use App\Http\Controllers\HasilKerjaPdfController;
use App\Http\Controllers\IndikatorKinerjaMasterController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PerilakuKerjaController;
use App\Http\Controllers\PerilakuPdfController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::inertia('/terms-of-service', 'Legal/TermsOfService')->name('terms');
Route::inertia('/privacy-policy', 'Legal/PrivacyPolicy')->name('privacy-policy');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('laporan', LaporanController::class);
    Route::resource('laporan.hasil-kerja', HasilKerjaController::class)->except(['index', 'show', 'create', 'edit']);
    Route::resource('laporan.perilaku-kerja', PerilakuKerjaController::class)->except(['index', 'show', 'create', 'edit']);
    Route::resource('indikator-kinerja-master', IndikatorKinerjaMasterController::class)->except(['show']);
    Route::post('laporan/{laporan}/generate-ai', GenerateLaporanAiController::class)->name('laporan.generate-ai');
    Route::get('laporan/{laporan}/export-pdf', ExportLaporanPdfController::class)->name('laporan.export-pdf');
    Route::post('hasil-kerja/{hasil_kerja}/generate-ai', GenerateHasilKerjaAiController::class)->name('hasil-kerja.generate-ai');
    Route::post('perilaku/{perilaku_kerja}/generate-ai', GeneratePerilakuAiController::class)->name('perilaku.generate-ai');
    Route::get('hasil-kerja/{hasil_kerja}/pdf', [HasilKerjaPdfController::class, 'export'])->name('hasil-kerja.pdf');
    Route::get('perilaku/{perilaku_kerja}/pdf', [PerilakuPdfController::class, 'export'])->name('perilaku.pdf');
    Route::post('hasil-kerja/{hasil_kerja}/upload-drive', [HasilKerjaPdfController::class, 'upload'])->name('hasil-kerja.upload-drive');
    Route::post('hasil-kerja/{id}/lampiran', [HasilKerjaController::class, 'uploadLampiran'])->name('hasil-kerja.upload-lampiran');
    Route::post('perilaku/{perilaku_kerja}/upload-drive', [PerilakuPdfController::class, 'upload'])->name('perilaku.upload-drive');
    Route::post('laporan/{laporan}/upload-drive', [LaporanController::class, 'uploadAllToDrive'])->name('laporan.upload-drive');
    Route::get('google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('google/callback', [GoogleController::class, 'callback'])->name('google.callback');
    Route::post('google/disconnect', [GoogleController::class, 'disconnect'])->name('google.disconnect');
});

require __DIR__.'/settings.php';
