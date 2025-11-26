<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule; // Wajib import untuk Rule::in

class ReminderController extends Controller
{
    public function index(): JsonResponse
    {
        // Ambil semua reminder milik user yang sedang login
        $reminders = Reminder::where('user_id', auth('sanctum')->id())->get();

        return Response::json([
            'status' => 'success',
            'data' => $reminders
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'jenis_reminder' => ['required', Rule::in(['Tugas', 'Ujian', 'Mandiri', 'KelasGanti'])],
            'tanggal' => 'required|date', 
            'jam' => 'required|date_format:H:i:s', // Format jam
            'keterangan' => 'nullable|string', // Sesuai description di Kotlin
        ]);
        
        // Simpan data
        $reminder = Reminder::create([
            'id' => (string) Str::uuid(),
            'user_id' => auth('sanctum')->id(),
            'jenis_reminder' => $request->jenis_reminder,
            'tanggal' => $request->tanggal,
            'jam' => $request->jam,
            'keterangan' => $request->keterangan,
        ]);

        return Response::json([
            'status' => 'success',
            'message' => 'Reminder berhasil disimpan.',
            'data' => $reminder
        ], 201);
    }
    
    // ... Tambahkan show, update, destroy (gunakan logika otentikasi yang sama dengan ScheduleController)
}