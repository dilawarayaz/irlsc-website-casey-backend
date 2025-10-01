<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;

class AdminUserController extends Controller
{
    /**
     * Get all users with profile details
     */
    public function index(Request $request)
    {
        $users = User::where('role', 'user')
            ->whereHas('videos') // sirf un users ko lana jinki videos hain
            ->with(['images', 'videos']) // images + videos dono lana
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }


    /**
     * Get single user profile (admin view)
     */
    public function show($id)
    {
        $user = User::with(['images', 'answers.question'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }
}
