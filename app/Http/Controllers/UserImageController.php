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
            'images'   => 'required|array',
            'images.*' => 'image',
        ]);

        // Limit to 5 images per user
        $uploadedImages = [];

        foreach ($request->file('images') as $img) {
            $path = $img->store('user_images', 'public');

            $image = UserImage::create([
                'user_id'    => $request->user()->id,
                'image_path' => $path,
            ]);

            $uploadedImages[] = [
                'id'  => $image->id,
                'url' => $image->image_url, // 👈 accessor se full URL aa jayega
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully.',
            'data'    => $uploadedImages,
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
