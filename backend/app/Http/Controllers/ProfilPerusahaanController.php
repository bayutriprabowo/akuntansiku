<?php

namespace App\Http\Controllers;

use App\Models\ProfilPerusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProfilPerusahaanController extends Controller
{
    /**
     * Get the company profile.
     */
    public function show()
    {
        $profil = ProfilPerusahaan::first();

        if (!$profil) {
            return response()->json(['message' => 'Profil perusahaan tidak ditemukan.'], 404);
        }

        return response()->json($profil);
    }

    /**
     * Create or update the company profile.
     */
    public function storeOrUpdate(Request $request)
    {
        Log::info('Data yang diterima:', $request->all()); // Debug log

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'tanggal_berdiri' => 'required|date',
            'telepon' => 'required|string|max:20',
            'alamat' => 'required|string',
            'email' => 'required|email|max:255|unique:profil_perusahaan,email,' . ($request->id ?? 'NULL') . ',id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profil = ProfilPerusahaan::first();

        if ($profil) {
            $profil->update($validator->validated());
            return response()->json(['message' => 'Profil perusahaan berhasil diperbarui.', 'data' => $profil]);
        } else {
            $profil = ProfilPerusahaan::create($validator->validated());
            return response()->json(['message' => 'Profil perusahaan berhasil dibuat.', 'data' => $profil], 201);
        }
    }

    /**
     * Delete the company profile.
     */
    public function destroy()
    {
        $profil = ProfilPerusahaan::first();

        if (!$profil) {
            return response()->json(['message' => 'Profil perusahaan tidak ditemukan.'], 404);
        }

        $profil->delete();
        return response()->json(['message' => 'Profil perusahaan berhasil dihapus.']);
    }
}
