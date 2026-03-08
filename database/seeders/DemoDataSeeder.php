<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // Create admin
        User::create([
            'name' => 'System Admin',
            'email' => 'olufidipecovenant@gmail.com',
            'password' => Hash::make('Ifeoluwa27'),
            'role' => 'admin',
            'tier' => 'admin',
            'balance' => 0,
        ]);

        // Create demo investor
        User::create([
            'name' => 'Demo Investor',
            'email' => 'demo@investor.com',
            'password' => Hash::make('demo123'),
            'role' => 'investor',
            'tier' => 'elite',
            'balance' => 42850.50,
        ]);

        // Create sample tasks
        Task::create([
            'title' => 'Market Sentiment Survey',
            'description' => 'Share your market outlook for this week',
            'reward' => 5.00,
            'active_date' => today(),
        ]);

        Task::create([
            'title' => 'Verify Secondary Email',
            'description' => 'Add backup email for account security',
            'reward' => 2.50,
            'active_date' => today(),
        ]);

        Task::create([
            'title' => 'Share Referral Link',
            'description' => 'Invite friends to earn bonus rewards',
            'reward' => 10.00,
            'active_date' => today(),
        ]);
    }
}
