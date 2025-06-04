<?php

namespace App\Http\Controllers;

use App\Models\JurnalUmum;
use App\Models\Akun;
use Illuminate\Http\Request;

class NeracaController extends Controller
{
    /**
     * Get balance sheet data.
     */
    public function index(Request $request)
    {
        $query = JurnalUmum::join('akuns', 'jurnal_umums.kode', '=', 'akuns.kode')
            ->select('jurnal_umums.kode', 'akuns.nama_akun', 'akuns.tipe_klasifikasi', 'jurnal_umums.debit', 'jurnal_umums.kredit');

        // Filter berdasarkan waktu
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('jurnal_umums.tanggal', [$request->start_date, $request->end_date]);
        }

        $entries = $query->orderBy('akuns.tipe_klasifikasi', 'asc')->get();

        return response()->json($entries);
    }
}
