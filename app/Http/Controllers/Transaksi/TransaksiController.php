<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransaksiController extends Controller
{
    protected $transaksi;
    protected $detailTransaksi;

    public function __construct(Transaksi $transaksi, TransaksiDetail $detailTransaksi)
    {
        $this->transaksi = $transaksi;
        $this->detailTransaksi = $detailTransaksi;
    }
    public function index()
    {
        $content = [
            'breadcrumb' => 'Dashboard',
            'breadcrumb_active' => 'Transaksi',
            'title' => 'Transaksi',
        ];

        $data = [
            'transaksi' => $this->transaksi::all(),
            'detailTransaksi' => [],
        ];

        foreach ($data['transaksi'] as $dataTransaksi) {
            $data['detailTransaksi'][$dataTransaksi->id] = $this->detailTransaksi::where('transaksiId', $dataTransaksi->id)->get();
        }
        return view('super_admin.pages.transaksi.index', $data, $content);
    }

    public function filterByStatus(Request $request)
    {
        $messages = [
            "required" => "Kolom :attribute Harus Diisi",
        ];

        $this->validate($request, [
            'filterBayar' => 'required',
        ], $messages);

        return DB::transaction(function () use ($request) {
            $status = $request->input('filterBayar');

            $filter = $this->transaksi::where('statusOrder', $status)->get();
            return back()->with(['filterBayar' => $filter, 'messages' => 'Data Filter ', 'status' => $status]);
        });
    }

    public function handleEwalletSuccessCallback(Request $request)
    {
        try {
            Log::info('E-wallet Success Callback', $request->all());
            return view('payment.ewallet-success');
        } catch (\Exception $e) {
            Log::error('Error E-wallet Success Callback: ' . $e->getMessage());

            return view('payment.ewallet-success')->with('error', $e->getMessage());
        }
    }
}
