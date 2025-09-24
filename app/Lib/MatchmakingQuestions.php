<?php

namespace App\Lib;

class MatchmakingQuestions
{
    public static function getQuestions(): array
    {
        return [
            // Personal Information
            ['id' => 'fullName', 'category' => 'Personal', 'question' => 'Full Name', 'type' => 'text', 'required' => true],
            ['id' => 'dateOfBirth', 'category' => 'Personal', 'question' => 'Date of Birth', 'type' => 'text', 'required' => true],
            [
                'id' => 'gender',
                'category' => 'Personal',
                'question' => 'Gender',
                'type' => 'select',
                'options' => ['Male', 'Female', 'Non-binary', 'Prefer not to say'],
                'required' => true,
            ],
            ['id' => 'occupation', 'category' => 'Personal', 'question' => 'Occupation/Job Title', 'type' => 'text', 'required' => true],
            [
                'id' => 'salaryRange',
                'category' => 'Personal',
                'question' => 'Salary Range (Confidential)',
                'type' => 'select',
                'options' => ['Under $50k', '$50k-$75k', '$75k-$100k', '$100k-$150k', '$150k+'],
                'required' => false,
            ],

            // Lifestyle & Preferences
            [
                'id' => 'workSchedule',
                'category' => 'Lifestyle',
                'question' => 'Work Schedule',
                'type' => 'select',
                'options' => ['9-5 weekdays', 'Evenings', 'Weekends', 'Shift work', 'Flexible'],
                'required' => true,
            ],
            [
                'id' => 'hobbies',
                'category' => 'Lifestyle',
                'question' => 'Hobbies and Interests',
                'type' => 'multiselect',
                'options' => [
                    'Reading', 'Sports', 'Cooking', 'Travel', 'Music',
                    'Art', 'Technology', 'Fitness', 'Photography', 'Dancing',
                ],
                'required' => true,
            ],
            [
                'id' => 'weekendActivities',
                'category' => 'Lifestyle',
                'question' => 'Typical Weekend Activities',
                'type' => 'text',
                'required' => true,
            ],
            [
                'id' => 'physicalAttraction',
                'category' => 'Lifestyle',
                'question' => 'Importance of Physical Attraction (1-10)',
                'type' => 'scale',
                'required' => true,
            ],
            [
                'id' => 'fitnessLevel',
                'category' => 'Lifestyle',
                'question' => 'Physical Fitness Level (1-10)',
                'type' => 'scale',
                'required' => true,
            ],

            // Relationship Goals
            [
                'id' => 'relationshipGoals',
                'category' => 'Relationship',
                'question' => 'What are you looking for in a relationship?',
                'type' => 'select',
                'options' => ['Marriage', 'Long-term partnership', 'Casual dating', 'Companionship'],
                'required' => true,
            ],
            [
                'id' => 'preferredAge',
                'category' => 'Relationship',
                'question' => 'Preferred Partner Age Range',
                'type' => 'text',
                'required' => true,
            ],
            [
                'id' => 'dealBreakers',
                'category' => 'Relationship',
                'question' => 'Deal-breakers in Relationships',
                'type' => 'text',
                'required' => true,
            ],
            [
                'id' => 'dealMakers',
                'category' => 'Relationship',
                'question' => 'Deal-makers in Relationships',
                'type' => 'text',
                'required' => true,
            ],
            [
                'id' => 'exclusivitySpeed',
                'category' => 'Relationship',
                'question' => 'Speed of Exclusivity',
                'type' => 'select',
                'options' => ['Very fast (1-2 dates)', 'Fast (3-5 dates)', 'Moderate (6-10 dates)', 'Slow (10+ dates)'],
                'required' => true,
            ],

            // Values & Compatibility
            [
                'id' => 'coreValues',
                'category' => 'Values',
                'question' => 'Core Values',
                'type' => 'multiselect',
                'options' => ['Honesty', 'Loyalty', 'Family', 'Career', 'Adventure', 'Stability', 'Creativity', 'Spirituality'],
                'required' => true,
            ],
            [
                'id' => 'loveLanguage',
                'category' => 'Values',
                'question' => 'Love Language',
                'type' => 'select',
                'options' => ['Quality Time', 'Physical Touch', 'Words of Affirmation', 'Acts of Service', 'Receiving Gifts'],
                'required' => true,
            ],
            [
                'id' => 'conflictResolution',
                'category' => 'Values',
                'question' => 'Conflict Resolution Style',
                'type' => 'select',
                'options' => ['Direct discussion', 'Need time to process', 'Avoid conflict', 'Seek compromise'],
                'required' => true,
            ],

            // Future Plans
            ['id' => 'longTermGoals', 'category' => 'Future', 'question' => 'Long-term Goals (3-5 years)', 'type' => 'text', 'required' => true],
            [
                'id' => 'idealRelationship',
                'category' => 'Future',
                'question' => 'Ideal Relationship in 3 Years',
                'type' => 'text',
                'required' => true,
            ],

            // Children & Family
            ['id' => 'hasChildren', 'category' => 'Family', 'question' => 'Do you have children?', 'type' => 'boolean', 'required' => true],
            [
                'id' => 'wantsChildren',
                'category' => 'Family',
                'question' => 'Do you want (more) children?',
                'type' => 'select',
                'options' => ['Yes, definitely', 'Maybe', 'No', 'Unsure'],
                'required' => true,
            ],
            [
                'id' => 'datingWithKids',
                'category' => 'Family',
                'question' => 'Would you date someone with children?',
                'type' => 'boolean',
                'required' => true,
            ],

            // Religion & Spirituality
            ['id' => 'religion', 'category' => 'Religion', 'question' => 'Religion or Spiritual Practices', 'type' => 'text', 'required' => false],
            [
                'id' => 'religionImportance',
                'category' => 'Religion',
                'question' => 'Importance of Partner Sharing Religious Views',
                'type' => 'scale',
                'required' => false,
            ],

            // Lifestyle Habits
            [
                'id' => 'smoking',
                'category' => 'Habits',
                'question' => 'Smoking Habits',
                'type' => 'select',
                'options' => ['Never', 'Occasionally', 'Socially', 'Regularly'],
                'required' => true,
            ],
            [
                'id' => 'drinking',
                'category' => 'Habits',
                'question' => 'Drinking Habits',
                'type' => 'select',
                'options' => ['Never', 'Occasionally', 'Socially', 'Regularly'],
                'required' => true,
            ],
            ['id' => 'pets', 'category' => 'Habits', 'question' => 'Do you have pets?', 'type' => 'text', 'required' => false],
        ];
    }
}