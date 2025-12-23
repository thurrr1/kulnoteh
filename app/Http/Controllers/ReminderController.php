<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule; // Wajib import untuk Rule::in
use Illuminate\Support\Facades\Storage;

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
            'jenis_reminder' => 'required|string',
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

    public function show(string $id): JsonResponse
    {
        $reminder = Reminder::where('id', $id)
                            ->where('user_id', auth('sanctum')->id())
                            ->firstOrFail();

        return Response::json([
            'status' => 'success',
            'data' => $reminder
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $reminder = Reminder::where('id', $id)
                            ->where('user_id', auth('sanctum')->id())
                            ->firstOrFail();

        $request->validate([
            'jenis_reminder' => 'required|string',
            'tanggal' => 'required|date',
            'jam' => 'required|date_format:H:i:s',
            'keterangan' => 'nullable|string',
        ]);

        $reminder->update($request->all());

        return Response::json([
            'status' => 'success',
            'message' => 'Reminder berhasil diperbarui.',
            'data' => $reminder
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $reminder = Reminder::where('id', $id)
                            ->where('user_id', auth('sanctum')->id())
                            ->firstOrFail();

        $reminder->delete();

        return Response::json([
            'status' => 'success',
            'message' => 'Reminder berhasil dihapus.'
        ], 200);
    }
    
    // ... Tambahkan show, update, destroy (gunakan logika otentikasi yang sama dengan ScheduleController)

    /**
     * Get all files associated with a specific reminder.
     * GET /api/reminders/{id}/files
     */
    public function getFiles(string $id): JsonResponse
    {
        // Find the reminder for the authenticated user
        $reminder = Reminder::where('id', $id)
                            ->where('user_id', auth('sanctum')->id())
                            ->firstOrFail();

        // Load the files relationship. The 'url' attribute will be automatically
        // appended thanks to the accessor in the FileCatatan model.
        $files = $reminder->files;

        return Response::json([
            'status' => 'success',
            'data' => $files,
        ]);
    }
}