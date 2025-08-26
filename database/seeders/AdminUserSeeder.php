<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Radcheck;
use App\Services\SshaHashService;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = 'atendimento@recifetecnologia.com.br';

        // Check if the admin user already exists to avoid duplicates
        $existingAdmin = Radcheck::where('email', $adminEmail)->first();

        if (!$existingAdmin) {
            Radcheck::create([
                'username' => 'admin',
                'attribute' => 'SSHA-Password',
                'op' => ':=',
                'value' => SshaHashService::hash(Str::random(16)), // Secure random initial password
                'email' => $adminEmail,
                'is_admin' => true,
            ]);
        }
    }
}
