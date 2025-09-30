<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserImage;
use App\Models\UserVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        try {
            $currentUser = $request->user();
            $currentUserId = $currentUser->id;

            // Fetch current user's profile details from user table
            $currentInterests = json_decode($currentUser->interests ?? '[]', true);
            $currentPreferences = [
                'looking_for' => $currentUser->looking_for ?? '',
                'relationship_goals' => $currentUser->relationship_goals ?? '',
            ];
            $currentProfile = [
                'age' => (int) ($currentUser->age ?? 0),
                'location' => $currentUser->location ?? '',
                'occupation' => $currentUser->occupation ?? '',
                'education' => $currentUser->education ?? '',
            ];

            // Fetch media counts for current user (optional for scoring)
            $currentImageCount = UserImage::where('user_id', $currentUserId)->count();
            $currentVideoCount = UserVideo::where('user_id', $currentUserId)->count();
            $currentMedia = [
                'has_images' => $currentImageCount > 0,
                'has_videos' => $currentVideoCount > 0,
                'image_count' => $currentImageCount,
                'video_count' => $currentVideoCount,
            ];

            // Fetch all other public users
            $otherUsers = User::where('id', '!=', $currentUserId)
                ->where('profile_type', 'public')
                ->get();

            $matches = [];
            foreach ($otherUsers as $otherUser) {
                // Fetch images from UserImage
                $images = UserImage::where('user_id', $otherUser->id)
                    ->pluck('image_path')
                    ->toArray();

                // Fetch videos from UserVideo
                $videos = UserVideo::where('user_id', $otherUser->id)
                    ->pluck('video_path')
                    ->toArray();

                // Fetch other user's profile from user table
                $otherInterests = json_decode($otherUser->interests ?? '[]', true);
                $otherPreferences = [
                    'looking_for' => $otherUser->looking_for ?? '',
                    'relationship_goals' => $otherUser->relationship_goals ?? '',
                ];
                $otherProfile = [
                    'age' => (int) ($otherUser->age ?? 0),
                    'location' => $otherUser->location ?? '',
                    'occupation' => $otherUser->occupation ?? '',
                    'education' => $otherUser->education ?? '',
                ];

                // Fetch media for other user
                $otherImageCount = UserImage::where('user_id', $otherUser->id)->count();
                $otherVideoCount = UserVideo::where('user_id', $otherUser->id)->count();
                $otherMedia = [
                    'has_images' => $otherImageCount > 0,
                    'has_videos' => $otherVideoCount > 0,
                    'image_count' => $otherImageCount,
                    'video_count' => $otherVideoCount,
                ];

                // Calculate scores (skipped questions, focused on user table and media)
                $interestsScore = $this->calculateInterestsScore($currentInterests, $otherInterests);
                $preferencesScore = $this->calculatePreferencesScore($currentPreferences, $otherPreferences);
                $profileScore = $this->calculateProfileScore($currentProfile, $otherProfile);
                $mediaScore = $this->calculateMediaScore($currentMedia, $otherMedia);

                $totalScore = (
                    $interestsScore * 0.3 +
                    $preferencesScore * 0.3 +
                    $profileScore * 0.3 +
                    $mediaScore * 0.1
                );

                $matches[] = [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'age' => $otherUser->age ?? 'N/A',
                    'location' => $otherUser->location ?? 'N/A',
                    'bio' => $otherUser->bio ?? '',
                    'images' => $images ?: ['/placeholder.svg'],
                    'videos' => $videos ?: [], // Added videos
                    'compatibility' => round($totalScore),
                    'isOnline' => false,
                ];
            }

            // Sort by compatibility descending
            usort($matches, function($a, $b) {
                return $b['compatibility'] <=> $a['compatibility'];
            });

            return response()->json([
                'success' => true,
                'data' => $matches,
            ]);
        } catch (\Exception $e) {
            Log::error('Match error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch matches.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function calculateInterestsScore($curr, $other)
    {
        $common = count(array_intersect($curr, $other));
        $total = max(count($curr), count($other));
        $score = ($total > 0) ? ($common / $total) * 100 * 1.2 : 0; // 20% boost
        Log::debug("InterestsScore: $score (common: $common, total: $total)");
        return $score;
    }

    private function calculatePreferencesScore($curr, $other)
    {
        $score = 0;
        $count = 0;
        if ($curr['looking_for'] && $curr['looking_for'] === $other['looking_for']) {
            $score += 100;
            $count++;
        }
        if ($curr['relationship_goals'] && $curr['relationship_goals'] === $other['relationship_goals']) {
            $score += 100;
            $count++;
        }
        $avgScore = ($count > 0) ? $score / $count : 0;
        Log::debug("PreferencesScore: $avgScore (count: $count)");
        return $avgScore;
    }

    private function calculateProfileScore($curr, $other)
    {
        $score = 0;
        $count = 0;
        // Age: within 5 years
        if ($curr['age'] && $other['age']) {
            $ageDiff = abs($curr['age'] - $other['age']);
            if ($ageDiff <= 5) {
                $score += 100;
            } else if ($ageDiff <= 10) {
                $score += 75; // Relaxed scoring
            } else {
                $score += 50;
            }
            $count++;
        }
        // Location: exact or partial match
        if ($curr['location'] && $other['location']) {
            if (strtolower($curr['location']) === strtolower($other['location'])) {
                $score += 100;
            } else {
                $score += 50; // Fallback for different locations
            }
            $count++;
        }
        // Occupation: exact match
        if ($curr['occupation'] && $other['occupation']) {
            $score += (strtolower($curr['occupation']) === strtolower($other['occupation'])) ? 100 : 50;
            $count++;
        }
        // Education: exact match
        if ($curr['education'] && $other['education']) {
            $score += (strtolower($curr['education']) === strtolower($other['education'])) ? 100 : 50;
            $count++;
        }
        $avgScore = ($count > 0) ? $score / $count : 0;
        Log::debug("ProfileScore: $avgScore (ageDiff: " . ($curr['age'] - $other['age'] ?? 'N/A') . ", locationMatch: " . ($curr['location'] === $other['location'] ? 'yes' : 'no') . ", occupationMatch: " . ($curr['occupation'] === $other['occupation'] ? 'yes' : 'no') . ", educationMatch: " . ($curr['education'] === $other['education'] ? 'yes' : 'no') . ")");
        return $avgScore;
    }

    private function calculateMediaScore($curr, $other)
    {
        $score = 0;
        $count = 0;
        // Basic check: if both have images and videos
        if ($curr['has_images'] && $other['has_images']) {
            $score += 50;
            $count++;
        }
        if ($curr['has_videos'] && $other['has_videos']) {
            $score += 50;
            $count++;
        }
        // Bonus for similar media count (optional)
        $imageDiff = abs($curr['image_count'] - $other['image_count']);
        $videoDiff = abs($curr['video_count'] - $other['video_count']);
        if ($imageDiff <= 2) {
            $score += 25;
            $count++;
        }
        if ($videoDiff <= 1) {
            $score += 25;
            $count++;
        }
        $avgScore = ($count > 0) ? $score / $count : 0;
        Log::debug("MediaScore: $avgScore (has_images_match: " . ($curr['has_images'] && $other['has_images'] ? 'yes' : 'no') . ", has_videos_match: " . ($curr['has_videos'] && $other['has_videos'] ? 'yes' : 'no') . ")");
        return $avgScore;
    }
}