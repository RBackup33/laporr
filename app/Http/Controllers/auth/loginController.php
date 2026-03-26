<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Masyarakat;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class loginController extends Controller
{
    public function showLoginForm()
    {
        if (Session::has('login')) {
            return redirect()->back();
        }

        return view('auth.login');
    }
    public function showRegisterForm()
    {
        if (Session::has('login')) {
            return redirect()->back();
        }
    
        return view('auth.register');
    }

    public function login(Request $hilmi_request)
    {
        $hilmi_request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // Cek di tabel petugas terlebih dahulu
        $hilmi_petugas = Petugas::where('username', $hilmi_request->username)->first();
        
        if ($hilmi_petugas) {
            if (Hash::check($hilmi_request->password, $hilmi_petugas->password)) {

                ActivityLog::create([
                    'user_id' => $hilmi_petugas->id_petugas,
                    'user_type' => $hilmi_petugas->level,
                    'action' => 'login',
                    'description' => "{$hilmi_petugas->nama_petugas} Login sebagai {$hilmi_petugas->level} ",
                    'ip_address' => $hilmi_request->ip()
                ]);

                Session::put('login', true);
                Session::put('id_petugas', $hilmi_petugas->id_petugas);
                Session::put('nama', $hilmi_petugas->nama_petugas);
                Session::put('level', $hilmi_petugas->level); // menggunakan 'level' sesuai field di database
                
                if ($hilmi_petugas->level == 'admin') {
                    return redirect('/admin/dashboard');
                }
                return redirect('/petugas/dashboard');
            }
        }

        // Jika bukan petugas, cek di tabel masyarakat
        $hilmi_masyarakat = Masyarakat::where('username', $hilmi_request->username)->first();

        // $hilmi_status = Masyarakat::first();
        
        if ($hilmi_masyarakat) {
            if (Hash::check($hilmi_request->password, $hilmi_masyarakat->password)) {
                Session::put('login', true);
                Session::put('nik', $hilmi_masyarakat->nik);
                Session::put('nama', $hilmi_masyarakat->nama);

                // dd(Session::get('status', $hilmi_masyarakat->status));

                // dd($hilmi_masyarakat->status);

                if ($hilmi_masyarakat->status == 'verifikasi') {
                    // $hilmi_request->session()->flush();
                    return redirect('/masyarakat/pengaduan');
                } elseif (Session::get('status', $hilmi_masyarakat->status) == '0') {
                    $hilmi_request->session()->flush();
                  return back()->with('unverifvied', 'akun anda belum di verifikasi, silahkan coba kembali');
                }
                

                // if ($hilmi_masyarakat->status == 'verifikasi') {
                //     
                // }
                // Session::put('status', $hilmi_status->status);
                

            }
        }

        // if ($hilmi_masyarakat->status == '0') {
        //     return back()->with('unverifvied', 'akun anda belum di verifikasi');
        // }
        // return back()->with('unverifvied', 'Username atau Password salah!');
        return back()->with('error', 'Username atau Password salah!');
    }
    public function register(Request $hilmi_request)
    {
        $hilmi_request->validate([
            'nik' => 'required|unique:masyarakat,nik|size:16',
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

        Masyarakat::create([
            'nik' => $hilmi_request->nik,
            'nama' => $hilmi_request->nama,
            'username' => $hilmi_request->username,
            'password' => Hash::make($hilmi_request->password),
            'telp' => $hilmi_request->telp,
            'status' => '0'
        ]);

        return redirect('/hilmi_login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    public function logout(Request $hilmi_request)
    {
        // Hapus semua data sesi
        $hilmi_request->session()->flush();
        $hilmi_request->session()->invalidate();
        $hilmi_request->session()->regenerateToken();
        return redirect('/hilmi_login')->with('success', 'Berhasil logout!')->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
