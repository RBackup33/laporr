<?php

namespace App\Http\Controllers\masyarakat;

use App\Http\Controllers\Controller;
use App\Models\Masyarakat;
use App\Models\Pengaduan;
use App\Models\Tanggapan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class pengaduanController extends Controller
{

    public function index()
    {
        return view('masyarakat.index');
    }

    
    public function pengaduanSaya(Request $hilmi_request)
    {
        // Ambil NIK atau ID masyarakat yang login dari session
        $hilmi_nik = Session::get('nik');
        
        // Ambil filter status dari request
        $hilmi_statusFilter = $hilmi_request->input('status', 'all');
        
        // Query untuk mengambil data pengaduan sesuai NIK dan filter status
        $hilmi_query = Pengaduan::with(['masyarakat', 'tanggapan'])->where('nik', $hilmi_nik);
        
        // Terapkan filter jika status tidak "all"
        if ($hilmi_statusFilter !== 'all') {
            $hilmi_query->where('status', $hilmi_statusFilter);
        }
        
        $hilmi_pengaduan = $hilmi_query->paginate(3);
        
        return view('masyarakat.pengaduan', compact('hilmi_pengaduan'));
    }


    public function store(Request $hilmi_request)
    {
        // Validasi data yang dikirimkan
        $hilmi_validatedData = $hilmi_request->validate([
            'tanggal_pengaduan' => 'required|date',
            'isi_laporan' => 'required|string',
            'photo' => 'nullable|image|max:2048', // Maks 2MB per foto
        ]);

        // dd($hilmi_validatedData);

        // Ambil `nik` dari sesi atau Auth (contoh jika Anda menggunakan session)
        $hilmi_nik = session('nik'); // atau bisa gunakan Auth jika sudah disesuaikan dengan model login
        // dd($nik);
        // Tangani upload foto dan simpan path-nya
        $hilmi_fotoPath = null;
        if ($hilmi_request->hasFile('photo')) {
            $hilmi_fotoPath = $hilmi_request->file('photo')->store('Pengaduan_foto', 'public');
        }

        // Buat pengaduan baru
        Pengaduan::create([
            'tgl_pengaduan' => $hilmi_validatedData['tanggal_pengaduan'],
            'isi_laporan' => $hilmi_validatedData['isi_laporan'],
            'nik' => $hilmi_nik, // masukkan nik masyarakat yang sedang login
            'foto' => $hilmi_fotoPath, // path ke foto yang diunggah
            'status' => '0', // Status default
        ]);

        // Redirect pengguna setelah pengaduan berhasil disimpan
        return redirect()->route('masyarakat.index')->with('success', 'Pengaduan berhasil disampaikan.');
    }

    /**
     * Display the specified resource.
     */
    public function profile()
    {
        $hilmi_nik = Session::get('nik');

        $hilmi_profile = Masyarakat::where('nik', $hilmi_nik)->first();
        $hilmi_pengaduan = Pengaduan::where('nik', $hilmi_nik)->count();
        $hilmi_pengaduan_selesai = Pengaduan::where('nik', $hilmi_nik)->where('status','selesai')->count();
        $hilmi_pengaduan_proses = Pengaduan::where('nik', $hilmi_nik)->where('status','proses')->count();

        return view('masyarakat.profile', compact('hilmi_profile','hilmi_pengaduan','hilmi_pengaduan_selesai','hilmi_pengaduan_proses'));        
    }




    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
// Controller

    public function destroy($id_pengaduan)
    {
        // Temukan pengaduan berdasarkan id_pengaduan
        $hilmi_pengaduan = Pengaduan::findOrFail($id_pengaduan);

        // Cek apakah pengaduan sudah memiliki tanggapan
        $hilmi_tanggapan = Tanggapan::where('id_pengaduan', $id_pengaduan)->first();

        // Jika ada tanggapan, jangan izinkan penghapusan
        if ($hilmi_tanggapan) {
            return redirect()->back()->with('error', 'Pengaduan ini sudah memiliki tanggapan dan tidak bisa dihapus.');
        }

        // Jika tidak ada tanggapan, hapus pengaduan
        $hilmi_pengaduan->delete();

        // Redirect atau memberi respons sesuai kebutuhan
        return redirect()->back()->with('success', 'Pengaduan berhasil dihapus.');
    }


}
