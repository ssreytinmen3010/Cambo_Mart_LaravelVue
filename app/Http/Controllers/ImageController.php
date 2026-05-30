<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $validated = $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'The image failed to upload.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                
                // Ensure the file is valid
                if (!$file->isValid()) {
                    return response()->json([
                        'message' => 'The image failed to upload.',
                        'errors' => ['image' => ['Uploaded file is not valid']],
                    ], 400);
                }

                // Store the file in the 'public/uploads' directory on the 'public' disk
                $path = $file->store('uploads', 'public');
                
                // Get the publicly accessible URL
                $url = asset('storage/' . $path);

                return response()->json(['url' => $url], 200);
            }
    
            return response()->json([
                'message' => 'The image failed to upload.',
                'errors' => ['image' => ['No image file provided']],
            ], 400);

        } catch (\Exception $e) {
            Log::error('Local Storage Upload Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'The image failed to upload.',
                'errors' => ['image' => ['Upload failed: ' . $e->getMessage()]],
            ], 500);
        }
    }
}