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
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $uploadedImages = [];

    foreach ($request->file('images') as $img) {
        $path = $img->store('user_images', 'public');

        $image = UserImage::create([
            'user_id'    => $request->user()->id,
            'image_path' => $path,
        ]);

        $uploadedImages[] = $image;
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
