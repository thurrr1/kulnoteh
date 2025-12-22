<?php

namespace App\Http\Controllers;

use App\Models\FileCatatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class FileController extends Controller
{
    /**
     * Store a new file and associate it with a note or reminder.
     * POST /api/files
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,gif,pdf,doc,docx,mp3,wav,aac|max:20480', // Max 20MB
            'id_catatan' => [
                'nullable',
                'string',
                Rule::exists('notes', 'id'),
            ],
            'id_reminder' => [
                'nullable',
                'string',
                Rule::exists('reminders', 'id'),
            ],
        ]);

        // Ensure at least one foreign key is present
        if (!$request->filled('id_catatan') && !$request->filled('id_reminder')) {
            return Response::json([
                'status' => 'error',
                'message' => 'Either id_catatan or id_reminder must be provided.'
            ], 422);
        }

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            // Gunakan extension() untuk deteksi MIME-type yang lebih andal
            $extension = $file->extension(); 
            $mimeType = $file->getMimeType();
            
            // Generate unique filename, pastikan ekstensi ada
            $filename = Str::uuid() . ($extension ? '.' . $extension : '');
            
            // Determine folder based on mime type
            $folder = 'notes/documents'; // Default folder
            if (str_starts_with($mimeType, 'image/')) {
                $folder = 'notes/images';
            } elseif (str_starts_with($mimeType, 'audio/')) {
                $folder = 'notes/audio';
            }
            
            // Store file in storage/app/public/{folder}
            $path = $file->storeAs($folder, $filename, 'public');

            // KRITIKAL: Cek apakah file berhasil disimpan
            if (!$path) {
                return Response::json([
                    'status' => 'error',
                    'message' => 'Server gagal menyimpan file fisik. Periksa izin folder.'
                ], 500);
            }
            
            // Create database record
            $fileRecord = FileCatatan::create([
                'id_file' => Str::uuid()->toString(),
                'id_catatan' => $request->input('id_catatan'),
                'id_reminder' => $request->input('id_reminder'),
                'nama_file' => $originalName,
                'tipe_file' => $mimeType,
                'path_file' => $path,
            ]);
            
            return Response::json([
                'status' => 'success',
                'message' => 'File berhasil diupload dan disimpan.',
                'data' => $fileRecord,
            ], 201);
            
        } catch (\Exception $e) {
            // If file was stored but DB failed, delete the orphaned file
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return Response::json([
                'status' => 'error',
                'message' => 'Gagal mengupload file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Extract semua file paths dari content_json
     * Method static agar bisa dipanggil dari controller lain
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,gif,pdf,doc,docx|max:10240', // Max 10MB
            'type' => 'required|string|in:image,document', // Type untuk kategorisasi
        ]);

        try {
            $file = $request->file('file');
            
            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            
            // Tentukan folder berdasarkan type
            $folder = $request->type === 'image' ? 'notes/images' : 'notes/documents';
            
            // Simpan file ke storage/app/public/{folder}
            $path = $file->storeAs($folder, $filename, 'public');
            
            // Generate URL publik
            $url = url('storage/' . $path);
            
            return Response::json([
                'status' => 'success',
                'message' => 'File berhasil diupload.',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => $url,
                    'type' => $request->type,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]
            ], 201);
            
        } catch (\Exception $e) {
            return Response::json([
                'status' => 'error',
                'message' => 'Gagal mengupload file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus file dari storage
     * DELETE /api/files/{path}
     * Path format: notes/images/uuid.jpg atau notes/documents/uuid.pdf
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            $path = $request->path;
            
            // Security: Pastikan path hanya di dalam folder notes/
            if (!str_starts_with($path, 'notes/')) {
                return Response::json([
                    'status' => 'error',
                    'message' => 'Invalid file path.'
                ], 403);
            }
            
            // Cek apakah file exists
            if (!Storage::disk('public')->exists($path)) {
                return Response::json([
                    'status' => 'error',
                    'message' => 'File tidak ditemukan.'
                ], 404);
            }
            
            // Hapus file
            Storage::disk('public')->delete($path);
            
            return Response::json([
                'status' => 'success',
                'message' => 'File berhasil dihapus.'
            ], 200);
            
        } catch (\Exception $e) {
            return Response::json([
                'status' => 'error',
                'message' => 'Gagal menghapus file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Extract semua file paths dari content_json
     */
    public static function extractFilePathsFromContent($contentJson): array
    {
        $paths = [];
        
        if (!is_array($contentJson)) {
            return $paths;
        }
        
        foreach ($contentJson as $item) {
            // Extract image paths
            if (isset($item['type']) && $item['type'] === 'image' && isset($item['imageUri'])) {
                $url = $item['imageUri'];
                // Extract path from URL (format: http://domain/storage/notes/images/uuid.jpg)
                if (str_contains($url, '/storage/')) {
                    $path = str_replace(url('storage/'), '', $url);
                    if (str_starts_with($path, 'notes/')) {
                        $paths[] = $path;
                    }
                }
            }
            
            // Extract file paths
            if (isset($item['type']) && $item['type'] === 'file' && isset($item['fileUri'])) {
                $url = $item['fileUri'];
                if (str_contains($url, '/storage/')) {
                    $path = str_replace(url('storage/'), '', $url);
                    if (str_starts_with($path, 'notes/')) {
                        $paths[] = $path;
                    }
                }
            }
        }
        
        return array_unique($paths);
    }
}
