<?php

namespace App\Http\Controllers;

use App\Models\UserImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserImageController extends Controller
{
    // Upload new image
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // max 2MB
        ]);

        $path = $request->file('image')->store('user_images', 'public');

        $image = UserImage::create([
            'user_id' => $request->user()->id,
            'image_path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully.',
            'data' => $image,
        ]);
    }

    // Delete image
    public function destroy(Request $request, $id)
    {
        $image = UserImage::where('user_id', $request->user()->id)->findOrFail($id);

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.',
        ]);
    }
}
