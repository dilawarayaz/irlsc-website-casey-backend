<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UploadVideoRequest;
use App\Http\Resources\UserResource;
use App\Models\Question;
use App\Models\UserAnswer;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserImage;
use App\Models\UserVideo;
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
            Profile::updateOrCreate(['user_id' => $userId], []);

            // Clear existing answers for this user to handle updates
            UserAnswer::where('user_id', $userId)->delete();

            // Save each answer based on question type
            foreach ($data as $key => $value) {
                if ($key == 'fullName') {
                    Profile::where('user_id', $userId)->update(['full_name' => $value]);
                }
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
                'message' => 'Profile saved successfully.',
                'data' => $data,
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

        $profileData = $answers->mapWithKeys(function ($answer) {
            $question = $answer->question;
            $decodedValue = json_decode($answer->answer, true);
            $value = (json_last_error() === JSON_ERROR_NONE) ? $decodedValue : $answer->answer;

            $convertedValue = match ($question->type) {
                'scale'      => (int) $value,
                'boolean'    => (bool) $value,
                'multiselect' => (array) $value,
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
    public function uploadImages(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'images'   => 'required|array',
                'images.*' => 'required|file|image|max:2048',
            ]);

            $uploadedImages = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    if ($file->isValid()) {
                        $originalName = $file->getClientOriginalName();
                        $mime = $file->getClientMimeType();
                        $size = $file->getSize();

                        $path = $file->store("images/user_{$user->id}", 'public');

                        $image = UserImage::create([
                            'user_id'     => $user->id,
                            'image_path'  => $path,
                            'original_name' => $originalName,
                            'mime_type'   => $mime,
                            'size'        => $size,
                        ]);

                        $uploadedImages[] = [
                            'id'        => $image->id,
                            'url'       => Storage::disk('public')->url($path),
                            'name'      => $originalName,
                            'mime_type' => $mime,
                            'size'      => $size,
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully.',
                'data'    => $uploadedImages,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Image upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadVideo(UploadVideoRequest $request)
    {
        try {
            $user = $request->user();
            $file = $request->file('video');
            $originalName = $file->getClientOriginalName();
            $mime = $file->getClientMimeType();
            $size = $file->getSize();

            $path = $file->store("videos/user_{$user->id}", 'public');

            $video = UserVideo::create([
                'user_id' => $user->id,
                'video_path' => $path,
                'original_name' => $originalName,
                'mime_type' => $mime,
                'size' => $size,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Video uploaded successfully.',
                'data' => $video,
                'video_url' => Storage::disk('public')->url($path),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Video upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload video.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleProfileType(Request $request)
    {
        $user = $request->user();
        $action = $request->input('action');

        if (!in_array($action, ['make_public', 'make_private'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid action. Use make_public or make_private.'
            ], 422);
        }

        if ($action === 'make_public') {
            $hasImage = \DB::table('user_images')->where('user_id', $user->id)->exists();
            $hasVideo = \DB::table('user_videos')->where('user_id', $user->id)->exists();

            if (!$hasImage || !$hasVideo) {
                $missing = [];
                if (!$hasImage) $missing[] = 'images';
                if (!$hasVideo) $missing[] = 'video';
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot make profile public. Please upload: ' . implode(', ', $missing),
                ], 400);
            }

            $user->profile_type = 'public';
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile is now public.',
                'data' => ['profile_type' => $user->profile_type],
            ]);
        }

        $user->profile_type = 'private';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile is now private.',
            'data' => ['profile_type' => $user->profile_type],
        ]);
    }

    public function listVideos(Request $request)
    {
        $videos = $request->user()->videos()->get()->map(function ($v) {
            return [
                'id' => $v->id,
                'video_url' => Storage::disk('public')->url($v->video_path),
                'original_name' => $v->original_name,
                'mime_type' => $v->mime_type,
                'size' => $v->size,
                'created_at' => $v->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $videos,
        ]);
    }

    public function getPublicProfiles(Request $request)
    {
        try {
            // Load videos along with answers and images
            $publicUsers = User::where('profile_type', 'public')
                ->with(['answers.question', 'images', 'videos']) // Added videos relation
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Public profiles fetched successfully.',
                'data' => UserResource::collection($publicUsers),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Public profiles fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch public profiles.',
            ], 500);
        }
    }

    public function getPrivateProfiles(Request $request)
{
    try {
        // Load videos along with answers and images
        $privateUsers = User::where('profile_type', 'private')
            ->with(['answers.question', 'images', 'videos']) // Added videos relation
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Private profiles fetched successfully.',
            'data' => UserResource::collection($privateUsers),
        ], 200);
    } catch (\Exception $e) {
        Log::error('Private profiles fetch error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch private profiles.',
        ], 500);
    }
}

}