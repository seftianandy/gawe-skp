<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $user = Auth::user();
        $laporanQuery = $user->laporan();
        $now = now();

        return Inertia::render('Dashboard', [
            'summary' => [
                'jumlah_laporan' => $laporanQuery->count(),
                'laporan_bulan_berjalan' => $user->laporan()
                    ->whereYear('periode', $now->year)
                    ->whereMonth('periode', $now->month)
                    ->count(),
                'status' => [
                    'draft' => $user->laporan()->where('status', 'draft')->count(),
                    'submit' => $user->laporan()->where('status', 'submit')->count(),
                    'final' => $user->laporan()->where('status', 'final')->count(),
                ],
            ],
            'laporanTerbaru' => $user->laporan()
                ->latest('periode')
                ->limit(5)
                ->get(['id', 'periode', 'bulan', 'tahun', 'status']),
        ]);
    }
}
