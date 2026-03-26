<?php

namespace App\Http\Controllers\petugas;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Pengaduan;
use App\Models\Tanggapan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class petugasController extends Controller
{
    // Menu dashboard
    public function index()
    {
        $hilmi_endDate = Carbon::today();
        $hilmi_startDate = $hilmi_endDate->copy()->subDays(6);

        $hilmi_pengaduanData = Pengaduan::selectRaw('DATE(tgl_pengaduan) as date, COUNT(*) as count')
            ->whereBetween('tgl_pengaduan', [$hilmi_startDate, $hilmi_endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $hilmi_chartData = [];
        $hilmi_labels = [];

        for ($hilmi_date = $hilmi_startDate; $hilmi_date <= $hilmi_endDate; $hilmi_date->addDay()) {
            $dateString = $hilmi_date->toDateString();
            $hilmi_labels[] = $hilmi_date->format('D'); // Day name (e.g., Mon, Tue)
            $hilmi_chartData[] = $hilmi_pengaduanData->get($dateString)->count ?? 0;
        }

        $hilmi_pengaduan = Pengaduan::latest()->take(5)->get();
        $hilmi_total_pengaduan = Pengaduan::count();
        $hilmi_pengaduan_baru = Pengaduan::where('status', '0')->count();
        $hilmi_pengaduan_proses = Pengaduan::where('status', 'proses')->count();
        $hilmi_pengaduan_selesai = Pengaduan::where('status', 'selesai')->count();
        return view('petugas.index', compact('hilmi_pengaduan', 'hilmi_total_pengaduan', 'hilmi_pengaduan_baru', 'hilmi_pengaduan_proses', 'hilmi_pengaduan_selesai', 'hilmi_chartData', 'hilmi_labels'));
    }

    public function pengaduan(Request $hilmi_request)
    {
        $hilmi_query = Pengaduan::with(['masyarakat', 'tanggapan'])->where('status', '0');
        
        if ($hilmi_request->filled('tanggal')) {
            $hilmi_query->whereDate('tgl_pengaduan', $hilmi_request->tanggal);
        }
        
        if ($hilmi_request->filled('urutan')) {
            if ($hilmi_request->urutan === 'terlama') {
                $hilmi_query->orderBy('created_at', 'asc');
            } else {
                $hilmi_query->orderBy('created_at', 'desc');
            }
        } else {
            $hilmi_query->orderBy('created_at', 'desc'); // Urutan default
        }
        
        $hilmi_pengaduan = $hilmi_query->paginate(6);
        return view('petugas.pengaduan', compact('hilmi_pengaduan'));
    }

    public function pengaduanProses(Request $hilmi_request)
    {
        $hilmi_query = Pengaduan::with(['masyarakat', 'tanggapan'])->where('status', 'proses');
        
        if ($hilmi_request->filled('tanggal')) {
            $hilmi_query->whereDate('tgl_pengaduan', $hilmi_request->tanggal);
        }
        
        if ($hilmi_request->filled('urutan')) {
            if ($hilmi_request->urutan === 'terlama') {
                $hilmi_query->orderBy('created_at', 'asc');
            } else {
                $hilmi_query->orderBy('created_at', 'desc');
            }
        } else {
            $hilmi_query->orderBy('created_at', 'desc'); // Urutan default
        }
        
        $hilmi_pengaduan = $hilmi_query->paginate(6);
        return view('petugas.pengaduan-proses', compact('hilmi_pengaduan'));
    }

    public function pengaduanSelesai(Request $hilmi_request)
    {
        $hilmi_query = Pengaduan::with(['masyarakat', 'tanggapan'])->where('status', 'selesai');
        
        if ($hilmi_request->filled('tanggal')) {
            $hilmi_query->whereDate('tgl_pengaduan', $hilmi_request->tanggal);
        }
        
        if ($hilmi_request->filled('urutan')) {
            if ($hilmi_request->urutan === 'terlama') {
                $hilmi_query->orderBy('created_at', 'asc');
            } else {
                $hilmi_query->orderBy('created_at', 'desc');
            }
        } else {
            $hilmi_query->orderBy('created_at', 'desc'); // Urutan default
        }
        
        $hilmi_pengaduan = $hilmi_query->paginate(6);
        return view('petugas.pengaduan-selesai', compact('hilmi_pengaduan'));
    }

    public function tolak($id_pengaduan)
    {
        $hilmi_id = Pengaduan::where('id_pengaduan', $id_pengaduan);
        // dd($hilmi_id); 

        $hilmi_id->delete();

        $hilmi_idPetugas = Session::get('id_petugas');
        $hilmi_namaPetugas = Session::get('nama');


        ActivityLog::create([
            'user_id' => $hilmi_idPetugas,
            'user_type' => 'petugas',
            'action' => 'tolak pengaduan',
            'description' => "petugas : {$hilmi_namaPetugas} Menolak Pengaduan",
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Pengaduan ditolak');
    }

    public function selesai($id_pengaduan)
    {
        $hilmi_pengaduan = Pengaduan::where('id_pengaduan', $id_pengaduan);
        // dd($hilmi_pengaduan);
        $hilmi_updateData = [
            'status' => 'selesai'
        ];

        $hilmi_pengaduan->update($hilmi_updateData);

        $hilmi_idPetugas = Session::get('id_petugas');
        $hilmi_namaPetugas = Session::get('nama');


        ActivityLog::create([
            'user_id' => $hilmi_idPetugas, // ID admin yang membuat akun
            'user_type' => 'petugas',
            'action' => 'selesai pengaduan',
            'description' => "Petugas : {$hilmi_namaPetugas} Menyelesaikan Pengaduan",
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Pengaduan selesai');
    }

    public function tanggapan(Request $hilmi_request)
    {

        $hilmi_request->validate([
            'id_pengaduan' => 'required|integer',
            'tanggapan' => 'required|string',
        ]);

        Tanggapan::create([
            'id_pengaduan' => $hilmi_request->id_pengaduan,
            'tanggapan' => $hilmi_request->tanggapan,
            'tgl_tanggapan' => now(),
            'id_petugas' => session('id_petugas'), // Menggunakan session untuk mendapatkan id_petugas
        ]);

        return response()->json(['success' => true]);
    }

    public function getTanggapan($id_pengaduan)
    {
        $hilmi_tanggapan = Tanggapan::with(['petugas', 'pengaduan.masyarakat'])
            ->where('id_pengaduan', $id_pengaduan)
            ->orderBy('tgl_tanggapan', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tanggapan' => $item->tanggapan,
                    'tgl_tanggapan' => $item->tgl_tanggapan,
                    'nama' => $item->petugas ? $item->petugas->nama_petugas : $item->pengaduan->masyarakat->nama,
                    'pengirim' => $item->petugas ? 'petugas' : 'masyarakat'
                ];
            });

        return response()->json($hilmi_tanggapan);
    }



    public function getPengaduanDetail($id_pengaduan)
    {
        $hilmi_pengaduan = Pengaduan::with('masyarakat') // Pastikan relasi ke masyarakat sudah terdefinisi
            ->where('id_pengaduan', $id_pengaduan)
            ->first();

        if (!$hilmi_pengaduan) {
            return response()->json(['message' => 'Pengaduan tidak ditemukan'], 404);
        }

        return response()->json($hilmi_pengaduan);
    }

    public function terima($id_pengaduan)
    {
        $hilmi_pengaduan = Pengaduan::where('id_pengaduan', $id_pengaduan);
        // dd($hilmi_pengaduan);
        $hilmi_updateData = [
            'status' => 'proses'
        ];

        $hilmi_pengaduan->update($hilmi_updateData);

        $hilmi_idPetugas = Session::get('id_petugas');
        $hilmi_namaPetugas = Session::get('nama');


        ActivityLog::create([
            'user_id' => $hilmi_idPetugas, // ID admin yang membuat akun
            'user_type' => 'petugas',
            'action' => 'konfirmasi_pengaduan',
            'description' => "Petugas : {$hilmi_namaPetugas} Konfirmasi Pengaduan",
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Pengaduan diterima');
    }
}
