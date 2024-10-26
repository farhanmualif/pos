<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanTransaksiController extends Controller
{
    protected $transaksi, $detailTransaksi;

    public function __construct(Transaksi $transaksi, TransaksiDetail $detailTransaksi)
    {
        $this->transaksi = $transaksi;
        $this->detailTransaksi = $detailTransaksi;
    }
    public function index()
    {
        $content = [
            'breadcrumb' => 'Dashboard',
            'breadcrumb_active' => 'Laporan Transaksi',
            'title' => 'Laporan Transaksi',
        ];
        $data['transaksi'] = $this->transaksi::where('mitraId', Auth::user()->mitra->id)->get();
        foreach ($data['transaksi'] as $dataTransaksi) {
            $data['detailTransaksi'][$dataTransaksi->id] = $this->detailTransaksi::where('transaksiId', $dataTransaksi->id)->get();
        }
        return view('admin.pages.transaksi.laporan_transaksi.index', $data, $content);
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

            $filter = $this->transaksi::where('statusOrder', $status)
                ->where('mitraId', Auth::user()->mitra->id)
                ->get();
            return back()->with(['filterBayar' => $filter, 'messages' => 'Data Filter ', 'status' => $status]);
        });
    }

    public function detailTransaksi($id)
    {
        $content = [
            'breadcrumb' => 'Dashboard',
            'breadcrumb_1' => 'Laporan Transaksi',
            'breadcrumb_active' => 'Detail  Transaksi',
            'title' => 'Detail  Transaksi',
        ];
        $data['transaksi'] = $this->transaksi::where('id', $id)->get();
        foreach ($data['transaksi'] as $dataTransaksi) {
            $data['detailTransaksi'][$dataTransaksi->id] = $this->detailTransaksi::where('transaksiId', $dataTransaksi->id)->get();
        }
        return view('admin.pages.transaksi.laporan_transaksi.detail_transaksi', $data, $content);
    }

    public function exportFilteredPdf(Request $request)
    {
        $filterBayar = $request->filterBayar;

        dd($filterBayar);
        if (empty($filterBayar)) {
            $dataTransaksi = $this->transaksi::where('mitraId', Auth::user()->mitra->id)->get();
        } else {
            $filter = $this->transaksi::where('statusOrder', $filterBayar)
                ->where('mitraId', Auth::user()->mitra->id)
                ->get();
        }

        $statusBayar = $filterBayar == 'PAID' ? 'Sudah Bayar' : ($filterBayar == 'UNPAID' ? 'Belum Bayar' : 'Semua');

        $data = [
            'title' => 'Laporan Transaksi Keseluruhan (' . $statusBayar . ')',
            'date' => Carbon::now()->locale('id')->translatedFormat('d F Y'),
        ];

        if (empty($filterBayar)) {
            $data['transaksi'] = $dataTransaksi;
        } else {
            $data['transaksi'] = $filter;
        }

        $pdf = Pdf::loadView('admin.pages.transaksi.laporan_transaksi.pdf.export_pdf', $data)->setPaper("a3");
        return $pdf->stream('laporan_transaksi.pdf');
    }
}
