<?php

namespace App\Http\Controllers;

use App\Models\Schedule; // Wajib import
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Wajib import untuk UUID

class ScheduleController extends Controller
{
    public function index()
    {
        // Ambil semua jadwal yang dimiliki oleh user yang sedang login
        $schedules = Schedule::where('user_id',auth('sanctum')->id())
                             ->get();

        return response()->json($schedules);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (Pastikan kolom wajib diisi)
        $request->validate([
            'nama_matakuliah' => 'required|string|max:255',
            'sks' => 'required|integer',
            'hari' => 'required|string',
            'jam_mulai' => 'required|date_format:H:i:s', // Format jam (HH:MM:SS)
            'jam_selesai' => 'required|date_format:H:i:s|after:jam_mulai',
            'ruangan' => 'nullable|string|max:255',
            'dosen' => 'nullable|string|max:255',
        ]);
        
        // 2. Buat ID unik (UUID) untuk konsistensi dengan Kotlin
        $uuid = (string) Str::uuid();

        // 3. Simpan data ke database
        $schedule = Schedule::create([
            'id' => $uuid,
            'user_id' => auth('sanctum')->id(), // Ambil ID user yang sedang login secara otomatis
            'nama_matakuliah' => $request->nama_matakuliah,
            'sks' => $request->sks,
            'dosen' => $request->dosen,
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'ruangan' => $request->ruangan,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil disimpan.',
            'data' => $schedule
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $schedule = Schedule::where('id', $id)
                            ->where('user_id', auth(guard: 'sanctum')->id())
                            ->first();

        if (!$schedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal tidak ditemukan atau bukan milik pengguna.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $schedule
        ]);
    }

    /**
     * Update the specified resource in storage. (PUT/PATCH /api/schedules/{id})
     */
    public function update(Request $request, string $id)
    {
        // 1. Validasi Input (gunakan validasi yang sama)
        $request->validate([
            'nama_matakuliah' => 'required|string|max:255',
            'sks' => 'required|integer',
            'hari' => 'required|string',
            'jam_mulai' => 'required|date_format:H:i:s',
            'jam_selesai' => 'required|date_format:H:i:s|after:jam_mulai',
            'ruangan' => 'nullable|string|max:255',
            'dosen' => 'nullable|string|max:255',
        ]);
        
        // 2. Cari Jadwal yang akan diupdate (dan pastikan itu milik user yang login)
        $schedule = Schedule::where('id', $id)
                            ->where('user_id', auth(guard: 'sanctum')->id())
                            ->first();

        if (!$schedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal tidak ditemukan atau bukan milik pengguna.'
            ], 404);
        }

        // 3. Update data
        $schedule->update($request->only([
            'nama_matakuliah', 'sks', 'dosen', 'hari', 'jam_mulai', 'jam_selesai', 'ruangan'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil diperbarui.',
            'data' => $schedule
        ]);
    }

    /**
     * Remove the specified resource from storage. (DELETE /api/schedules/{id})
     */
    public function destroy(string $id)
    {
        // 1. Cari Jadwal dan pastikan itu milik user yang login
        $schedule = Schedule::where('id', $id)
                            ->where('user_id', auth(guard: 'sanctum')->id())
                            ->first();

        if (!$schedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal tidak ditemukan atau bukan milik pengguna.'
            ], 404);
        }

        // 2. Hapus data
        $schedule->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil dihapus.'
        ], 200);
    }
}
