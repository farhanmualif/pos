<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class RegisterMitraController extends Controller
{
    protected $user, $mitra;

    public function __construct()
    {
        $this->user = new User();
        $this->mitra = new Mitra();
    }
    public function index()
    {
        return view('auth.register.index');
    }

    public function proccess(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = $this->user->create([
                'nama' => $request['nama'],
                'username' => Str::slug($request['nama']),
                'email' => $request['email'],
                'password' => bcrypt('password'),
                'akses' => '2',
            ]);
            $this->mitra->create([
                'namaMitra' => $request->mitra,
                'nomorHp' => $request->no_telp,
                'userId' => $user['id'],
            ]);

            DB::commit();

            Alert::success('success', 'Selamat anda berhasil mendaftar sebagai mitra');
            return redirect('/login')->with('success', 'Selamat anda berhasil mendaftar sebagai mitra');
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('error', 'Error anda gagal mendaftar, terjadi kesalahan!' . $e->getMessage());
            return back()->with('error', 'Error anda gagal mendaftar, Terjadi kesalahan!');
        }
    }
}
