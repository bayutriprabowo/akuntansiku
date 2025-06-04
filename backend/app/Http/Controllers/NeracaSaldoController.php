<?php

namespace App\Http\Controllers;

use App\Models\JurnalUmum;
use Illuminate\Http\Request;

class NeracaSaldoController extends Controller
{
    /**
     * Get trial balance data.
     */
    public function index(Request $request)
    {
        $query = JurnalUmum::join('akuns', 'jurnal_umums.kode', '=', 'akuns.kode')
            ->select('jurnal_umums.kode', 'akuns.nama_akun', 'akuns.tipe_debit_kredit')
            ->selectRaw('SUM(jurnal_umums.debit) as total_debit, SUM(jurnal_umums.kredit) as total_kredit')
            ->groupBy('jurnal_umums.kode', 'akuns.nama_akun', 'akuns.tipe_debit_kredit');

        // Filter berdasarkan tanggal jika ada
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('jurnal_umums.tanggal', [$request->start_date, $request->end_date]);
        }

        $entries = $query->orderBy('jurnal_umums.kode', 'asc')->get();

        // Format data untuk frontend
        $trialBalance = $entries->map(function ($entry) {
            return [
                'kode' => $entry->kode,
                'nama_akun' => $entry->nama_akun,
                'tipe_debit_kredit' => $entry->tipe_debit_kredit,
                'debit' => $entry->total_debit,
                'kredit' => $entry->total_kredit
            ];
        });

        return response()->json($trialBalance);
    }
}
