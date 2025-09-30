<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserImage;
use App\Models\UserVideo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAndAnswerSeeder extends Seeder
{
    public function run(): void
    {
        // Fake users with profile details from user table and public profile_type
        $users = [
            [
                'name' => 'Test User 1',
                'email' => 'user1@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'profile_type' => 'public',
                'bio' => 'Love adventures and books!',
                'age' => 25,
                'location' => 'New York',
                'occupation' => 'Software Engineer',
                'education' => 'Bachelor in Computer Science',
                'interests' => json_encode(['Reading', 'Travel', 'Fitness']),
                'looking_for' => 'Long-term relationship',
                'relationship_goals' => 'Marriage and family',
            ],
            [
                'name' => 'Test User 2',
                'email' => 'user2@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'profile_type' => 'public',
                'bio' => 'Bookworm and music lover.',
                'age' => 27,
                'location' => 'New York',
                'occupation' => 'Designer',
                'education' => 'Master in Arts',
                'interests' => json_encode(['Reading', 'Music', 'Fitness']),
                'looking_for' => 'Long-term relationship',
                'relationship_goals' => 'Marriage and family',
            ],
            [
                'name' => 'Test User 3',
                'email' => 'user3@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'profile_type' => 'public',
                'bio' => fake()->sentence(5),
                'age' => rand(20, 40),
                'location' => fake()->city(),
                'occupation' => fake()->jobTitle(),
                'education' => 'Bachelor Degree',
                'interests' => json_encode(fake()->randomElements(['Sports', 'Cooking', 'Art'], 2)),
                'looking_for' => 'Casual dating',
                'relationship_goals' => 'Partnership and growth',
            ],
            [
                'name' => 'Test User 4',
                'email' => 'user4@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'profile_type' => 'public',
                'bio' => fake()->sentence(5),
                'age' => rand(20, 40),
                'location' => fake()->city(),
                'occupation' => fake()->jobTitle(),
                'education' => 'Master Degree',
                'interests' => json_encode(fake()->randomElements(['Technology', 'Photography', 'Dancing'], 3)),
                'looking_for' => 'Companionship',
                'relationship_goals' => 'Life companion',
            ],
            [
                'name' => 'Test User 5',
                'email' => 'user5@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'profile_type' => 'public',
                'bio' => fake()->sentence(5),
                'age' => rand(20, 40),
                'location' => fake()->city(),
                'occupation' => fake()->jobTitle(),
                'education' => 'PhD',
                'interests' => json_encode(fake()->randomElements(['Music', 'Art', 'Fitness'], 2)),
                'looking_for' => 'Marriage',
                'relationship_goals' => 'Spiritual partnership',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create($userData);
            $userId = $user->id;

            // Add fake images (1-3 images per user)
            $imageCount = rand(1, 3);
            for ($i = 0; $i < $imageCount; $i++) {
                UserImage::create([
                    'user_id' => $userId,
                    'image_path' => "/storage/images/user_{$userId}/image{$i}.jpg",
                    'is_primary' => $i === 0,
                    'order' => $i,
                ]);
            }

            // Add fake video (1 video per user for public profile requirement)
            UserVideo::create([
                'user_id' => $userId,
                'video_path' => "/storage/videos/user_{$userId}/video.mp4",
                'original_name' => 'video.mp4',
                'mime_type' => 'video/mp4',
                'size' => rand(5000000, 10000000),
            ]);
        }
    }
}