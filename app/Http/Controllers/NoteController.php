<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Ambil semua catatan milik user yang sedang login
        // Jika ada filter id_jadwal (matkulId) dari query string, gunakan filter tersebut
        $query = Note::where('user_id', auth('sanctum')->id());
        
        if ($request->has('matkulId')) {
            $query->where('id_jadwal', $request->matkulId);
        }

        $notes = $query->get();

        return Response::json([
            'status' => 'success',
            'data' => $notes
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'judul_catatan' => 'required|string|max:255',
            'id_jadwal' => 'required|string', // Wajib terikat ke jadwal
            'isi_teks' => 'nullable|string',
        ]);
        
        // 1. Cek apakah id_jadwal valid dan milik user yang login (Security Check)
        $schedule = Schedule::where('id', $request->id_jadwal)
                            ->where('user_id', auth('sanctum')->id())
                            ->first();

        if (!$schedule) {
            return Response::json([
                'status' => 'error',
                'message' => 'Jadwal tidak ditemukan atau bukan milik Anda.'
            ], 404);
        }

        // 2. Simpan data
        $note = Note::create([
            'id' => (string) Str::uuid(),
            'user_id' => auth('sanctum')->id(),
            'id_jadwal' => $request->id_jadwal,
            'judul_catatan' => $request->judul_catatan,
            'isi_teks' => $request->isi_teks,
        ]);

        return Response::json([
            'status' => 'success',
            'message' => 'Catatan berhasil disimpan.',
            'data' => $note
        ], 201);
    }
    
    // ... Tambahkan show, update, destroy nanti (gunakan logika otentikasi yang sama dengan ScheduleController)
}