<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditProfileKaryawanController extends Controller
{
    protected $user, $karyawan;

    public function __construct(User $user, Karyawan $karyawan)
    {
        $this->user = $user;
        $this->karyawan = $karyawan;
    }
    public function index()
    {
        $content = [
            'breadcrumb' => 'Dashboard',
            'breadcrumb_active' => 'Edit Profile',
            'title' => 'Edit Profile',
        ];
        $data["user"] = $this->karyawan->where("userId", Auth::user()->id)->first();

        return view('karyawan.pages.profile.index', $data, $content);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|min:5|max:50',
            'email' => 'required|email|min:5|max:50',
            'no_telp' => 'required|string|min:5|max:50',
            'alamat' => 'required|string|min:5|max:50',
        ]);

        try {
            DB::beginTransaction();

            $karyawan = $this->karyawan::findOrFail($id);

            $karyawan->user->update([
                'nama' => $request->nama,
                'email' => $request->email,
            ]);

            $karyawan->update([
                'nomorHpAktif' => $request->no_telp,
                'alamat' => $request->alamat,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Profil berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memperbarui profil');
        }
    }


    public function editPassword($id)
    {
        $data["user"] = $this->karyawan->where("id", $id)->first();
        return view('karyawan.pages.profile.edit_password', $data);
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            "password" => "required|min:8|max:255|same:konfirmasi_password",
            "konfirmasi_password" => "required|min:8|max:255|same:password",
        ]);
        try {
            $karyawan = $this->karyawan::findOrFail($id);
            $karyawan->user->update([
                'password' => bcrypt($request->password),
            ]);

            return redirect()->back()->with('success', 'Password berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Password gagal diubah. Error: ' . $e->getMessage());
        }
    }

    public function editGambar($id)
    {
        $data["user"] = $this->karyawan->where("id", $id)->first();
        return view('karyawan.pages.profile.edit_gambar', $data);
    }

    public function updateGambar(Request $request, $id)
    {
        $request->validate([
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $karyawan = $this->karyawan::findOrFail($id);

            if ($request->hasFile('foto')) {
                if ($karyawan->user && $karyawan->user->foto && File::exists(public_path($karyawan->user->foto))) {
                    File::delete(public_path($karyawan->user->foto));
                }

                $imageExtension = $request->file('foto')->getClientOriginalExtension();
                $newImageName = 'karyawan_' . uniqid() . '.' . $imageExtension;
                $imagePath = 'karyawan_image/' . $newImageName;
                $request->file('foto')->move(public_path('karyawan_image'), $newImageName);

                $karyawan->user->foto = $imagePath;
                $karyawan->user->save();
            }

            return redirect()->back()->with('success', 'Foto berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui foto');
        }
    }
}
