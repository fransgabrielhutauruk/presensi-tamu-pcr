<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    function index()
    {
        $this->title        = 'Dashboard';
        $this->activeMenu   = 'dashboard';
        $this->breadCrump[] = ['title' => 'Dashboard', 'link' => url()->current()];

        $userRole = session('active_role');

        $dashboardData = $this->getDashboardDataByRole($userRole);

        $this->dataView([
            'userRole' => $userRole,
            'dashboardData' => $dashboardData
        ]);

        return $this->view('admin.dashboard');
    }

    private function getDashboardDataByRole($role)
    {
        $data = [];

        switch ($role) {
            case 'Admin':
                $data = $this->getAdminDashboardData();
                break;
            case 'Eksekutif':
                $data = $this->getEksekutifDashboardData();
                break;
            case 'Security':
                $data = $this->getSecurityDashboardData();
                break;
            default:
                $data = [];
        }

        return $data;
    }

    private function getAdminDashboardData()
    {
        return [
            'title' => 'Dashboard Admin',
            'widgets' => [
                [
                    'title' => 'Total Kunjungan',
                    'value' => \App\Models\Kunjungan::get()->count(),
                    'icon' => 'ki-people',
                    'color' => 'success',
                    'link' => route('app.kunjungan.index')
                ],
                [
                    'title' => 'Kunjungan Event',
                    'value' => \App\Models\Kunjungan::whereNotNull('event_id')->count(),
                    'icon' => 'ki-calendar-tick',
                    'color' => 'info',
                    'link' => route('app.kunjungan.index')
                ],
                [
                    'title' => 'Kunjungan Non-Event',
                    'value' => \App\Models\Kunjungan::whereNull('event_id')->count(),
                    'icon' => 'ki-user',
                    'color' => 'primary',
                    'link' => route('app.kunjungan.index')
                ],
                [
                    'title' => 'Validasi Kunjungan',
                    'value' => \App\Models\Kunjungan::where('status_validasi', false)->count(),
                    'icon' => 'ki-notification-status',
                    'color' => 'warning',
                    'link' => route('app.kunjungan.validasi')
                ],
                [
                    'title' => 'Total Event',
                    'value' => \App\Models\Event::count(),
                    'icon' => 'ki-calendar',
                    'color' => 'success',
                    'link' => route('app.event.index')
                ],
                [
                    'title' => 'Event Mendatang',
                    'value' => \App\Models\Event::whereDate('tanggal_event', '>=', today())->count(),
                    'icon' => 'ki-calendar-add',
                    'color' => 'warning',
                    'link' => route('app.event.index')
                ],
                [
                    'title' => 'Total Feedback',
                    'value' => \App\Models\Feedback::count(),
                    'icon' => 'ki-message-text',
                    'color' => 'danger',
                    'link' => route('app.feedback.index')
                ],
                [
                    'title' => 'Total Pengguna',
                    'value' => \App\Models\User::count(),
                    'icon' => 'ki-profile-user',
                    'color' => 'dark',
                    'link' => route('app.user.index')
                ],
            ],
            'charts' => [
                'kunjunganPerBulan' => $this->getKunjunganPerBulan(),
                'eventAktifHariIni' => $this->getEventAktifHariIni(),
            ]
        ];
    }

    private function getEksekutifDashboardData()
    {
        return [
            'title' => 'Dashboard Eksekutif',
            'widgets' => [
                [
                    'title' => 'Kunjungan Hari Ini',
                    'value' => \App\Models\Kunjungan::whereDate('created_at', today())->count(),
                    'icon' => 'ki-calendar-tick',
                    'color' => 'primary'
                ],
                [
                    'title' => 'Kunjungan Bulan Ini',
                    'value' => \App\Models\Kunjungan::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->count(),
                    'icon' => 'ki-chart-line',
                    'color' => 'success'
                ],
                [
                    'title' => 'Total Event',
                    'value' => \App\Models\Event::count(),
                    'icon' => 'ki-calendar-add',
                    'color' => 'warning'
                ],
            ],
            'charts' => [
                'statistikKunjungan' => $this->getStatistikKunjungan(),
                'trendKunjungan' => $this->getTrendKunjungan(),
            ]
        ];
    }

    private function getSecurityDashboardData()
    {
        return [
            'title' => 'Dashboard Security',
            'widgets' => [
                [
                    'title' => 'Kunjungan Hari Ini',
                    'value' => \App\Models\Kunjungan::whereDate('created_at', today())->count(),
                    'icon' => 'ki-shield-tick',
                    'color' => 'primary'
                ],
                [
                    'title' => 'Menunggu Validasi',
                    'value' => \App\Models\Kunjungan::where('status_validasi', false)->count(),
                    'icon' => 'ki-time',
                    'color' => 'warning'
                ],
                [
                    'title' => 'Sudah Divalidasi',
                    'value' => \App\Models\Kunjungan::where('status_validasi', true)
                        ->whereDate('created_at', today())
                        ->count(),
                    'icon' => 'ki-check-circle',
                    'color' => 'success'
                ],
            ],
        ];
    }

    private function getKunjunganPerBulan()
    {
        $data = \App\Models\Kunjungan::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('bulan')
            ->get();

        return $data;
    }

    private function getEventAktifHariIni()
    {
        return \App\Models\Event::whereDate('tanggal_event', today())
            ->orderBy('waktu_mulai_event', 'asc')
            ->get();
    }

    private function getStatistikKunjungan()
    {
        return [
            'total' => \App\Models\Kunjungan::count(),
            'validated' => \App\Models\Kunjungan::where('status_validasi', true)->count(),
            'pending' => \App\Models\Kunjungan::where('status_validasi', false)->count(),
            'rejected' => 0,
        ];
    }

    private function getTrendKunjungan()
    {
        $data = \App\Models\Kunjungan::selectRaw('CAST(created_at AS DATE) as tanggal, COUNT(*) as total')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw('CAST(created_at AS DATE)'))
            ->orderBy('tanggal')
            ->get();

        return $data;
    }
}
