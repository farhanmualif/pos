<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class PengaturanMitraController extends Controller
{
    protected $mitra;

    public function __construct(Mitra $mitra)
    {
        $this->mitra = $mitra;
    }
    public function index()
    {
        $content  = [
            'breadcrumb' => 'Dashboard',
            'breadcrumb_active' => 'Pengaturan Mitra',
            'title' => 'Pengaturan Mitra',
        ];
        $data['mitra'] = $this->mitra::where('namaMitra', Auth::user()->mitra->namaMitra)->first();
        return view('admin.pages.pengaturan_mitra.index', $content, $data);
    }

    public function editGambar($id)
    {
        $data["mitra"] = $this->mitra->where("id", $id)->first();
        return view('admin.pages.pengaturan_mitra.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|min:5|max:50',
            'no_telp' => 'required|string|min:5|max:15',
        ]);
        try {
            DB::beginTransaction();
            $mitra = $this->mitra::findOrfail($id);
            $mitra->update([
                'namaMitra' => $request->nama,
                'nomorHp' => $request->no_telp,
            ]);
            DB::commit();
            return redirect()->back()->with('success', 'Profile Mitra Berhasil Di Update');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Profile Mitra gagal di update' . $e->getMessage());
        }
    }

    public function updateGambar(Request $request, $id)
    {
        $request->validate([
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $mitra = $this->mitra::findOrFail($id);

            if ($request->hasFile('foto')) {
                if ($mitra->fotoMitra && File::exists(public_path($mitra->fotoMitra))) {
                    File::delete(public_path($mitra->fotoMitra));
                }

                $imageExtension = $request->file('foto')->getClientOriginalExtension();
                $newImageName = 'mitra_image_' . uniqid() . '.' . $imageExtension;
                $imagePath = 'mitra_image/' . $newImageName;
                $request->file('foto')->move(public_path('mitra_image'), $newImageName);

                $mitra->fotoMitra = $imagePath;
            }

            $mitra->save();

            return redirect()->back()->with('success', 'Foto Mitra berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui Foto Mitra');
        }
    }
}
