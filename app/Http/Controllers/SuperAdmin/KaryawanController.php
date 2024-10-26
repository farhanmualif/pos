<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    protected $karyawan;

    public function __construct(Karyawan $karyawan)
    {
        $this->karyawan = $karyawan;
    }
    public function index()
    {
        $content  = [
            'breadcrumb' => 'Dashboard',
            'breadcrumb_active' => 'Karyawan',
            'title' => 'Karyawan',
        ];

        $data['karyawan'] = $this->karyawan::orderByDesc('userId')->get();
        return view('super_admin.pages.master.karyawan.index', $content, $data);
    }
}
