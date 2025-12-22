<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
            'isi_teks' => 'nullable|string', // Deprecated: untuk backward compatibility
            'content_json' => 'nullable|array', // JSON array dari List<NoteContentItem>
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
            'content_json' => $request->content_json ?? [], // Default empty array jika tidak ada
        ]);

        return Response::json([
            'status' => 'success',
            'message' => 'Catatan berhasil disimpan.',
            'data' => $note
        ], 201);
    }
    
    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $note = Note::where('id', $id)
                    ->where('user_id', auth('sanctum')->id())
                    ->first();

        if (!$note) {
            return Response::json([
                'status' => 'error',
                'message' => 'Catatan tidak ditemukan atau bukan milik Anda.'
            ], 404);
        }

        return Response::json([
            'status' => 'success',
            'data' => $note
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'judul_catatan' => 'required|string|max:255',
            'id_jadwal' => 'required|string',
            'isi_teks' => 'nullable|string',
            'content_json' => 'nullable|array',
        ]);
        
        // 1. Cari Note dan pastikan itu milik user yang login
        $note = Note::where('id', $id)
                    ->where('user_id', auth('sanctum')->id())
                    ->first();

        if (!$note) {
            return Response::json([
                'status' => 'error',
                'message' => 'Catatan tidak ditemukan atau bukan milik Anda.'
            ], 404);
        }

        // 2. Cleanup old files yang dihapus dari content
        $oldPaths = FileController::extractFilePathsFromContent($note->content_json ?? []);
        $newPaths = FileController::extractFilePathsFromContent($request->content_json ?? []);
        $deletedPaths = array_diff($oldPaths, $newPaths);
        
        foreach ($deletedPaths as $path) {
            try {
                Storage::disk('public')->delete($path);
                Log::info("Deleted orphaned file: $path");
            } catch (\Exception $e) {
                Log::error("Failed to delete file $path: " . $e->getMessage());
            }
        }

        // 3. Update data
        $note->update([
            'judul_catatan' => $request->judul_catatan,
            'isi_teks' => $request->isi_teks,
            'content_json' => $request->content_json ?? $note->content_json ?? [],
        ]);

        return Response::json([
            'status' => 'success',
            'message' => 'Catatan berhasil diperbarui.',
            'data' => $note
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        // 1. Cari Note dan pastikan itu milik user yang login
        $note = Note::where('id', $id)
                    ->where('user_id', auth('sanctum')->id())
                    ->first();

        if (!$note) {
            return Response::json([
                'status' => 'error',
                'message' => 'Catatan tidak ditemukan atau bukan milik Anda.'
            ], 404);
        }

        // 2. Cleanup semua file terkait
        $filePaths = FileController::extractFilePathsFromContent($note->content_json ?? []);
        foreach ($filePaths as $path) {
            try {
                Storage::disk('public')->delete($path);
                Log::info("Deleted file on note deletion: $path");
            } catch (\Exception $e) {
                Log::error("Failed to delete file $path: " . $e->getMessage());
            }
        }

        // 3. Hapus data
        $note->delete();

        return Response::json([
            'status' => 'success',
            'message' => 'Catatan berhasil dihapus.'
        ], 200);
    }
}