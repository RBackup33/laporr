<?php

namespace App\Http\Controllers\masyarakat;

use App\Http\Controllers\Controller;
use App\Models\Tanggapan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MasyarakatController extends Controller
{
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

    public function Tanggapan(Request $hilmi_request)
    {

        $hilmi_request->validate([
            'id_pengaduan' => 'required|integer',
            'tanggapan' => 'required|string',
        ]);

        Tanggapan::create([
            'id_pengaduan' => $hilmi_request->id_pengaduan,
            'tanggapan' => $hilmi_request->tanggapan,
            'tgl_tanggapan' => now(),
            'pengirim' => 'masyarakat',
            'nik' => session('nik'), // Menggunakan session untuk mendapatkan id_petugas
        ]);

        return response()->json(['success' => true]);
        
    }
}
