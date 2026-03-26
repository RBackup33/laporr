<?php

namespace App\Http\Controllers\admin\akun;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class AkunPetugasContoller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $hilmi_request)
    {
        $hilmi_search = $hilmi_request->input('search');
        $hilmi_query = Petugas::where('level', 'petugas');

        if ($hilmi_search) {
            $hilmi_query->where(function($q) use ($hilmi_search) {
                $q->where('nama_petugas', 'LIKE', "%{$hilmi_search}%")
                  ->orWhere('username', 'LIKE', "%{$hilmi_search}%");
            });
        }

        $hilmi_petugas = $hilmi_query->paginate(10);
        return view('admin.akun.petugas',compact('hilmi_petugas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.akun.add-petugas');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $hilmi_request)
    {
        $hilmi_request->validate([
            'nama_petugas' => 'required|string|max:255',
            'username' => [
                'required',
                'max:25',
                Rule::unique('masyarakat', 'username'),
                Rule::unique('petugas', 'username')
            ],
            'password' => 'required|string|min:6',
            'telp' => 'required|string|max:20',
            'level' => 'required|in:admin,petugas',
        ]);

        $hilmi_petugas = Petugas::create([
            'nama_petugas' => $hilmi_request->nama_petugas,
            'username' => $hilmi_request->username,
            'password' => Hash::make($hilmi_request->password),
            'telp' => $hilmi_request->telp,
            'level' => $hilmi_request->level,
        ]);

        // Ambil ID admin dari session
        $hilmi_idAdmin = Session::get('id_petugas');
        $hilmi_namaAdmin = Session::get('nama');

        // Logging aktivitas pembuatan akun petugas
        ActivityLog::create([
            'user_id' => $hilmi_idAdmin, // ID admin yang membuat akun
            'user_type' => 'admin', 
            'action' => 'create_petugas',
            'description' => "Admin : {$hilmi_namaAdmin} Membuat akun petugas dengan nama : {$hilmi_petugas->nama_petugas} (Level: {$hilmi_petugas->level})",
            'ip_address' => request()->ip()
        ]);
        
    
        return redirect()->route('admin.akun.petugas')->with('success', 'Akun berhasil dibuat');
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
    public function edit($hilmi_id_petugas)
    {
        $hilmi_data['hilmi_petugas'] = Petugas::where('id_petugas', $hilmi_id_petugas)->first();
        return view('admin.akun.edit-petugas',$hilmi_data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $hilmi_request, $hilmi_id_petugas)
    {
        $hilmi_petugas = Petugas::findOrFail($hilmi_id_petugas); // Ambil data petugas berdasarkan ID

        $hilmi_validatedData = $hilmi_request->validate([
            'nama_petugas' => 'required|string|max:255',
            'username' => [
                'required',
                'max:25',
                Rule::unique('masyarakat', 'username'),
                Rule::unique('petugas', 'username')->ignore($hilmi_petugas->id_petugas, 'id_petugas') // Tambahkan pengecualian untuk username saat ini
            ],
            'telp' => 'required|string|max:20',
        ]);
    
        $hilmi_updateData = [
            'nama_petugas' => $hilmi_validatedData['nama_petugas'],
            'username' => $hilmi_validatedData['username'],
            'telp' => $hilmi_validatedData['telp'],
        ];
    
        // Update password jika disediakan
        if (!empty($hilmi_request->password)) {
            $hilmi_updateData['password'] = Hash::make($hilmi_request->password);
        }
    
        $hilmi_petugas->update($hilmi_updateData);

        $hilmi_idAdmin = Session::get('id_petugas');
        $hilmi_namaAdmin = Session::get('nama');

        
        ActivityLog::create([
            'user_id' => $hilmi_idAdmin, // ID admin yang membuat akun
            'user_type' => 'admin', 
            'action' => 'update_petugas',
            'description' => "Admin : {$hilmi_namaAdmin} Mengupdate akun Petugas dengan nama : {$hilmi_petugas->nama_petugas} (Level: {$hilmi_petugas->level})",
            'ip_address' => request()->ip()
        ]);
    
        return redirect()->route('admin.akun.petugas')->with('success', 'Data Admin berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($hilmi_id_petugas)
    {
        $hilmi_petugas = Petugas::findOrFail($hilmi_id_petugas);
        $hilmi_petugas->delete();

        $hilmi_idAdmin = Session::get('id_petugas');
        $hilmi_namaAdmin = Session::get('nama');

        ActivityLog::create([
            'user_id' => $hilmi_idAdmin,
            'user_type' => 'admin', 
            'action' => 'delete_petugas',
            'description' => "Admin : {$hilmi_namaAdmin} Menghapus akun Petugas dengan nama : {$hilmi_petugas->nama_petugas} (Level: {$hilmi_petugas->level})",
            'ip_address' => request()->ip()
        ]);

        // Redirect dengan pesan sukses
        return redirect()->route('admin.akun.petugas')->with('success', 'Data petugas berhasil dihapus');
    }
}
