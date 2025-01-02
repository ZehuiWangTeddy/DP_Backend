<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:jpg,png,mp4|max:20480']);
        
        try {
            $file = $request->file('file');
            $safeName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file->getClientOriginalName());
            $path = $file->storeAs('media', $safeName, 'public');
            
            return response()->json(['url' => Storage::url($path)], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Upload failed'], 500);
        }
    }

    public function getMedia($id)
    {
        try {
            $path = "media/$id";
            if (!Storage::exists($path)) {
                return response()->json(['error' => 'File not found'], 404);
            }
            
            $file = Storage::get($path);
            return response($file, 200)->header('Content-Type', Storage::mimeType($path));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve file'], 500);
        }
    }

    public function delete($id)
    {
        try {
            $path = "media/$id";
            if (!Storage::exists($path)) {
                return response()->json(['error' => 'File not found'], 404);
            }
            
            Storage::delete($path);
            return response()->json(['message' => 'Deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete file'], 500);
        }
    }
}