<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthApiController extends Controller
{

    protected $user;
    protected $karyawan;

    public function __construct(User $user, Karyawan $karyawan) // Constructor harus public, bukan private
    {
        $this->user = $user;
        $this->karyawan = $karyawan;
    }

    public function signIn(Request $request): JsonResponse
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $this->user->whereEmail($request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email tidak ditemukan'
                ], 404);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password tidak valid'
                ], 401);
            }

            // Cek status aktivasi untuk user dengan akses level 2 (mitra)
            if ($user->akses == 2) {
                if ($user->active == 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Akun Anda belum diaktifkan, silahkan hubungi Admin'
                    ], 401);
                }

                $mitra = $user->mitra;
                if ($mitra && $mitra->statusMitra == 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Akun Mitra Anda belum diaktifkan, silahkan hubungi Admin'
                    ], 401);
                }
            }

            // Cek status aktivasi untuk user dengan akses level 3 (karyawan)
            if ($user->akses == 3) {
                if ($user->active == 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Akun Anda belum diaktifkan, silahkan hubungi Mitra Anda'
                    ], 401);
                }

                $karyawan = $user->karyawan;
                if ($karyawan) {
                    $mitra = $karyawan->mitra;
                    if ($mitra && $mitra->statusMitra == 0) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Akun Mitra Anda belum diaktifkan, silahkan hubungi Admin'
                        ], 401);
                    }
                }
            }

            // Proses login
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $token = $user->createToken('auth-token')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'message' => 'Login berhasil',
                    'data' => [
                        'nama' => $user->nama,
                        'username' => $user->username,
                        'email' => $user->email,
                        'token' => $token,
                    ]
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'Gagal melakukan autentikasi'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            Auth::guard('sanctum')->user()->tokens()->delete();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil Logout'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada server. ' . $th->getMessage(),
            ], 500);
        }
    }

    public function profile()
    {
        try {
            $profilKaryawan = $this->karyawan
                ->where('userId', Auth::user()->id)
                ->with('user')  // Eager load relasi user
                ->first();      // Ambil single record

            if (!$profilKaryawan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Profil karyawan tidak ditemukan',
                ], 404);
            }

            unset($profilKaryawan["user"]["password"]);

            return response()->json([
                'status' => true,
                'message' => 'Data profil berhasil diambil',
                'data' => $profilKaryawan
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => config('app.debug') ? $th->getMessage() : 'Server Error'
            ], 500);
        }
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

            return response()->json([
                'status' => true,
                'message' => 'Update Profile berhasil',
                'data' => $karyawan
            ], 200);
        } catch (\Exception $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $th->getMessage()
            ], 500);
        }
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

            return response()->json([
                'status' => true,
                'message' => 'Update Gambar berhasil',
                'data' => $karyawan
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'status' => true,
                'message' => 'Gagal Update Gambar',
                'data' => $th
            ], status: 500);
        }
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

            return response()->json([
                'status' => true,
                'message' => 'Update Password berhasil',
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function checkAuth()
    {
        try {
            $authenticated =  Auth::guard('sanctum')->check();
            if ($authenticated) {
                return response()->json([
                    'status' => true,
                    'message' => 'Authenticated',
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Unauthenticated',
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
