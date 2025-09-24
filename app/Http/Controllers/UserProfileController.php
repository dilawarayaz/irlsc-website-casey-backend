<?php

namespace App\Http\Controllers;

use App\Models\UserImage;
use Illuminate\Http\Request;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
     public function show()
    {
        $profile = Profile::where('user_id', Auth::id())->with('images')->first();
        return response()->json($profile);
    }

    // Update or create profile
    public function update(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'looking_for' => 'nullable|string|max:255',
            'relationship_goals' => 'nullable|string|max:255',
        ]);

        $profile = Profile::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $profile,
        ]);
    }

    // Upload image
    public function uploadImage(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'is_primary' => 'boolean',
        ]);

        $path = $request->file('image')->store('profile_images', 'public');

        $image = UserImage::create([
            'user_id' => Auth::id(),
            'image_path' => $path,
            'is_primary' => $request->input('is_primary', false),
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully',
            'image' => $image,
        ]);
    }

    // Delete image
    public function deleteImage($id)
    {
        $image = UserImage::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully']);
    }
}
