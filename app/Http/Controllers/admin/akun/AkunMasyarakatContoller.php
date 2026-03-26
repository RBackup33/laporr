<?php

namespace App\Http\Controllers\admin\akun;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Masyarakat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class AkunMasyarakatContoller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $hilmi_request)
    {
        $hilmi_search = $hilmi_request->input('search');
        $hilmi_query = Masyarakat::where('status','verifikasi')->latest();

        if ($hilmi_search) {
            $hilmi_query->where(function($q) use ($hilmi_search) {
                $q->where('nama', 'LIKE', "%{$hilmi_search}%")
                  ->orWhere('username', 'LIKE', "%{$hilmi_search}%");
            });
        }

        $hilmi_masyarakat = $hilmi_query->paginate(10);

        // $hilmi_data['hilmi_masyarakat'] = Masyarakat::latest()->get();
        return view('admin.akun.masyarakat', compact('hilmi_masyarakat'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.akun.add-masyarakat');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $hilmi_request)
    {
        $hilmi_request->validate([
            'nik' => 'required|string|unique:masyarakat,nik|size:16',
            'nama' => 'required|max:35',
            'username' => [
                'required',
                'max:25',
                Rule::unique('masyarakat', 'username'),
                Rule::unique('petugas', 'username')
            ],
            'password' => 'required|min:6|max:32',
            'telp' => 'required|max:13'
        ],
        [
            'nik.unique' => 'nik sudah di gunakan'
        ]

        );

        

        $hilmi_masyarakat = Masyarakat::create([
            'nik' => $hilmi_request->nik,
            'nama' => $hilmi_request->nama,
            'username' => $hilmi_request->username,
            'password' => Hash::make($hilmi_request->password),
            'telp' => $hilmi_request->telp,
            'status' => $hilmi_request->status
        ]);

        $hilmi_idAdmin = Session::get('id_petugas');
        $hilmi_namaAdmin = Session::get('nama');

        
        ActivityLog::create([
            'user_id' => $hilmi_idAdmin, // ID admin yang membuat akun
            'user_type' => 'admin', 
            'action' => 'create_masyarakat',
            'description' => "Admin : {$hilmi_namaAdmin} Membuat akun Masyarakat dengan nama : {$hilmi_masyarakat->nama} (NIK: {$hilmi_masyarakat->nik})",
            'ip_address' => request()->ip()
        ]);

        return to_route('admin.akun.masyarakat')->with('success', 'Akun Masyarakat Berhasil Di Buat!.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($hilmi_nik)
    {
        $hilmi_masyarakat = Masyarakat::where('nik', $hilmi_nik)->first();
        // Melihat apakah data ditemukan
    
        $hilmi_data['hilmi_masyarakat'] = $hilmi_masyarakat;
        return view('admin.akun.edit-masyarakat', $hilmi_data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $hilmi_request, $hilmi_nik)
    {
        $hilmi_masyarakat = Masyarakat::where('nik', $hilmi_nik)->firstOrFail();

        // Validasi data yang diterima
        $hilmi_validatedData = $hilmi_request->validate([
            'nama' => 'required|string|max:255',
            'nik' => [
                'required',
                'string',
                'max:16',
                Rule::unique('masyarakat', 'nik')->ignore($hilmi_masyarakat->nik, 'nik'), // Pengecualian pada masyarakat
            ],
            'username' => [
                'required',
                'string',
                'max:25',
                Rule::unique('masyarakat', 'username')->ignore($hilmi_masyarakat->username, 'username'), // Pengecualian pada masyarakat
                Rule::unique('petugas', 'username')->ignore($hilmi_masyarakat->username, 'username') // Pengecualian pada petugas, dengan kolom 'username'
            ],
            'telp' => 'required|string|max:20',
        ]);

        // Siapkan data untuk update
        $hilmi_updateData = [
            'nama' => $hilmi_validatedData['nama'],
            'username' => $hilmi_validatedData['username'],
            'telp' => $hilmi_validatedData['telp'],
        ];

        // Update password jika disediakan
        if (!empty($hilmi_request->password)) {
            $hilmi_updateData['password'] = Hash::make($hilmi_request->password);
        }

        // Lakukan update pada data masyarakat
        $hilmi_masyarakat->update($hilmi_updateData);

        $hilmi_idAdmin = Session::get('id_petugas');
        $hilmi_namaAdmin = Session::get('nama');

        
        ActivityLog::create([
            'user_id' => $hilmi_idAdmin, // ID admin yang membuat akun
            'user_type' => 'admin', 
            'action' => 'update_masyarakat',
            'description' => "Admin : {$hilmi_namaAdmin} Update akun Masyarakat dengan nama : {$hilmi_masyarakat->nama} (NIK: {$hilmi_masyarakat->nik})",
            'ip_address' => request()->ip()
        ]);

        // Redirect setelah update sukses
        return redirect()->route('admin.akun.masyarakat')->with('success', 'Data masyarakat berhasil diperbarui');
    }

    public function verifikasiAkun(Request $hilmi_request)
    {
        $hilmi_search = $hilmi_request->input('search');
        $hilmi_query = Masyarakat::where('status', '0')->latest();

        // $hilmi_query = Masyarakat::where('status','verifikasi')->latest();

        if ($hilmi_search) {
            $hilmi_query->where(function($q) use ($hilmi_search) {
                $q->where('nama', 'LIKE', "%{$hilmi_search}%")
                  ->orWhere('username', 'LIKE', "%{$hilmi_search}%");
            });
        }

        $hilmi_masyarakat = $hilmi_query->paginate(10);


        return view('admin.akun.belum-verifikasi', compact('hilmi_masyarakat') );
    

        // return back()->with('success', 'akun di verifikasi');


    }
    public function verifikasi($nik)
    {
        $hilmi_masyarakat = Masyarakat::where('nik', $nik);
        // dd($hilmi_pengaduan);
        $hilmi_update_data = [
            'status' => 'verifikasi'
        ];

        
        $hilmi_masyarakat->update($hilmi_update_data);

        return back()->with('success', 'akun di verifikasi');


    }

    public function hapus($hilmi_nik)
    {
        // Cari data masyarakat berdasarkan nik
        $hilmi_masyarakat = Masyarakat::where('nik', $hilmi_nik)->first();

        // Cek jika data masyarakat ditemukan
        if ($hilmi_masyarakat) {
            // Hapus data masyarakat
            $hilmi_masyarakat->delete();

            $hilmi_idAdmin = Session::get('id_petugas');
            $hilmi_namaAdmin = Session::get('nama');
    
            
            ActivityLog::create([
                'user_id' => $hilmi_idAdmin, // ID admin yang membuat akun
                'user_type' => 'admin', 
                'action' => 'delete_masyarakat',
                'description' => "Admin : {$hilmi_namaAdmin} Menghapus akun Masyarakat dengan nama : {$hilmi_masyarakat->nama} (NIK: {$hilmi_masyarakat->nik})",
                'ip_address' => request()->ip()
            ]);

            // Redirect dengan pesan sukses
            return redirect()->route('akun.masyarakat.verifikasi')->with('success', 'Data masyarakat berhasil dihapus');
        } else {
            // Jika data tidak ditemukan, redirect dengan pesan error
            return redirect()->route('akun.masyarakat.verifikasi')->with('error', 'Data masyarakat tidak ditemukan');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($hilmi_nik)
    {
        // Cari data masyarakat berdasarkan nik
        $hilmi_masyarakat = Masyarakat::where('nik', $hilmi_nik)->first();

        // Cek jika data masyarakat ditemukan
        if ($hilmi_masyarakat) {
            // Hapus data masyarakat
            $hilmi_masyarakat->delete();

            $hilmi_idAdmin = Session::get('id_petugas');
            $hilmi_namaAdmin = Session::get('nama');
    
            
            ActivityLog::create([
                'user_id' => $hilmi_idAdmin, // ID admin yang membuat akun
                'user_type' => 'admin', 
                'action' => 'delete_masyarakat',
                'description' => "Admin : {$hilmi_namaAdmin} Menghapus akun Masyarakat dengan nama : {$hilmi_masyarakat->nama} (NIK: {$hilmi_masyarakat->nik})",
                'ip_address' => request()->ip()
            ]);

            // Redirect dengan pesan sukses
            return redirect()->route('admin.akun.masyarakat')->with('success', 'Data masyarakat berhasil dihapus');
        } else {
            // Jika data tidak ditemukan, redirect dengan pesan error
            return redirect()->route('admin.akun.masyarakat')->with('error', 'Data masyarakat tidak ditemukan');
        }
    }
}
