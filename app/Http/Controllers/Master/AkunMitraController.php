<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RealRashid\SweetAlert\Facades\Alert;

class AkunMitraController extends Controller
{
    protected $user, $mitra;

    public function __construct()
    {
        $this->user = new User();
        $this->mitra = new Mitra();
    }

    public function index()
    {
        $content = [
            'breadcrumb' => 'Dashboard',
            'breadcrumb_active' => 'Mitra',
            'button_create' => 'Tambah Mitra',
            'title' => 'Table Mitra',
            'titleCreate' => 'Tambah Mitra',
        ];
        $data = [
            'mitra' => $this->mitra::orderByDesc('namaMitra')->get(),
        ];
        return view('super_admin.pages.master.mitra.index', $content, $data);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $imageExtension = $request->file('fotoPemilikMitra')->getClientOriginalExtension();
            $newImageName = 'owner_image_' . (count(File::files(public_path('pemilik_mitra_image'))) + 1) . '.' . $imageExtension;
            $imagePath = 'pemilik_mitra_image/' . $newImageName;

            $request->file('fotoPemilikMitra')->move(public_path('pemilik_mitra_image'), $newImageName);

            $user = $this->user->create([
                'nama' => $request->namaPemilikMitra,
                'username' => $request->namaPemilikMitra,
                'email' => $request->emailPemilikMitra,
                'password' => bcrypt('password'),
                'akses' => 2,
                'foto' => $imagePath,
            ]);

            $imageExtension = $request->file('fotoMitra')->getClientOriginalExtension();
            $namaMitra = $request->namaMitra;
            $newImageName = 'mitra_image_' . $namaMitra . '_' . (count(File::files(public_path('mitra_image'))) + 1) . '.' . $imageExtension;
            $imagePathMitra = 'mitra_image/' . $newImageName;

            $request->file('fotoMitra')->move(public_path('mitra_image'), $newImageName);

            $this->mitra->create([
                'userId' => $user['id'],
                'namaMitra' => $namaMitra,
                'nomorHp' => $request->noTelpMitra,
                'statusMitra' => '0',
                'fotoMitra' => $imagePathMitra,
                'validasiMitraId' => 'valid',
            ]);

            DB::commit();
            Alert::success('success', 'Success Data Mitra Untuk ' . $request->namaPemilikMitra . ' Berhasil Dibuat!');
            return back()->with('success', 'Succes Data Mitra Untuk ' . $request->namaPemilikMitra . ' Berhasil Dibuat!');
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Error Data Mitra Untuk ' . $request->namaPemilikMitra . ' Gagal Dibuat!' . $e->getMessage());
            return back()->with('Error Data Mitra Untuk ' . $request->namaPemilikMitra . ' Gagal Dibuat!');
        }
    }

    public function edit($id)
    {
        $data["edit"] = $this->mitra->where("id", $id)->first();

        return view('super_admin.pages.master.mitra.edit', $data);
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $mitra = $this->mitra->findOrFail($id);

            $user = $mitra->user;
            $user->nama = $request->namaPemilikMitra;
            $user->username = $request->namaPemilikMitra;
            $user->email = $request->emailPemilikMitra;
            $user->save();

            if ($request->hasFile('fotoPemilikMitra')) {
                if (File::exists(public_path($user->foto))) {
                    File::delete(public_path($user->foto));
                }

                $imageExtension = $request->file('fotoPemilikMitra')->getClientOriginalExtension();
                $newImageName = 'owner_image_' . (count(File::files(public_path('pemilik_mitra_image'))) + 1) . '.' . $imageExtension;
                $imagePath = 'pemilik_mitra_image/' . $newImageName;

                $request->file('fotoPemilikMitra')->move(public_path('pemilik_mitra_image'), $newImageName);

                $user->foto = $imagePath;
                $user->save();
            }

            $mitra->namaMitra = $request->namaMitra;
            $mitra->nomorHp = $request->noTelpMitra;

            if ($request->hasFile('fotoMitra')) {
                if (File::exists(public_path($mitra->fotoMitra))) {
                    File::delete(public_path($mitra->fotoMitra));
                }

                $imageExtension = $request->file('fotoMitra')->getClientOriginalExtension();
                $namaMitra = $request->namaMitra;
                $newImageName = 'mitra_image_' . $namaMitra . '_' . (count(File::files(public_path('mitra_image'))) + 1) . '.' . $imageExtension;
                $imagePathMitra = 'mitra_image/' . $newImageName;

                $request->file('fotoMitra')->move(public_path('mitra_image'), $newImageName);

                $mitra->fotoMitra = $imagePathMitra;
            }
            $mitra->save();

            DB::commit();
            Alert::success('success', 'Data Mitra untuk ' . $request->namaPemilikMitra . ' berhasil diperbarui!');
            return back()->with('success', 'Data Mitra untuk ' . $request->namaPemilikMitra . ' berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Error Data Mitra untuk ' . $request->namaPemilikMitra . ' gagal diperbarui!' . $e->getMessage());
            return back()->with('Error Data Mitra untuk ' . $request->namaPemilikMitra . ' gagal diperbarui!');
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $mitra = $this->mitra::find($id);
            if ($mitra) {
                if (File::exists(public_path($mitra->foto))) {
                    File::delete(public_path($mitra->foto));
                }
                if (File::exists(public_path($mitra->user->foto))) {
                    File::delete(public_path($mitra->user->foto));
                }
                $mitra->delete();
                $mitra->user->delete();
                DB::commit();
                Alert::success('success', 'Success Akun Mitra Untuk  Berhasil Dihapus!');
                return back()->with('success', 'Success Akun Mitra Untuk  Berhasil Dihapus!');
            } else {
                throw new \Exception('Data Mitra tidak ditemukan.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Error Akun Mitra Untuk  Gagal Dihapus!' . $e->getMessage());
            return back()->with('error', 'Error Akun Mitra Untuk  Gagal Dihapus!');
        }
    }

    public function changeStatus($id)
    {
        try {
            DB::beginTransaction();
            $mitra = $this->mitra::find($id);
            $userId = auth()->id();
            if ($mitra) {
                $mitra->statusMitra = $mitra->statusMitra == '0' ? '1' : '0';
                $mitra->validasiMitraId = $userId;
                $mitra->save();

                $user = $mitra->user;
                if ($user) {
                    $user->active = $user->active  == '0' ? '1' : '0';
                    $user->save();
                }

                DB::commit();
                Alert::success('Berhasil', 'Status Akun Mitra berhasil diubah.');
                return back()->with('success', 'Status Akun Mitra berhasil diubah.');
            } else {
                throw new \Exception('Akun Mitra tidak ditemukan.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal mengubah status Akun Mitra: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengubah status Akun Mitra: ' . $e->getMessage());
        }
    }
}
