<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            $data = $request->validated();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // Remove Hash::make, model auto hashes
                'role' => 'user', // Added role
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully.',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed.',
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => new UserResource($user),
                'role' => $user->role, // 👈 role frontend ko mil jayega
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image', // <-- removed mimes and size for testing
        ]);

        $user = $request->user();

        // Purani file delete (agar hai)
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // New file upload
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        // Save in DB
        $user->update([
            'profile_picture' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully.',
            'data' => new UserResource($user), // ✅ return updated user
        ]);
    }


    public function show(Request $request)
    {
        $user = $request->user()->load(['answers.question', 'images']);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string|max:500',
            'age' => 'nullable|integer|min:18|max:100',
            'location' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'interests' => 'nullable|array',
            'looking_for' => 'nullable|string|max:255',
            'relationship_goals' => 'nullable|string|max:255',
        ]);

        if (isset($data['interests'])) {
            $data['interests'] = json_encode($data['interests']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
