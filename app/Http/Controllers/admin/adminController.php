<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Pengaduan;
use App\Models\Tanggapan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class adminController extends Controller
{

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
        $hilmi_pengaduan_baru = Pengaduan::where('status','0')->count();
        $hilmi_pengaduan_proses = Pengaduan::where('status','proses')->count();
        $hilmi_pengaduan_selesai = Pengaduan::where('status','selesai')->count();

        return view('admin.index' ,compact('hilmi_pengaduan','hilmi_total_pengaduan','hilmi_pengaduan_baru','hilmi_pengaduan_proses','hilmi_pengaduan_selesai','hilmi_chartData', 'hilmi_labels'));
    }



// AdminController.php
    public function pengaduan(Request $hilmi_request)
    {
        // Mulai query dasar dengan relasi
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
        
        // Debug untuk melihat jumlah data yang diambil
        // Log::info('Total Data:', ['count' => $hilmi_pengaduan->count()]);
        
        return view('admin.pengaduan.baru', compact('hilmi_pengaduan'));
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
        return view('admin.pengaduan.proses', compact('hilmi_pengaduan'));
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
        return view('admin.pengaduan.selesai', compact('hilmi_pengaduan'));
    }

    public function terima($id_pengaduan)
    {
        $hilmi_pengaduan = Pengaduan::where('id_pengaduan', $id_pengaduan);
        // dd($hilmi_pengaduan);
        $hilmi_updateData = [
            'status' => 'proses'
        ];

        $hilmi_pengaduan->update($hilmi_updateData);

        $hilmi_idAdmin = Session::get('id_petugas');
        $hilmi_namaAdmin = Session::get('nama');

        ActivityLog::create([
            'user_id' => $hilmi_idAdmin, // ID admin yang membuat akun
            'user_type' => 'admin', 
            'action' => 'konfirmasi_pengaduan',
            'description' => "Admin : {$hilmi_namaAdmin} Konfirmasi Pengaduan",
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Pengaduan diterima');

    }

    public function tolak($id_pengaduan)
    {
        $hilmi_id = Pengaduan::where('id_pengaduan', $id_pengaduan);
        // dd($hilmi_id); 

        $hilmi_id->delete();

        $hilmi_idAdmin = Session::get('id_petugas');
        $hilmi_namaAdmin = Session::get('nama');

        
        ActivityLog::create([
            'user_id' => $hilmi_idAdmin,
            'user_type' => 'admin', 
            'action' => 'tolak_pengaduan',
            'description' => "Admin : {$hilmi_namaAdmin} Menolak Pengaduan",
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

        $hilmi_idAdmin = Session::get('id_petugas');
        $hilmi_namaAdmin = Session::get('nama');

        
        ActivityLog::create([
            'user_id' => $hilmi_idAdmin, // ID admin yang membuat akun
            'user_type' => 'admin', 
            'action' => 'konfirmasi_pengaduan',
            'description' => "Admin : {$hilmi_namaAdmin} Menyelesaikan Pengaduan",
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Pengaduan Selesai');

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

        $hilmi_idAdmin = Session::get('id_petugas');
        $hilmi_namaAdmin = Session::get('nama');

        
        ActivityLog::create([
            'user_id' => $hilmi_idAdmin, // ID admin yang membuat akun
            'user_type' => 'admin', 
            'action' => 'create_masyarakat',
            'description' => "Admin : {$hilmi_namaAdmin} Memberikan tanggapan ",
            'ip_address' => request()->ip()
        ]);

        return response()->json(['success' => true]);

        // return redirect()->back();
    }

    public function getTanggapan($id_pengaduan)
    {
        $hilmi_tanggapan = Tanggapan::with(['petugas', 'pengaduan.masyarakat'])
            ->where('id_pengaduan', $id_pengaduan)
            ->orderBy('tgl_tanggapan', 'asc')
            ->get()
            ->map(function ($hilmi_item) {
                return [
                    'id' => $hilmi_item->id,
                    'tanggapan' => $hilmi_item->tanggapan,
                    'tgl_tanggapan' => $hilmi_item->tgl_tanggapan,
                    'nama' => $hilmi_item->petugas ? $hilmi_item->petugas->nama_petugas : $hilmi_item->pengaduan->masyarakat->nama,
                    'pengirim' => $hilmi_item->petugas ? 'petugas' : 'masyarakat'
                ];
            });
    
        return response()->json($hilmi_tanggapan);
    }

    public function activityLog(Request $hilmi_request)
    {
  

        $hilmi_search = $hilmi_request->input('search');
        $hilmi_query = ActivityLog::orderBy('created_at','desc')->latest();

        if ($hilmi_search) {
            $hilmi_query->where(function($q) use ($hilmi_search) {
                $q->where('user_type', 'LIKE', "%{$hilmi_search}%")
                  ->orWhere('action', 'LIKE', "%{$hilmi_search}%")
                  ->orWhere('description', 'LIKE', "%{$hilmi_search}%");
            });
        }

        $hilmi_activitylogs = $hilmi_query->paginate(50);

        return view('admin.activityLog',compact('hilmi_activitylogs'));
    }
}
