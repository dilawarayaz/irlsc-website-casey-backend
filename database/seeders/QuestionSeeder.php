<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            ['key' => 'fullName', 'category' => 'Personal', 'question' => 'Full Name', 'type' => 'text', 'required' => true, 'options' => null],
            ['key' => 'dateOfBirth', 'category' => 'Personal', 'question' => 'Date of Birth', 'type' => 'text', 'required' => true, 'options' => null], // Validated as date in request
            ['key' => 'gender', 'category' => 'Personal', 'question' => 'Gender', 'type' => 'select', 'required' => true, 'options' => ['Male', 'Female', 'Non-binary', 'Prefer not to say']],
            ['key' => 'occupation', 'category' => 'Personal', 'question' => 'Occupation/Job Title', 'type' => 'text', 'required' => true, 'options' => null],
            ['key' => 'salaryRange', 'category' => 'Personal', 'question' => 'Salary Range (Confidential)', 'type' => 'select', 'required' => false, 'options' => ['Under $50k', '$50k-$75k', '$75k-$100k', '$100k-$150k', '$150k+']],
            ['key' => 'workSchedule', 'category' => 'Lifestyle', 'question' => 'Work Schedule', 'type' => 'select', 'required' => true, 'options' => ['9-5 weekdays', 'Evenings', 'Weekends', 'Shift work', 'Flexible']],
            ['key' => 'hobbies', 'category' => 'Lifestyle', 'question' => 'Hobbies and Interests', 'type' => 'multiselect', 'required' => true, 'options' => ['Reading', 'Sports', 'Cooking', 'Travel', 'Music', 'Art', 'Technology', 'Fitness', 'Photography', 'Dancing']],
            ['key' => 'weekendActivities', 'category' => 'Lifestyle', 'question' => 'Typical Weekend Activities', 'type' => 'text', 'required' => true, 'options' => null],
            ['key' => 'physicalAttraction', 'category' => 'Lifestyle', 'question' => 'Importance of Physical Attraction (1-10)', 'type' => 'scale', 'required' => true, 'options' => null],
            ['key' => 'fitnessLevel', 'category' => 'Lifestyle', 'question' => 'Physical Fitness Level (1-10)', 'type' => 'scale', 'required' => true, 'options' => null],
            ['key' => 'relationshipGoals', 'category' => 'Relationship', 'question' => 'What are you looking for in a relationship?', 'type' => 'select', 'required' => true, 'options' => ['Marriage', 'Long-term partnership', 'Casual dating', 'Companionship']],
            ['key' => 'preferredAge', 'category' => 'Relationship', 'question' => 'Preferred Partner Age Range', 'type' => 'text', 'required' => true, 'options' => null],
            ['key' => 'dealBreakers', 'category' => 'Relationship', 'question' => 'Deal-breakers in Relationships', 'type' => 'text', 'required' => true, 'options' => null],
            ['key' => 'dealMakers', 'category' => 'Relationship', 'question' => 'Deal-makers in Relationships', 'type' => 'text', 'required' => true, 'options' => null],
            ['key' => 'exclusivitySpeed', 'category' => 'Relationship', 'question' => 'Speed of Exclusivity', 'type' => 'select', 'required' => true, 'options' => ['Very fast (1-2 dates)', 'Fast (3-5 dates)', 'Moderate (6-10 dates)', 'Slow (10+ dates)']],
            ['key' => 'coreValues', 'category' => 'Values', 'question' => 'Core Values', 'type' => 'multiselect', 'required' => true, 'options' => ['Honesty', 'Loyalty', 'Family', 'Career', 'Adventure', 'Stability', 'Creativity', 'Spirituality']],
            ['key' => 'loveLanguage', 'category' => 'Values', 'question' => 'Love Language', 'type' => 'select', 'required' => true, 'options' => ['Quality Time', 'Physical Touch', 'Words of Affirmation', 'Acts of Service', 'Receiving Gifts']],
            ['key' => 'conflictResolution', 'category' => 'Values', 'question' => 'Conflict Resolution Style', 'type' => 'select', 'required' => true, 'options' => ['Direct discussion', 'Need time to process', 'Avoid conflict', 'Seek compromise']],
            ['key' => 'longTermGoals', 'category' => 'Future', 'question' => 'Long-term Goals (3-5 years)', 'type' => 'text', 'required' => true, 'options' => null],
            ['key' => 'idealRelationship', 'category' => 'Future', 'question' => 'Ideal Relationship in 3 Years', 'type' => 'text', 'required' => true, 'options' => null],
            ['key' => 'hasChildren', 'category' => 'Family', 'question' => 'Do you have children?', 'type' => 'boolean', 'required' => true, 'options' => null],
            ['key' => 'wantsChildren', 'category' => 'Family', 'question' => 'Do you want (more) children?', 'type' => 'select', 'required' => true, 'options' => ['Yes, definitely', 'Maybe', 'No', 'Unsure']],
            ['key' => 'datingWithKids', 'category' => 'Family', 'question' => 'Would you date someone with children?', 'type' => 'boolean', 'required' => true, 'options' => null],
            ['key' => 'religion', 'category' => 'Religion', 'question' => 'Religion or Spiritual Practices', 'type' => 'text', 'required' => false, 'options' => null],
            ['key' => 'religionImportance', 'category' => 'Religion', 'question' => 'Importance of Partner Sharing Religious Views', 'type' => 'scale', 'required' => false, 'options' => null],
            ['key' => 'smoking', 'category' => 'Habits', 'question' => 'Smoking Habits', 'type' => 'select', 'required' => true, 'options' => ['Never', 'Occasionally', 'Socially', 'Regularly']],
            ['key' => 'drinking', 'category' => 'Habits', 'question' => 'Drinking Habits', 'type' => 'select', 'required' => true, 'options' => ['Never', 'Occasionally', 'Socially', 'Regularly']],
            ['key' => 'pets', 'category' => 'Habits', 'question' => 'Do you have pets?', 'type' => 'text', 'required' => false, 'options' => null],
        ];

        foreach ($questions as $q) {
            Question::create($q);
        }
    }
}