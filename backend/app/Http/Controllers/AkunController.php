<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class AkunController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Akun::query();

        // Filter berdasarkan tipe klasifikasi jika ada
        if ($request->has('klasifikasi') && !empty($request->klasifikasi)) {
            $query->where('tipe_klasifikasi', $request->klasifikasi);
        }

        $akuns = $query->get();
        return response()->json($akuns);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|unique:akuns,kode',
            'nama_akun' => 'required|string',
            'tipe_debit_kredit' => 'required|in:debit,kredit',
            'tipe_klasifikasi' => 'required|string',
            'kode_akun_kontra' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $akun = Akun::create($validator->validated());
            return response()->json($akun, 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal membuat akun.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $akun = Akun::find($id);
        if (!$akun) {
            return response()->json(['message' => 'Akun tidak ditemukan.'], 404);
        }
        return response()->json($akun);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $akun = Akun::find($id);
        if (!$akun) {
            return response()->json(['message' => 'Akun tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|unique:akuns,kode,' . $id,
            'nama_akun' => 'required|string',
            'tipe_debit_kredit' => 'required|in:debit,kredit',
            'tipe_klasifikasi' => 'required|string',
            'kode_akun_kontra' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $akun->update($validator->validated());
            return response()->json($akun);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui akun.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $akun = Akun::find($id);
        if (!$akun) {
            return response()->json(['message' => 'Akun tidak ditemukan.'], 404);
        }

        try {
            $akun->delete();
            return response()->json(['message' => 'Akun berhasil dihapus.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menghapus akun.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Import accounts from an array of data (e.g., from Excel).
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entries' => 'required|array',
            'entries.*.kode' => 'required|string|distinct|unique:akuns,kode',
            'entries.*.nama_akun' => 'required|string',
            'entries.*.tipe_debit_kredit' => 'required|in:debit,kredit',
            'entries.*.tipe_klasifikasi' => 'required|string',
            'entries.*.kode_akun_kontra' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $importedCount = 0;
        $errors = [];

        foreach ($request->entries as $akunData) {
            try {
                Akun::create([
                    'kode' => $akunData['kode'],
                    'nama_akun' => $akunData['nama_akun'],
                    'tipe_debit_kredit' => $akunData['tipe_debit_kredit'],
                    'tipe_klasifikasi' => $akunData['tipe_klasifikasi'],
                    'kode_akun_kontra' => $akunData['kode_akun_kontra'],
                ]);
                $importedCount++;
            } catch (Exception $e) {
                $errors[] = "Gagal mengimpor akun '{$akunData['kode']}': " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => "Sebagian akun berhasil diimpor ({$importedCount} akun).",
                'errors' => $errors
            ], 207); // Multi-Status
        }

        return response()->json(['message' => "Berhasil mengimpor {$importedCount} akun."], 201);
    }
}
