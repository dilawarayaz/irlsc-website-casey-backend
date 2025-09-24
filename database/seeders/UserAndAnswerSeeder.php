<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserAnswer;
use App\Models\Question;
use App\Models\UserImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAndAnswerSeeder extends Seeder
{
    public function run(): void
    {
        // Pehle questions fetch karo
        $questions = Question::all()->keyBy('key');

        // Fake users with profile details from user table
        $users = [
            [
                'name' => 'Test User 1',
                'email' => 'user1@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
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

        // Specific answers for Test User 1 and Test User 2 to ensure high compatibility
        $specificAnswers = [
            2 => [ // Test User 1 (ID 2)
                'fullName' => 'John Doe',
                'dateOfBirth' => '1998-05-15',
                'gender' => 'Male',
                'occupation' => 'Software Engineer',
                'salaryRange' => '$150k+',
                'workSchedule' => '9-5 weekdays',
                'hobbies' => ['Reading', 'Travel', 'Fitness'],
                'weekendActivities' => 'Hiking and reading',
                'physicalAttraction' => '8',
                'fitnessLevel' => '7',
                'lookingFor' => 'Long-term relationship',
                'preferredAge' => '22-30',
                'dealBreakers' => 'Dishonesty',
                'dealMakers' => 'Kindness',
                'exclusivitySpeed' => 'Moderate (6-10 dates)',
                'coreValues' => ['Honesty', 'Family'],
                'loveLanguage' => 'Quality Time',
                'conflictResolution' => 'Direct discussion',
                'longTermGoals' => 'Build a family',
                'idealRelationship' => 'Supportive partnership',
                'hasChildren' => 'false',
                'wantsChildren' => 'Yes, definitely',
                'datingWithKids' => 'true',
                'religion' => 'None',
                'religionImportance' => '2',
                'smoking' => 'Never',
                'drinking' => 'Socially',
                'pets' => 'Dog',
            ],
            3 => [ // Test User 2 (ID 3, almost identical answers)
                'fullName' => 'Jane Smith',
                'dateOfBirth' => '1996-03-22',
                'gender' => 'Female',
                'occupation' => 'Designer',
                'salaryRange' => '$150k+', // Same as User 1
                'workSchedule' => '9-5 weekdays', // Same
                'hobbies' => ['Reading', 'Travel', 'Fitness'], // Same
                'weekendActivities' => 'Hiking and reading', // Same
                'physicalAttraction' => '8', // Same
                'fitnessLevel' => '7', // Same
                'lookingFor' => 'Long-term relationship', // Same
                'preferredAge' => '22-30', // Same
                'dealBreakers' => 'Dishonesty', // Same
                'dealMakers' => 'Kindness', // Same
                'exclusivitySpeed' => 'Moderate (6-10 dates)', // Same
                'coreValues' => ['Honesty', 'Family'], // Same
                'loveLanguage' => 'Quality Time', // Same
                'conflictResolution' => 'Direct discussion', // Same
                'longTermGoals' => 'Build a family', // Same
                'idealRelationship' => 'Supportive partnership', // Same
                'hasChildren' => 'false', // Same
                'wantsChildren' => 'Yes, definitely', // Same
                'datingWithKids' => 'true', // Same
                'religion' => 'None', // Same
                'religionImportance' => '2', // Same
                'smoking' => 'Never', // Same
                'drinking' => 'Socially', // Same
                'pets' => 'Dog', // Same
            ],
        ];

        foreach ($users as $index => $userData) {
            $user = User::create($userData);
            $userId = $user->id;

            foreach ($questions as $key => $question) {
                $answer = isset($specificAnswers[$userId][$key])
                    ? $specificAnswers[$userId][$key]
                    : $this->generateFakeAnswer($question->type, $question->options);

                UserAnswer::create([
                    'user_id' => $userId,
                    'question_id' => $question->id,
                    'answer' => is_array($answer) ? json_encode($answer) : $answer,
                ]);
            }

            // Add fake image
            UserImage::create([
                'user_id' => $userId,
                'image_path' => '/storage/profile_images/user' . $userId . '.jpg',
                'is_primary' => true,
                'order' => 0,
            ]);
        }
    }

    private function generateFakeAnswer($type, $options)
    {
        switch ($type) {
            case 'text':
                return fake()->sentence(3);
            case 'select':
                return $options ? fake()->randomElement($options) : 'Default';
            case 'multiselect':
                return $options ? fake()->randomElements($options, rand(1, 3)) : [];
            case 'scale':
                return rand(1, 10);
            case 'boolean':
                return fake()->boolean() ? 'true' : 'false';
            default:
                return 'N/A';
        }
    }
}