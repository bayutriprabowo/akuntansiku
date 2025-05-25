<?php

namespace App\Http\Controllers;

use App\Models\User; // Pastikan model User Anda ada dan benar
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Exception;

class AuthController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        // Validasi input dari form login Vue
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // Field 'identifier' dari Vue (bisa username atau email)
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255' // Opsional, untuk nama token
        ]);

        if ($validator->fails()) {
            // Kembalikan error validasi jika input tidak sesuai
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $credentials = $validator->validated();
        $identifier = $credentials['identifier'];
        $password = $credentials['password'];

        try {
            // Cari user berdasarkan username ATAU email (case-insensitive)
            $user = User::whereRaw('LOWER(username) = ?', [strtolower($identifier)])
                        ->orWhereRaw('LOWER(email) = ?', [strtolower($identifier)])
                        ->first();

            // Periksa apakah user ditemukan DAN passwordnya cocok
            if (!$user || !Hash::check($password, $user->password)) {
                // Jika user tidak ada atau password salah, kembalikan error 401
                return response()->json(['message' => 'Kredensial tidak valid.'], 401);
            }

            // Opsional: Periksa status user (jika ada kolom 'status' di tabel users)
            if (isset($user->status) && $user->status !== 'aktif') {
                 // Jika akun tidak aktif, kembalikan error 403
                 return response()->json(['message' => 'Akun Anda tidak aktif.'], 403);
            }

            // Jika login berhasil, buat token API menggunakan Sanctum
            $deviceName = $credentials['device_name'] ?? 'auth_token_user_' . $user->id;
            $token = $user->createToken($deviceName)->plainTextToken;

            // Kembalikan respons sukses berisi token dan data user (tanpa password)
            return response()->json([
                'message' => 'Login berhasil.',
                'token' => $token,
                'token_type' => 'Bearer', // Standar tipe token
                // Kirim data user yang relevan dan aman
                'user' => $user->only(['id', 'name', 'username', 'email', 'role', 'status'])
            ]);

        } catch (Exception $e) {
            Log::error('Login process error for identifier ' . $identifier . ': ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan internal saat mencoba login.'], 500);
        }
    }

     /**
      * Handle a logout request to the application.
      * (Pastikan rute ini dilindungi middleware auth:sanctum)
      *
      * @param  Request  $request
      * @return JsonResponse
      */
     public function logout(Request $request): JsonResponse
     {
         try {
             // Middleware 'auth:sanctum' akan memastikan $request->user() ada
             $user = $request->user();

             if ($user) {
                 // Hapus token API yang digunakan untuk request ini
                 $user->currentAccessToken()->delete();
                 return response()->json(['message' => 'Logout berhasil.']);
             }
             // Jika tidak ada user terautentikasi (seharusnya tidak terjadi jika middleware aktif)
             return response()->json(['message' => 'Tidak terautentikasi.'], 401);

         } catch (Exception $e) {
             Log::error('Logout error for user ID ' . ($request->user()?->id ?? 'unknown') . ': ' . $e->getMessage());
             return response()->json(['message' => 'Gagal logout, terjadi kesalahan server.'], 500);
         }
     }
}
