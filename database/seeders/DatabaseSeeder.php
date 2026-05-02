<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Child;
use App\Models\BehaviorLog;
use App\Models\Recommendation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo Parent
        $parent = User::firstOrCreate(['email' => 'parent@example.com'], [
            'name'     => 'Sarah Johnson',
            'password' => Hash::make('password'),
            'role'     => 'parent',
        ]);

        // Demo Teacher
        $teacher = User::firstOrCreate(['email' => 'teacher@example.com'], [
            'name'     => 'Mr. David Lee',
            'password' => Hash::make('password'),
            'role'     => 'teacher',
        ]);

        // Demo Psychologist
        $psychologist = User::firstOrCreate(['email' => 'psychologist@example.com'], [
            'name'     => 'Dr. Maria Garcia',
            'password' => Hash::make('password'),
            'role'     => 'psychologist',
        ]);

        // Demo Children
        $emma = Child::firstOrCreate(['name' => 'Emma', 'parent_id' => $parent->id], [
            'age'              => 7,
            'psychologist_id'  => $psychologist->id,
            'notes'            => 'Has difficulty focusing in loud environments.',
        ]);

        $lucas = Child::firstOrCreate(['name' => 'Lucas', 'parent_id' => $parent->id], [
            'age'              => 10,
            'psychologist_id'  => $psychologist->id,
            'notes'            => 'Very social, sometimes anxious about exams.',
        ]);

        // Generate 30 days of behavior logs for Emma
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();

            BehaviorLog::firstOrCreate(['child_id' => $emma->id, 'log_date' => $date], [
                'user_id'            => $teacher->id,
                'focus_level'        => rand(2, 5),
                'mood'               => collect(['happy', 'neutral', 'sad', 'anxious'])->random(),
                'sleep_hours'        => rand(60, 90) / 10,
                'social_interaction' => rand(2, 5),
                'note'               => 'Daily observation log.',
            ]);
        }

        // Generate 30 days of behavior logs for Lucas
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();

            BehaviorLog::firstOrCreate(['child_id' => $lucas->id, 'log_date' => $date], [
                'user_id'            => $teacher->id,
                'focus_level'        => rand(3, 5),
                'mood'               => collect(['happy', 'neutral', 'anxious'])->random(),
                'sleep_hours'        => rand(70, 95) / 10,
                'social_interaction' => rand(3, 5),
                'note'               => 'Daily observation log.',
            ]);
        }

        // Recommendations for Emma
        Recommendation::firstOrCreate(['child_id' => $emma->id, 'title' => 'Morning Mindfulness'], [
            'description'  => 'Practice 5 minutes of breathing exercises before school to improve focus.',
            'category'     => 'focus',
            'is_completed' => false,
        ]);

        Recommendation::firstOrCreate(['child_id' => $emma->id, 'title' => 'Sleep Schedule'], [
            'description'  => 'Establish a consistent bedtime of 8:30 PM to ensure 9 hours of sleep.',
            'category'     => 'sleep',
            'is_completed' => false,
        ]);

        Recommendation::firstOrCreate(['child_id' => $emma->id, 'title' => 'Quiet Study Corner'], [
            'description'  => 'Create a designated quiet study space away from distractions.',
            'category'     => 'focus',
            'is_completed' => true,
        ]);

        echo "✅ Demo data seeded successfully!\n";
        echo "   Parent:       parent@example.com / password\n";
        echo "   Teacher:      teacher@example.com / password\n";
        echo "   Psychologist: psychologist@example.com / password\n";
    }
}
