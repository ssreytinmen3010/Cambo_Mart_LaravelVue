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
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                
                // Ensure the file is valid
                if (!$file->isValid()) {
                    return response()->json(['error' => 'Uploaded file is not valid'], 400);
                }

                // Store the file in the 'public/uploads' directory on the 'public' disk
                $path = $file->store('uploads', 'public');
                
                // Get the publicly accessible URL
                $url = asset('storage/' . $path);

                return response()->json(['url' => $url], 200);
            }
    
            return response()->json(['error' => 'No image file provided'], 400);

        } catch (\Exception $e) {
            Log::error('Local Storage Upload Error: ' . $e->getMessage());
            return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }
}