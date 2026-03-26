<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function laporan()
    {   
        $hilmi_laporan = Pengaduan::all();
        return view('admin.laporan', compact('hilmi_laporan'));
    }

    public function filterLaporan(Request $hilmi_request)
    {
        // Buat query dasar
        $hilmi_query = Pengaduan::with('masyarakat');

        // Filter berdasarkan status
        if ($hilmi_request->has('status') && $hilmi_request->status !== '') {
            $hilmi_query->where('status', $hilmi_request->status);
        }

        // Filter berdasarkan rentang tanggal
        if ($hilmi_request->has('start_date') && $hilmi_request->has('end_date') && 
            $hilmi_request->start_date !== '' && $hilmi_request->end_date !== '') {
            $hilmi_query->whereBetween('tgl_pengaduan', [
                $hilmi_request->start_date, 
                $hilmi_request->end_date
            ]);
        }

        // Dapatkan pengaduan yang difilter
        $hilmi_laporan = $hilmi_query->get();

        // Kembalikan sebagai JSON untuk request AJAX
        return response()->json($hilmi_laporan);
    }

    public function printSingleLaporan($id)
    {
        $hilmi_laporan = Pengaduan::with('masyarakat')->where('id_pengaduan', $id)->get();
        $hilmi_pdf = PDF::loadView('admin.laporan_print', compact('hilmi_laporan'));
        return $hilmi_pdf->download('laporan_pengaduan_' . $id . '.pdf');
    }

    public function printLaporan(Request $hilmi_request)
    {
        // Buat query dasar
        $hilmi_query = Pengaduan::with('masyarakat');
    
        // Filter berdasarkan status
        if ($hilmi_request->filled('status')) {
            $hilmi_query->where('status', $hilmi_request->status);
        }
    
        // Filter berdasarkan rentang tanggal
        if ($hilmi_request->filled('start_date') && $hilmi_request->filled('end_date')) {
            $hilmi_query->whereBetween('tgl_pengaduan', [
                $hilmi_request->start_date, 
                $hilmi_request->end_date
            ]);
        }
    
        // Dapatkan pengaduan yang difilter
        $hilmi_laporan = $hilmi_query->get();
    
        // Generate PDF
        $hilmi_pdf = Pdf::loadView('admin.laporan_print', compact('hilmi_laporan'));
        
        // Nama file dinamis berdasarkan filter
        $hilmi_filename = 'laporan_pengaduan_';
        $hilmi_filename .= $hilmi_request->status ? $hilmi_request->status . '_' : 'semua_';
        $hilmi_filename .= $hilmi_request->start_date && $hilmi_request->end_date 
            ? $hilmi_request->start_date . '_to_' . $hilmi_request->end_date 
            : 'semua_tanggal';
        $hilmi_filename .= '.pdf';
        
        return $hilmi_pdf->download($hilmi_filename);
    }
}
