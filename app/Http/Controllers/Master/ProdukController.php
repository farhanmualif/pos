<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProdukController extends Controller
{
    protected $produk, $mitra;

    public function __construct(Produk $produk, Mitra $mitra)
    {
        $this->produk = $produk;
        $this->mitra = $mitra;
    }
    public function index()
    {
        try {

            DB::beginTransaction();

            $content = [
                'titleCreate' => 'Tambah Data Produk',
                'title' => 'Table Produk',
                'breadcrumb' => 'Dashboard',
                'breadcrumb_active' => 'Table Produk',
                'button_create' => 'Tambah Produk',
            ];

            $mitra = $this->mitra->where("userId", Auth::user()->id)->first();

            $data = [
                'produk' => $this->produk::where('mitraId', $mitra->id)->get(),
            ];

            DB::commit();

            return view('admin.pages.master.produk.index', $content, $data);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->to("/admin/master/produk");
        }
    }

    public function store(Request $request)
    {
        $messages = [
            'namaProduk.required' => 'Nama produk harus diisi.',
            'namaProduk.string' => 'Nama produk harus berupa teks.',
            'namaProduk.min' => 'Panjang nama produk minimal :min karakter.',
            'namaProduk.max' => 'Panjang nama produk maksimal :max karakter.',
            'kategori.required' => 'Kategori harus diisi.',
            'kategori.string' => 'Kategori harus berupa teks.',
            'harga.required' => 'Harga harus diisi.',
            'harga.numeric' => 'Harga harus berupa angka.',
            'harga.min' => 'Harga minimal adalah :min.',
            'harga.max' => 'Harga maksimal adalah :max.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.mimes' => 'Format foto harus jpeg, png, jpg.',
            'foto.max' => 'Ukuran foto maksimal 2048 KB.',
        ];
        $request->validate([
            'namaProduk' => 'required|string|min:3|max:100',
            'kategori' => 'required|',
            'harga' => 'required|numeric|min:500|max:100000',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], $messages);

        try {
            DB::beginTransaction();

            $imageExtension = $request->file('foto')->getClientOriginalExtension();
            $newImageName = 'thumbnail_' . (count(File::files(public_path('produk_thumbnail'))) + 1) . '.' . $imageExtension;
            $imagePath = 'produk_thumbnail/' . $newImageName;

            $request->file('foto')->move(public_path('produk_thumbnail'), $newImageName);
            $mitraId = Auth::user()->mitra->id;

            $this->produk->create([
                'namaProduk' => $request->namaProduk,
                'slugProduk' => Str::slug($request->namaProduk),
                'kategori' => $request->kategori,
                'hargaProduk' => $request->harga,
                'fotoProduk' => $imagePath,
                'status' => '0',
                'mitraId' => $mitraId,
            ]);

            DB::commit();
            Alert::success('success', 'Success Produk Berhasil Ditambahkan!');
            return back()->with('success', 'Success Produk Berhasil Ditambahkan!');
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('error', 'Gagal Menambahkan Produk' . $e->getMessage());
            return back()->with('error', 'Gagal Menambahkan Produk' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $data["edit"] = $this->produk->where("id", $id)->first();

        return view('admin.pages.master.produk.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $messages_modal = [
            'namaProduk_modal.required' => 'Nama produk harus diisi.',
            'namaProduk_modal.string' => 'Nama produk harus berupa teks.',
            'namaProduk_modal.min' => 'Panjang nama produk minimal :min karakter.',
            'namaProduk_modal.max' => 'Panjang nama produk maksimal :max karakter.',
            'kategori_modal.required' => 'Kategori harus diisi.',
            'kategori_modal.string' => 'Kategori harus berupa teks.',
            'harga_modal.required' => 'Harga harus diisi.',
            'harga_modal.numeric' => 'Harga harus berupa angka.',
            'harga_modal.min' => 'Harga minimal adalah :min.',
            'harga_modal.max' => 'Harga maksimal adalah :max.',
            'foto_modal.image' => 'Foto harus berupa gambar.',
            'foto_modal.mimes' => 'Format foto harus jpeg, png, jpg.',
            'foto_modal.max' => 'Ukuran foto maksimal 2048 KB.',
        ];

        $request->validate([
            'namaProduk_modal' => 'required|string|min:3|max:100',
            'kategori_modal' => 'required|string',
            'harga_modal' => 'required|numeric|min:500|max:100000',
            'foto_modal' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], $messages_modal);

        try {
            DB::beginTransaction();
            $produk = $this->produk::find($id);
            if ($request->hasFile('foto_modal')) {
                if (File::exists(public_path($produk->fotoProduk))) {
                    File::delete(public_path($produk->fotoProduk));
                }

                $imageExtension = $request->file('foto_modal')->getClientOriginalExtension();
                $newImageName = 'thumbnail_' . (count(File::files(public_path('produk_thumbnail'))) + 1) . '.' . $imageExtension;
                $imagePath = 'produk_thumbnail/' . $newImageName;
                $request->file('foto_modal')->move(public_path('produk_thumbnail'), $newImageName);

                $produk->fotoProduk = $imagePath;
            }

            $produk->namaProduk = $request->namaProduk_modal;
            $produk->slugProduk = Str::slug($request->namaProduk_modal);
            $produk->kategori = $request->kategori_modal;
            $produk->hargaProduk = $request->harga_modal;
            $produk->save();

            DB::commit();
            Alert::success('success', 'Produk Berhasil Diperbarui!');
            return back()->with('success', 'Produk Berhasil Diperbarui!');
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('error', 'Gagal Memperbarui Produk: ' . $e->getMessage());
            return back()->with('error', 'Gagal Memperbarui Produk: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $produk = $this->produk::find($id);
            if ($produk) {
                if (File::exists(public_path($produk->fotoProduk))) {
                    File::delete(public_path($produk->fotoProduk));
                }
                $produk->delete();
                DB::commit();
                Alert::success('Berhasil', 'Produk berhasil dihapus.');
                return back()->with('success', 'Produk berhasil dihapus.');
            } else {
                throw new \Exception('Produk tidak ditemukan.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal menghapus produk: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    public function changeStatus($id)
    {
        try {
            DB::beginTransaction();
            $produk = $this->produk::find($id);
            if ($produk) {
                $produk->status = $produk->status == '0' ? '1' : '0';
                $produk->save();
                DB::commit();
                Alert::success('Berhasil', 'Status produk berhasil diubah.');
                return back()->with('success', 'Status produk berhasil diubah.');
            } else {
                throw new \Exception('Produk tidak ditemukan.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal mengubah status produk: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengubah status produk: ' . $e->getMessage());
        }
    }
}
