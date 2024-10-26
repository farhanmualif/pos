<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RealRashid\SweetAlert\Facades\Alert;

class EditProfileController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = new User();
    }
    public function index()
    {
        $content = [
            'breadcrumb' => 'Dashboard',
            'breadcrumb_active' => 'Edit Profile',
            'title' => 'Edit Profile',
        ];
        $data = [
            'user' => User::where('id', Auth::user()->id)->first(),
        ];
        return view('edit_profile.index', $data, $content);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|min:5|max:50',
            'email' => 'required|email|min:5|max:50',
        ]);
        try {
            DB::beginTransaction();
            $user = $this->user::findOrfail($id);
            $user->update([
                'nama' => $request->nama,
                'email' => $request->email,
            ]);
            DB::commit();
            return redirect()->back()->with('success', 'Profile Berhasil Di Update');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Profile gagal di update');
        }
    }

    public function editPassword($id)
    {
        $data["user"] = $this->user->where("id", $id)->first();

        return view('edit_profile.edit_password', $data);
    }

    public function UpdatePassword(Request $request, $id)
    {
        $request->validate([
            "password" => "required|min:8|max:255|same:konfirmasi_password",
            "konfirmasi_password" => "required|min:8|max:255|same:password",
        ]);
        try {
            $user = $this->user::findOrFail($id);
            $user->update([
                'password' => bcrypt($request->password),
            ]);

            return redirect()->back()->with('success', 'Password berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Password gagal diubah. Error: ' . $e->getMessage());
        }
    }

    public function updateGambar($id)
    {
        $data["user"] = $this->user->where("id", $id)->first();

        return view('edit_profile.edit_gambar', $data);
    }



    public function editGambar(Request $request, $id)
    {
        $request->validate([
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $user = $this->user::findOrFail($id);

            if ($request->hasFile('foto')) {
                if ($user->foto && File::exists(public_path($user->foto))) {
                    File::delete(public_path($user->foto));
                }

                $imageExtension = $request->file('foto')->getClientOriginalExtension();
                $newImageName = 'image_user_' . uniqid() . '.' . $imageExtension;
                $imagePath = 'pemilik_mitra_image/' . $newImageName;
                $request->file('foto')->move(public_path('pemilik_mitra_image'), $newImageName);

                $user->foto = $imagePath;
            }

            $user->save();

            return redirect()->back()->with('success', 'Foto berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui foto');
        }
    }
}
