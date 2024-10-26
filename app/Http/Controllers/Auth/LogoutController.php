<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class LogoutController extends Controller
{
    public function __invoke(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Alert::success("Logout", 'Berhasil Logout');
        return redirect('/login')->with('success', 'Anda Berhasil Logout');
    }
}
