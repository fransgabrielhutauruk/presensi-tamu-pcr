<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tamu;
use App\Models\Kunjungan;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class PresensiDashboardController extends Controller
{
    public function __construct()
    {
        // Middleware akan didefinisikan di routes
    }

    public function index()
    {
        $today = Carbon::today();
        $thisWeek = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
        $thisMonth = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];

        // Statistics
        $stats = [
            'today' => [
                'total_kunjungan' => Kunjungan::today()->count(),
                'validated' => Kunjungan::today()->validated()->count(),
                'active' => Kunjungan::today()->whereNull('waktu_keluar')->count(),
            ],
            'week' => [
                'total_kunjungan' => Kunjungan::thisWeek()->count(),
                'avg_per_day' => round(Kunjungan::thisWeek()->count() / 7, 1),
            ],
            'month' => [
                'total_kunjungan' => Kunjungan::thisMonth()->count(),
                'total_tamu' => Tamu::whereHas('kunjungan', function($q) use ($thisMonth) {
                    $q->whereBetween('waktu_presensi', $thisMonth);
                })->distinct()->count(),
                'avg_rating' => Feedback::whereHas('kunjungan', function($q) use ($thisMonth) {
                    $q->whereBetween('waktu_presensi', $thisMonth);
                })->avg('rating'),
            ]
        ];

        // Chart data - Daily visits this month
        $dailyVisits = Kunjungan::whereBetween('waktu_presensi', $thisMonth)
            ->selectRaw('DATE(waktu_presensi) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Category distribution
        $categoryDistribution = Kunjungan::thisMonth()
            ->selectRaw('kategori_tujuan, COUNT(*) as total')
            ->groupBy('kategori_tujuan')
            ->get()
            ->mapWithKeys(function($item) {
                $kategoriOptions = Kunjungan::getKategoriOptions();
                $label = $kategoriOptions[$item->kategori_tujuan] ?? $item->kategori_tujuan;
                return [$label => $item->total];
            });

        // Recent visits
        $recentVisits = Kunjungan::with(['tamu'])
            ->latest('waktu_presensi')
            ->take(10)
            ->get();

        // Rating distribution
        $ratingDistribution = Feedback::selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->get()
            ->pluck('total', 'rating')
            ->toArray();

        return view('admin.presensi.dashboard', compact(
            'stats', 
            'dailyVisits', 
            'categoryDistribution', 
            'recentVisits',
            'ratingDistribution'
        ));
    }

    public function kunjungan(Request $request)
    {
        if ($request->ajax()) {
            $query = Kunjungan::with(['tamu', 'feedback'])
                ->select('kunjungan.*');

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('waktu_presensi', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            }

            if ($request->filled('kategori')) {
                $query->where('kategori_tujuan', $request->kategori);
            }

            if ($request->filled('status')) {
                if ($request->status === 'validated') {
                    $query->where('status_validasi', true);
                } elseif ($request->status === 'unvalidated') {
                    $query->where('status_validasi', false);
                } elseif ($request->status === 'active') {
                    $query->whereNull('waktu_keluar');
                } elseif ($request->status === 'completed') {
                    $query->whereNotNull('waktu_keluar');
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nama_tamu', function($row) {
                    return $row->tamu->name ?? '-';
                })
                ->addColumn('email_tamu', function($row) {
                    return $row->tamu->email ?? '-';
                })
                ->addColumn('phone_tamu', function($row) {
                    return $row->tamu->phone_number ?? '-';
                })
                ->addColumn('kategori_label', function($row) {
                    return $row->kategori_label;
                })
                ->addColumn('durasi', function($row) {
                    if ($row->waktu_keluar) {
                        $masuk = Carbon::createFromFormat('H:i:s', $row->waktu_masuk);
                        $keluar = Carbon::createFromFormat('H:i:s', $row->waktu_keluar);
                        return $masuk->diffInMinutes($keluar) . ' menit';
                    }
                    return 'Masih aktif';
                })
                ->addColumn('status', function($row) {
                    $badges = [];
                    
                    if ($row->status_validasi) {
                        $badges[] = '<span class="badge bg-success">Tervalidasi</span>';
                    } else {
                        $badges[] = '<span class="badge bg-warning">Belum Validasi</span>';
                    }
                    
                    if (!$row->waktu_keluar) {
                        $badges[] = '<span class="badge bg-info">Aktif</span>';
                    } else {
                        $badges[] = '<span class="badge bg-secondary">Selesai</span>';
                    }
                    
                    return implode(' ', $badges);
                })
                ->addColumn('rating', function($row) {
                    if ($row->feedback) {
                        return $row->feedback->rating_stars . ' (' . $row->feedback->rating . '/5)';
                    }
                    return '-';
                })
                ->addColumn('action', function($row) {
                    $buttons = '';
                    
                    $buttons .= '<button type="button" class="btn btn-sm btn-info me-1" onclick="showDetail(\'' . $row->kunjungan_id . '\')" title="Detail">
                        <i class="ri-eye-line"></i>
                    </button>';
                    
                    if (!$row->status_validasi) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-success me-1" onclick="validateVisit(\'' . $row->kunjungan_id . '\')" title="Validasi">
                            <i class="ri-check-line"></i>
                        </button>';
                    }
                    
                    if (!$row->waktu_keluar) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-warning me-1" onclick="checkoutVisit(\'' . $row->kunjungan_id . '\')" title="Checkout">
                            <i class="ri-logout-circle-line"></i>
                        </button>';
                    }
                    
                    return $buttons;
                })
                ->rawColumns(['status', 'action', 'rating'])
                ->make(true);
        }

        $kategoriOptions = Kunjungan::getKategoriOptions();
        
        return view('admin.presensi.kunjungan', compact('kategoriOptions'));
    }

    public function detailKunjungan($id)
    {
        $kunjungan = Kunjungan::with(['tamu', 'detailKunjungan', 'feedback'])
            ->where('kunjungan_id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $kunjungan
        ]);
    }

    public function validateKunjungan(Request $request, $id)
    {
        try {
            $kunjungan = Kunjungan::where('kunjungan_id', $id)->firstOrFail();
            
            $kunjungan->update([
                'status_validasi' => true,
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kunjungan berhasil divalidasi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memvalidasi kunjungan'
            ]);
        }
    }

    public function checkoutKunjungan(Request $request, $id)
    {
        try {
            $kunjungan = Kunjungan::where('kunjungan_id', $id)->firstOrFail();
            
            if ($kunjungan->waktu_keluar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunjungan sudah di-checkout'
                ]);
            }

            $kunjungan->update([
                'waktu_keluar' => now()->format('H:i:s'),
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checkout berhasil dilakukan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan checkout'
            ]);
        }
    }

    public function reportDaily(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        
        $kunjungan = Kunjungan::with(['tamu', 'feedback'])
            ->whereDate('waktu_presensi', $date)
            ->orderBy('waktu_presensi')
            ->get();

        $stats = [
            'total' => $kunjungan->count(),
            'validated' => $kunjungan->where('status_validasi', true)->count(),
            'completed' => $kunjungan->whereNotNull('waktu_keluar')->count(),
            'avg_rating' => $kunjungan->whereNotNull('feedback.rating')->avg('feedback.rating')
        ];

        return view('admin.presensi.report-daily', compact('kunjungan', 'stats', 'date'));
    }

    public function exportDaily(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        
        $kunjungan = Kunjungan::with(['tamu', 'detailKunjungan', 'feedback'])
            ->whereDate('waktu_presensi', $date)
            ->orderBy('waktu_presensi')
            ->get();

        $filename = 'laporan-kunjungan-' . $date . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($kunjungan) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, [
                'No',
                'Waktu Presensi',
                'Nama Tamu',
                'Email',
                'No. HP',
                'Kategori Tujuan',
                'Waktu Masuk',
                'Waktu Keluar',
                'Durasi (menit)',
                'Transportasi',
                'Status Validasi',
                'Rating',
                'Komentar'
            ]);

            $no = 1;
            foreach ($kunjungan as $item) {
                $durasi = '';
                if ($item->waktu_keluar) {
                    $masuk = Carbon::createFromFormat('H:i:s', $item->waktu_masuk);
                    $keluar = Carbon::createFromFormat('H:i:s', $item->waktu_keluar);
                    $durasi = $masuk->diffInMinutes($keluar);
                }

                fputcsv($file, [
                    $no++,
                    $item->waktu_presensi->format('Y-m-d H:i:s'),
                    $item->tamu->name ?? '',
                    $item->tamu->email ?? '',
                    $item->tamu->phone_number ?? '',
                    $item->kategori_label,
                    $item->waktu_masuk,
                    $item->waktu_keluar ?? 'Masih aktif',
                    $durasi,
                    $item->transportasi,
                    $item->status_validasi ? 'Tervalidasi' : 'Belum validasi',
                    $item->feedback->rating ?? '',
                    $item->feedback->komentar ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}