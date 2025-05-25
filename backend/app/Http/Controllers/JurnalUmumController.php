<?php

namespace App\Http\Controllers;

use App\Models\JurnalUmum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class JurnalUmumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = JurnalUmum::query();

        // Filter berdasarkan tanggal jika ada
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        $jurnalUmums = $query->orderBy('tanggal', 'asc')->get();
        return response()->json($jurnalUmums);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode' => 'required|string',
            'nama_akun' => 'required|string',
            'debit' => 'required|numeric|min:0',
            'kredit' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $jurnalUmum = JurnalUmum::create($validator->validated());
            return response()->json($jurnalUmum, 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal membuat jurnal.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $jurnalUmum = JurnalUmum::find($id);
        if (!$jurnalUmum) {
            return response()->json(['message' => 'Jurnal tidak ditemukan.'], 404);
        }
        return response()->json($jurnalUmum);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $jurnalUmum = JurnalUmum::find($id);
        if (!$jurnalUmum) {
            return response()->json(['message' => 'Jurnal tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode' => 'required|string',
            'nama_akun' => 'required|string',
            'debit' => 'required|numeric|min:0',
            'kredit' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $jurnalUmum->update($validator->validated());
            return response()->json($jurnalUmum);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui jurnal.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $jurnalUmum = JurnalUmum::find($id);

        if (!$jurnalUmum) {
            return response()->json(['message' => 'Jurnal tidak ditemukan.'], 404);
        }

        try {
            $jurnalUmum->delete();
            return response()->json(['message' => 'Jurnal berhasil dihapus.']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menghapus jurnal.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Import journals from an array of data (e.g., from Excel).
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entries' => 'required|array',
            'entries.*.kode' => 'required|string',
            'entries.*.nama_akun' => 'required|string',
            'entries.*.debit' => 'required|numeric|min:0',
            'entries.*.kredit' => 'required|numeric|min:0',
            'entries.*.tanggal' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $importedCount = 0;
        $errors = [];

        foreach ($request->entries as $entry) {
            try {
                JurnalUmum::create($entry);
                $importedCount++;
            } catch (Exception $e) {
                $errors[] = "Gagal mengimpor jurnal dengan kode '{$entry['kode']}': " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => "Sebagian jurnal berhasil diimpor ({$importedCount} jurnal).",
                'errors' => $errors
            ], 207); // Multi-Status
        }

        return response()->json(['message' => "Berhasil mengimpor {$importedCount} jurnal."], 201);
    }
}
