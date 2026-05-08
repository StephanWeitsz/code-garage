<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Ava Admin',
                'email' => 'admin@studentportal.test',
                'mobile' => '+27000000001',
                'password' => 'password',
                'status' => UserStatus::Active->value,
                'lecturer_headline' => null,
                'lecturer_specialties' => null,
                'lecturer_bio' => null,
                'is_featured_lecturer' => false,
                'role' => 'admin',
            ],
            [
                'name' => 'Leo Lecturer',
                'email' => 'lecturer@studentportal.test',
                'mobile' => '+27000000002',
                'password' => 'password',
                'status' => UserStatus::Active->value,
                'lecturer_headline' => 'Software lecturer focused on Python, robotics, and beginner-friendly problem solving.',
                'lecturer_specialties' => 'Python, Robotics, AI Fundamentals, Web Development',
                'lecturer_bio' => 'Leo teaches students how to move from coding basics into real projects. His sessions focus on practical exercises, clear explanations, and building confidence through structured learning.',
                'is_featured_lecturer' => true,
                'role' => 'lecturer',
            ],
            [
                'name' => 'Sam Student',
                'email' => 'student@studentportal.test',
                'mobile' => '+27000000003',
                'password' => 'password',
                'status' => UserStatus::Active->value,
                'lecturer_headline' => null,
                'lecturer_specialties' => null,
                'lecturer_bio' => null,
                'is_featured_lecturer' => false,
                'role' => 'student',
            ],
        ];

        foreach ($users as $attributes) {
            $role = $attributes['role'];
            unset($attributes['role']);

            $user = User::updateOrCreate(
                ['email' => $attributes['email']],
                $attributes,
            );

            $user->syncRoles([$role]);
        }
    }
}
