<?php

namespace App\Http\Controllers;

use App\Models\JurnalUmum;
use Illuminate\Http\Request;

class BukuBesarController extends Controller
{
    /**
     * Get ledger entries based on account.
     */
    public function index(Request $request)
    {
        $query = JurnalUmum::query();

        // Filter berdasarkan kode akun jika ada
        if ($request->has('kode_akun') && !empty($request->kode_akun)) {
            $query->where('kode', $request->kode_akun);
        }

        // Filter berdasarkan tanggal jika ada
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        $entries = $query->orderBy('tanggal', 'asc')->get();

        // Hitung saldo berjalan
        $saldo = 0;
        $ledger = $entries->map(function ($entry) use (&$saldo) {
            $saldo += $entry->debit - $entry->kredit;
            return [
                'tanggal' => $entry->tanggal,
                'nama_akun' => $entry->nama_akun,
                'debit' => $entry->debit,
                'kredit' => $entry->kredit,
                'saldo' => $saldo
            ];
        });

        return response()->json($ledger);
    }
}
