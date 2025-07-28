<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FileManagerController extends Controller
{
    private function userBasePath()
    {
        return 'uploads/' . Str::slug(Auth::user()->name) . '-' . Auth::id();
    }

    public function index()
    {
        return view('filemanager/index');
    }

    public function tree()
    {
        $disk = Storage::disk('public');
        $base = 'uploads';

        $user = Auth::user();
        $folderName = Str::slug($user->name) . '-' . $user->id;
        $userPath = "$base/$folderName";

        // Ambil semua subfolder user
        $directories = collect($disk->allDirectories($userPath));

        // Tambahkan folder root user
        $directories->prepend($userPath);

        $tree = $directories->map(function ($dir) use ($userPath) {
            return [
                'id' => $dir,
                'parent' => $dir === $userPath ? '#' : dirname($dir),
                'text' => basename($dir),
                'icon' => '📁',
            ];
        });

        return response()->json($tree);
    }

    public function getTree()
    {
        $base = storage_path('app/public/' . $this->userBasePath());
        $children = $this->scanFolder($base, $this->userBasePath());
        return response()->json($children);
    }

    private function scanFolder($path, $prefix)
    {
        $items = [];

        if (!is_dir($path)) return $items;

        foreach (scandir($path) as $file) {
            if ($file === '.' || $file === '..') continue;

            $fullPath = $path . DIRECTORY_SEPARATOR . $file;
            $id = $prefix . '/' . $file;

            if (is_dir($fullPath)) {
                $items[] = [
                    'id' => $id,
                    'text' => $file,
                    'children' => $this->scanFolder($fullPath, $id),
                    'icon' => 'jstree-folder'
                ];
            }
        }

        return $items;
    }

    public function browse($path = '')
    {
        $userFolder = 'uploads/' . Str::slug(Auth::user()->name) . '-' . Auth::id();
        $safePath = $userFolder . '/' . ltrim($path, '/');

        Log::info("🔍 Browse request path: " . $path);
        Log::info("✅ Safe full path: " . $safePath);

        // Pastikan hanya akses folder milik user
        if (!str_starts_with($safePath, $userFolder)) {
            Log::error("🚫 Akses tidak diizinkan ke folder ini: " . $safePath);
            return response()->json(['message' => 'Akses tidak diizinkan.'], 403);
        }

        // Pastikan folder benar-benar ada secara fisik (meskipun kosong)
        if (!is_dir(storage_path('app/public/' . $safePath))) {
            Log::warning("❌ Folder tidak ditemukan (fisik): " . $safePath);
            return response()->json(['message' => 'Folder tidak ditemukan.'], 404);
        }

        // Ambil file-file (jika ada)
        $files = collect(Storage::disk('public')->files($safePath))->map(function ($file) {
            return [
                'name' => basename($file),
                'size' => Storage::disk('public')->size($file),
                'type' => pathinfo($file, PATHINFO_EXTENSION),
                'path' => $file,
            ];
        });

        return response()->json($files);
    }

    public function createFolder(Request $request)
    {
        $parent = $request->input('parent');
        $name = $request->input('name');

        $user = Auth::user();
        $folderName = Str::slug($user->name) . '-' . $user->id;

        // Hilangkan prefix 'uploads/' dan 'folderName/' jika ada di $parent
        $cleanParent = preg_replace("#^uploads/{$folderName}/?#", '', $parent);

        // Bangun path akhir
        $fullPath = 'uploads/' . $folderName . ($cleanParent ? '/' . trim($cleanParent, '/') : '') . '/' . trim($name, '/');

        if (!Storage::disk('public')->exists($fullPath)) {
            Storage::disk('public')->makeDirectory($fullPath);
            return response()->json(['message' => 'Folder created.']);
        } else {
            return response()->json(['message' => 'Folder already exists.'], 400);
        }
    }

    public function renameFolder(Request $request)
    {
        $oldPath = trim($request->input('path'), '/');     // contoh: dedi-1/dedi3
        $newName = trim($request->input('newName'), '/');  // contoh: dedi2

        // Cegah directory traversal
        if (str_contains($oldPath, '..') || str_contains($newName, '..')) {
            return response()->json(['message' => 'Nama folder tidak valid!'], 400);
        }

        $disk = Storage::disk('public');
        $base = 'uploads';

        // Buang prefix 'uploads/' jika sudah ada di path
        $relativePath = Str::startsWith($oldPath, "$base/") ? Str::after($oldPath, "$base/") : $oldPath;

        $fullOldPath = $base . '/' . $relativePath;
        $parent = dirname($relativePath);

        $newPath = $parent === '.'
            ? $base . '/' . $newName
            : $base . '/' . $parent . '/' . $newName;

        if (!$disk->exists($fullOldPath)) {
            return response()->json(['message' => 'Folder asal tidak ditemukan!'], 404);
        }

        if ($disk->exists($newPath)) {
            return response()->json(['message' => 'Folder tujuan sudah ada!'], 409);
        }

        $disk->move($fullOldPath, $newPath);

        return response()->json(['message' => 'Folder berhasil diubah!', 'path' => $newPath]);
    }


    // public function renameFolder(Request $request)
    // {
    //     $oldPath = $request->input('old_path');
    //     $newName = $request->input('new_name');
    //     $userRoot = 'uploads/' . Str::slug(Auth::user()->name) . '-' . Auth::id();

    //     if ($oldPath === $userRoot) {
    //         return response()->json(['error' => 'Folder utama tidak boleh di-rename.'], 403);
    //     }

    //     $newPath = dirname($oldPath) . '/' . $newName;
    //     Storage::disk('public')->move($oldPath, $newPath);

    //     return response()->json(['success' => true]);
    // }

    public function deleteFolder(Request $request)
    {
        $folder = $request->input('folder');

        // Validasi akses folder berdasarkan user
        $userBasePath = 'uploads/' . Str::slug(Auth::user()->name) . '-' . Auth::id();
        if (!Str::startsWith($folder, $userBasePath)) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $disk = Storage::disk('public');

        if ($disk->exists($folder)) {
            $disk->deleteDirectory($folder);
            return response()->json(['message' => 'Folder berhasil dihapus.']);
        }

        return response()->json(['message' => 'Folder tidak ditemukan.'], 404);
    }

    public function upload(Request $req)
    {
        $req->validate(['file' => 'required|file']);

        $folderInput = $req->input('folder');
        $userBase = $this->userBasePath();

        if (Str::startsWith($folderInput, $userBase)) {
            $path = $folderInput;
        } else {
            $path = rtrim($userBase . '/' . ltrim($folderInput, '/'), '/');
        }

        $filename = $req->file('file')->getClientOriginalName();

        $req->file('file')->storeAs($path, $filename, 'public');

        return response()->json(['message' => 'File berhasil diunggah!']);
    }

    public function deleteFile(Request $req)
    {
        $path = $req->input('path');

        if (!Str::startsWith($path, $this->userBasePath())) {
            return response()->json(['message' => 'Akses ditolak!'], 403);
        }

        Storage::disk('public')->delete($path);
        return response()->json(['message' => 'File dihapus']);
    }
}
