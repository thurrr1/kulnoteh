<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException; // Wajib import

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi Input (Email dan Password Wajib)
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Cari User berdasarkan Email
        $user = User::where('email', $request->email)->first();

        // 3. Cek User dan Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Jika user tidak ditemukan atau password salah
            throw ValidationException::withMessages([
                'message' => ['Email atau password salah.'],
            ]);
        }

        // 4. Hapus Token Lama (Opsional, untuk keamanan)
        $user->tokens()->delete();

        // 5. Buat Token Baru dengan Sanctum
        $token = $user->createToken('auth-token')->plainTextToken;

        // 6. Kembalikan Respons (cocok dengan ekspektasi Kotlin)
        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token, // Android akan menyimpan token ini
        ], 200);
    }

    public function register(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|min:6|confirmed', // 'confirmed' berarti harus ada 'password_confirmation'
        ]);

        // 2. Buat User Baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 3. Buat Token Baru dengan Sanctum
        $token = $user->createToken('auth-token')->plainTextToken;

        // 4. Kembalikan Respons Sukses
        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil. User dibuat dan login otomatis.',
            'user' => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ], 201); // 201 Created
    }
}