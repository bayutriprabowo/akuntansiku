<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Untuk validasi unik yang lebih baik
use Exception; // Untuk menangani error umum

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Query dasar untuk mengambil semua user
        $query = User::query();

        // Filter berdasarkan role jika ada parameter 'role' di request
        if ($request->has('role') && !empty($request->role)) {
            $query->where('role', $request->role);
        }

        // Ambil data user, diurutkan berdasarkan ID atau nama (opsional)
        $users = $query->orderBy('name')->get();

        // Kembalikan data user sebagai JSON
        // Tidak perlu menyertakan password dalam respons list
        return response()->json($users->map(function ($user) {
            return $user->only(['id', 'username', 'email', 'name', 'role', 'status', 'created_at', 'updated_at']);
        }));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi data input dari frontend
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8', // Tambahkan validasi panjang password jika perlu
            'role' => ['required', 'string', Rule::in(['user', 'admin', 'direktur'])], // Validasi role
            'status' => ['required', 'string', Rule::in(['aktif', 'nonaktif'])], // Validasi status
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        try {
            // Buat user baru
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'name' => $request->name,
                'password' => Hash::make($request->password), // Hash password sebelum disimpan
                'role' => $request->role,
                'status' => $request->status,
            ]);

            // Kembalikan data user yang baru dibuat (tanpa password)
            return response()->json($user->only(['id', 'username', 'email', 'name', 'role', 'status', 'created_at', 'updated_at']), 201); // Created
        } catch (Exception $e) {
            // Tangani error jika pembuatan user gagal
            return response()->json(['message' => 'Gagal membuat user.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Cari user berdasarkan ID
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404); // Not Found
        }

        // Validasi data input (username dan email harus unik, kecuali untuk user saat ini)
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'name' => 'required|string|max:255',
            'role' => ['required', 'string', Rule::in(['user', 'admin', 'direktur'])],
            'status' => ['required', 'string', Rule::in(['aktif', 'nonaktif'])],
            // Password tidak di-require saat update, hanya diupdate jika ada input baru
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Update data user
            $user->username = $request->username;
            $user->email = $request->email;
            $user->name = $request->name;
            $user->role = $request->role;
            $user->status = $request->status;

            // Update password hanya jika ada input password baru
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Kembalikan data user yang sudah diupdate (tanpa password)
            return response()->json($user->only(['id', 'username', 'email', 'name', 'role', 'status', 'created_at', 'updated_at']));
        } catch (Exception $e) {
            // Tangani error jika update user gagal
            return response()->json(['message' => 'Gagal memperbarui user.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Cari user berdasarkan ID
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        try {
            // Hapus user
            $user->delete();
            // Kembalikan respons sukses tanpa konten
            return response()->json(['message' => 'User berhasil dihapus.'], 200); // Atau 204 No Content
        } catch (Exception $e) {
            // Tangani error jika penghapusan user gagal
            return response()->json(['message' => 'Gagal menghapus user.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Import users from an array of data (e.g., from Excel).
     */
    public function import(Request $request)
    {
        // Validasi bahwa input 'users' ada dan merupakan array
        $validator = Validator::make($request->all(), [
            'users' => 'required|array',
            'users.*.username' => 'required|string|max:255|distinct|unique:users,username', // distinct untuk validasi unik dalam array input
            'users.*.email' => 'required|string|email|max:255|distinct|unique:users,email',
            'users.*.name' => 'required|string|max:255',
            'users.*.password' => 'required|string|min:8',
            'users.*.role' => ['required', 'string', Rule::in(['user', 'admin', 'direktur'])],
            'users.*.status' => ['required', 'string', Rule::in(['aktif', 'nonaktif'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $importedCount = 0;
        $errors = [];

        foreach ($request->users as $userData) {
            try {
                User::create([
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'status' => $userData['status'],
                ]);
                $importedCount++;
            } catch (Exception $e) {
                // Kumpulkan error jika ada user yang gagal diimpor
                $errors[] = "Gagal mengimpor user '{$userData['username']}': " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => "Sebagian user berhasil diimpor ({$importedCount} user).",
                'errors' => $errors
            ], 207); // Multi-Status
        }

        return response()->json(['message' => "Berhasil mengimpor {$importedCount} user."], 201);
    }
}
