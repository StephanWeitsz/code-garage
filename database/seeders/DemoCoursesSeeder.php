<?php

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\CourseSection;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;

class DemoCoursesSeeder extends Seeder
{
    public function run(): void
    {
        $lecturer = User::where('email', 'lecturer@studentportal.test')->first();

        if ($lecturer === null) {
            return;
        }

        $courses = [
            [
                'title' => 'Coding Basics Boot Sequence',
                'description' => 'Start from variables, flow control, and core programming habits through a robotics-inspired beginner path.',
                'difficulty_level' => DifficultyLevel::Beginner->value,
                'category' => 'Programming Basics',
                'status' => CourseStatus::Published->value,
                'published_at' => now(),
                'sections' => [
                    [
                        'title' => 'Computational Thinking',
                        'description' => 'Build the problem-solving habits that sit underneath every programming language.',
                        'lessons' => [
                            ['title' => 'Think Like a Machine', 'content_type' => 'text', 'content' => 'Programming starts with instructions, state, and predictable outcomes.'],
                            ['title' => 'Variables and Input Signals', 'content_type' => 'code', 'content' => "name = 'Code Garage'\nprint(name)"],
                        ],
                    ],
                    [
                        'title' => 'Visual Programming',
                        'description' => 'Use visual patterns to understand repetition, branching, and logic flow before moving deeper into code.',
                        'lessons' => [
                            ['title' => 'Loops for Repeating Tasks', 'content_type' => 'video', 'content' => 'https://example.com/videos/loops-intro'],
                            ['title' => 'From Blocks to Logic', 'content_type' => 'text', 'content' => 'Visual programming helps students see conditions, sequences, and loops before syntax gets in the way.'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Web Logic for Young Engineers',
                'description' => 'Learn how web requests, views, and backend rules fit together in a structured learning environment.',
                'difficulty_level' => DifficultyLevel::Beginner->value,
                'category' => 'Web Development',
                'status' => CourseStatus::Published->value,
                'published_at' => now(),
                'sections' => [
                    [
                        'title' => 'How Web Apps Respond',
                        'description' => 'Understand the path from browser action to rendered page.',
                        'lessons' => [
                            ['title' => 'How the Browser Talks to Laravel', 'content_type' => 'text', 'content' => 'Requests, routes, controllers, and views all play a role in the response cycle.'],
                        ],
                    ],
                    [
                        'title' => 'Blade Components',
                        'description' => 'Break interfaces into reusable pieces that stay readable as the app grows.',
                        'lessons' => [
                            ['title' => 'Blade Components in Practice', 'content_type' => 'code', 'content' => "<x-card>\n    Hello student\n</x-card>"],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($courses as $courseData) {
            $sections = $courseData['sections'];
            unset($courseData['sections']);

            $course = Course::updateOrCreate(
                ['slug' => Str::slug($courseData['title'])],
                array_merge($courseData, ['lecturer_id' => $lecturer->id]),
            );

            Lesson::where('course_id', $course->id)->delete();
            CourseSection::where('course_id', $course->id)->delete();

            foreach ($sections as $sectionIndex => $sectionData) {
                $lessons = $sectionData['lessons'];
                unset($sectionData['lessons']);

                $section = CourseSection::create(array_merge($sectionData, [
                    'course_id' => $course->id,
                    'slug' => Str::slug($sectionData['title']),
                    'sequence' => $sectionIndex + 1,
                ]));

                foreach ($lessons as $lessonIndex => $lessonData) {
                    Lesson::create(array_merge($lessonData, [
                        'course_id' => $course->id,
                        'course_section_id' => $section->id,
                        'slug' => Str::slug($lessonData['title']),
                        'sequence' => $lessonIndex + 1,
                        'is_preview' => $sectionIndex === 0 && $lessonIndex === 0,
                    ]));
                }
            }
        }
    }
}
