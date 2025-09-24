<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use App\Models\Question;
use App\Models\UserAnswer;
use App\Models\Profile; // Optional, only if you still use profiles table
use App\Models\UserImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class ProfileController extends Controller

{
    public function store(StoreProfileRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $userId = $request->user()->id;

            // Optional: Update Profile table if you still use it
            // Profile::updateOrCreate(['user_id' => $userId], []);

            // Clear existing answers for this user to handle updates
            UserAnswer::where('user_id', $userId)->delete();

            // Save each answer based on question type
            foreach ($data as $key => $value) {
                if($key == 'fullName'){
                    Profile::where(
                        'user_id', $userId
                    )->update([
                        'full_name'=> $value
                    ]);
                }
                // dd($value);
                $question = Question::where('key', $key)->first();
                // dd($question);
                   if ($question) {
        UserAnswer::updateOrCreate(
            [
                'user_id' => $userId,
                'question_id' => $question->id,
            ],
            [
                'answer' => is_array($value) ? json_encode($value) : $value,
            ]
        );
    }

            }

            return response()->json([
                'success' => true,
                'message' => 'Profile saved successfully.',
                'data' => $data, // Return submitted data for confirmation
            ], 200);
        } catch (\Exception $e) {
            Log::error('Profile save error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save profile.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $answers = UserAnswer::where('user_id', $userId)
            ->with('question')
            ->get();

        if ($answers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found.',
            ], 404);
        }

        // Map answers to {key: value} with correct type
        $profileData = $answers->mapWithKeys(function ($answer) {
            $question = $answer->question;
            // Safe JSON decode
    $decodedValue = json_decode($answer->answer, true);
    $value = (json_last_error() === JSON_ERROR_NONE) ? $decodedValue : $answer->answer;

    $convertedValue = match ($question->type) {
        'scale'      => (int) $value,
        'boolean'    => (bool) $value,
        'multiselect'=> (array) $value,
        'text', 
        'select'     => (string) $value,
        default      => $value,
    };
            return [$question->key => $convertedValue];
        });

        return response()->json([
            'success' => true,
            'data' => $profileData,
        ]);
    }

   public function update(Request $request): JsonResponse
    {
        // Updated without images
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'bio' => 'sometimes|string',
                'age' => 'sometimes|integer|min:18',
                'location' => 'sometimes|string|max:255',
                'occupation' => 'sometimes|string|max:255',
                'education' => 'sometimes|string|max:255',
                'interests' => 'sometimes|array',
                'interests.*' => 'string',
                'looking_for' => 'sometimes|string',
                'relationship_goals' => 'sometimes|string',
            ]);

            $user = $request->user();
            $userId = $user->id;

            if (isset($validated['name'])) {
                $user->name = $validated['name'];
                $user->save();
                unset($validated['name']);
            }

            foreach ($validated as $key => $value) {
                $question = Question::where('key', $key)->first();

                if ($question) {
                    UserAnswer::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'question_id' => $question->id,
                        ],
                        [
                            'answer' => is_array($value) ? json_encode($value) : $value,
                        ]
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => $validated,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateImages(Request $request): JsonResponse
    {
        try {
            // Production-level validation
            $validated = $request->validate([
                'images.*' => [
                    'sometimes',
                    'file',
                    'image',
                    'mimes:jpeg,png,jpg,gif',
                    'max:2048', // 2MB max per image
                    Rule::dimensions()->maxWidth(2000)->maxHeight(2000), // Prevent very large images
                ],
                'existing_images' => 'sometimes|array|max:6', // Max 6 images total
                'existing_images.*' => 'string|url', // Validate existing as URLs
                'deleted_images' => 'sometimes|array', // Array of URLs to delete
                'deleted_images.*' => 'string|url',
                'primary_image' => 'sometimes|string|url', // URL of the primary image
            ]);

            $userId = $request->user()->id;
            $maxImages = 6; // Business rule: max 6 images per user

            // Get current images from DB
            $currentImages = UserImage::where('user_id', $userId)
                ->orderBy('order')
                ->get();

            // Handle deletions
            if (isset($validated['deleted_images'])) {
                foreach ($validated['deleted_images'] as $deletedUrl) {
                    $image = $currentImages->firstWhere('image_path', $deletedUrl);
                    if ($image) {
                        // Delete file from storage
                        $path = str_replace('/storage/', '', parse_url($deletedUrl, PHP_URL_PATH));
                        Storage::disk('public')->delete($path);
                        $image->delete();
                    }
                }
                // Refresh current images after deletion
                $currentImages = UserImage::where('user_id', $userId)
                    ->orderBy('order')
                    ->get();
            }

            // Prepare existing images
            $images = [];
            if (isset($validated['existing_images'])) {
                $images = $validated['existing_images'];
            }

            // Upload new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    if ($file->isValid()) {
                        // Generate unique filename
                        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('profile_images', $filename, 'public');
                        $url = Storage::url($path);
                        $images[] = $url;
                    }
                }
            }

            // Enforce max images
            if (count($images) > $maxImages) {
                return response()->json([
                    'success' => false,
                    'message' => "You can upload a maximum of $maxImages images.",
                ], 422);
            }

            // Clear existing DB images and re-insert
            UserImage::where('user_id', $userId)->delete();

            // Insert new/updated images
            foreach ($images as $order => $imagePath) {
                $isPrimary = (isset($validated['primary_image']) && $validated['primary_image'] === $imagePath);
                UserImage::create([
                    'user_id' => $userId,
                    'image_path' => $imagePath,
                    'is_primary' => $isPrimary,
                    'order' => $order,
                ]);
            }

            // If no primary specified and images exist, set first as primary
            if (!isset($validated['primary_image']) && !empty($images)) {
                $firstImage = UserImage::where('user_id', $userId)->first();
                if ($firstImage) {
                    $firstImage->is_primary = true;
                    $firstImage->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile images updated successfully.',
                'data' => [
                    'images' => $images,
                    'primary' => $validated['primary_image'] ?? $images[0] ?? null,
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Image update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update images.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
