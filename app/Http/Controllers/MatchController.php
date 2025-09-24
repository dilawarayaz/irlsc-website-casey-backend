<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAnswer;
use App\Models\UserImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        try {
            $currentUser = $request->user();
            $currentUserId = $currentUser->id;

            // Fetch current user's answers from UserAnswer (for questions)
            $currentAnswers = UserAnswer::where('user_id', $currentUserId)
                ->with('question')
                ->get()
                ->keyBy('question.key');

            // Fetch profile details from user table
            $currentInterests = json_decode($currentUser->interests ?? '[]', true);
            $currentPreferences = [
                'looking_for' => $currentUser->looking_for ?? '',
                'relationship_goals' => $currentUser->relationship_goals ?? '',
            ];
            $currentProfile = [
                'age' => (int) ($currentUser->age ?? 0),
                'location' => $currentUser->location ?? '',
            ];

            // Fetch all other users
            $otherUsers = User::where('id', '!=', $currentUserId)->get();

            $matches = [];
            foreach ($otherUsers as $otherUser) {
                $otherAnswers = UserAnswer::where('user_id', $otherUser->id)
                    ->with('question')
                    ->get()
                    ->keyBy('question.key');

                if ($otherAnswers->isEmpty()) continue;

                // Fetch images from UserImage
                $images = UserImage::where('user_id', $otherUser->id)
                    ->pluck('image_path')
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
                ];

                // Calculate scores
                $questionScore = $this->calculateQuestionScore($currentAnswers, $otherAnswers);
                $interestsScore = $this->calculateInterestsScore($currentInterests, $otherInterests);
                $preferencesScore = $this->calculatePreferencesScore($currentPreferences, $otherPreferences);
                $profileScore = $this->calculateProfileScore($currentProfile, $otherProfile);

                $totalScore = (
                    $questionScore * 0.5 +
                    $interestsScore * 0.2 +
                    $preferencesScore * 0.2 +
                    $profileScore * 0.1
                );

                $matches[] = [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'age' => $otherUser->age ?? 'N/A',
                    'location' => $otherUser->location ?? 'N/A',
                    'bio' => $otherUser->bio ?? '',
                    'images' => $images ?: ['/placeholder.svg'],
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

    private function calculateQuestionScore($current, $other)
    {
        $total = 0;
        $count = 0;
        foreach ($current as $key => $currAnswer) {
            if (isset($other[$key])) {
                $question = $currAnswer->question;
                $currVal = $currAnswer->answer;
                $otherVal = $other[$key]->answer;

                switch ($question->type) {
                    case 'text':
                    case 'select':
                        $score = ($currVal === $otherVal) ? 100 : 0;
                        break;
                    case 'multiselect':
                        $currArr = json_decode($currVal, true) ?? [];
                        $otherArr = json_decode($otherVal, true) ?? [];
                        $common = count(array_intersect($currArr, $otherArr));
                        $totalOptions = max(count($currArr), count($otherArr));
                        $score = ($totalOptions > 0) ? ($common / $totalOptions) * 100 : 0;
                        break;
                    case 'scale':
                        $diff = abs((int)$currVal - (int)$otherVal);
                        $maxDiff = 9; // 1-10 scale
                        $score = (1 - $diff / $maxDiff) * 100;
                        break;
                    case 'boolean':
                        $score = ((bool)$currVal === (bool)$otherVal) ? 100 : 0;
                        break;
                    default:
                        $score = 0;
                }
                $total += $score;
                $count++;
                // Debug log to check scores
                Log::debug("QuestionScore for $key: $score (curr: $currVal, other: $otherVal)");
            }
        }
        $avgScore = ($count > 0) ? $total / $count : 0;
        Log::debug("Total QuestionScore: $avgScore (count: $count)");
        return $avgScore;
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
        $avgScore = ($count > 0) ? $score / $count : 0;
        Log::debug("ProfileScore: $avgScore (ageDiff: " . ($curr['age'] - $other['age'] ?? 'N/A') . ", locationMatch: " . ($curr['location'] === $other['location'] ? 'yes' : 'no') . ")");
        return $avgScore;
    }
}