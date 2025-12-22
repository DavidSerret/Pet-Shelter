
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Pet;

// Additional seeders
use Database\Seeders\QuizQuestionsSeeder;
use Database\Seeders\AdoptionRequestSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $this->seedUsers();

        $this->seedPetsFromJsonOrSamples();

        $this->call([
            QuizQuestionsSeeder::class,
            AdoptionRequestSeeder::class,
            // more
        ]);

        $this->command->info('✅ Database seeded successfully!');
    }

    /**
     * Siembra usuarios de forma idempotente (updateOrCreate).
     */
    private function seedUsers(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@happinest.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'john@happinest.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );

        User::updateOrCreate(
            ['email' => 'jane@happinest.com'],
            [
                'name' => 'Jane Smith',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );

        $this->command->info('👤 Users seeded (idempotent).');
    }

    private function seedPetsFromJsonOrSamples(): void
    {
        $jsonPath = public_path('assets/animals.json');

        if (File::exists($jsonPath)) {
            $content = File::get($jsonPath);

            try {
                $animals = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $this->command->warn('⚠️ Invalid animals.json: ' . $e->getMessage());
                $animals = [];
            }

            $count = 0;
            foreach ($animals as $animal) {
                $name        = $animal['name']     ?? null;
                $species     = $animal['species']  ?? null;
                $age         = $animal['age']      ?? null;
                $sex         = $animal['sex']      ?? null;
                $imageUrl    = $animal['imageUrl'] ?? null;
                $description = $animal['description'] ?? null;

                if (!$name || !$species) {
                    continue;
                }

                Pet::updateOrCreate(
                    ['name' => $name, 'species' => $species],
                    [
                        'age'        => $age,
                        'sex'        => $sex,
                        'image_url'  => $imageUrl,
                        'description'=> $description,
                        'status'     => 'available',
                    ]
                );

                $count++;
            }

            $this->command->info("🐾 Pets imported from JSON successfully! ({$count} records)");
        } else {
            $this->command->warn('⚠️ Animals JSON file not found. Creating sample pets...');

            Pet::updateOrCreate(
                ['name' => 'Max', 'species' => 'Dog'],
                [
                    'age' => 3,
                    'sex' => 'Male',
                    'description' => 'Friendly and energetic dog looking for a loving home.',
                    'status' => 'available',
                ]
            );

            Pet::updateOrCreate(
                ['name' => 'Whiskers', 'species' => 'Cat'],
                [
                    'age' => 2,
                    'sex' => 'Female',
                    'description' => 'Calm and affectionate cat who loves to cuddle.',
                    'status' => 'available',
                ]
            );

            $this->command->info('🐾 Sample pets created.');
        }
    }
}
