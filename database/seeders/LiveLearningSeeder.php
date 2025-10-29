<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LiveLearning;
use App\Models\LiveLearningRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LiveLearningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('role', 'admin')->first();
        
        if (!$admin) {
            $admin = User::first();
            if (!$admin) {
                echo "⚠️  Warning: No users found. Please create users first.\n";
                return;
            }
        }

        echo "🌱 Seeding Live Learnings...\n";

        // Clear existing data (with foreign key check disabled)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('live_learning_registrations')->truncate();
        DB::table('live_learnings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ========================================
        // 1. LIVE LEARNING: Belajar AI untuk Pemula (PUBLISHED)
        // ========================================
        $liveLearning1 = LiveLearning::create([
            'title' => 'Belajar AI untuk Pemula',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=450&fit=crop',
            'description' => 'Workshop intensif untuk memahami dasar-dasar Artificial Intelligence. Cocok untuk pemula yang ingin memulai karir di bidang AI. Dalam workshop ini, kamu akan belajar konsep fundamental AI, machine learning, dan deep learning dengan pendekatan praktis.',
            'schedule' => '15 November 2025, Pukul 19:00 - 21:00 WIB',
            'materials' => [
                'Pengenalan Artificial Intelligence dan Machine Learning',
                'Memahami algoritma dasar ML (Linear Regression, Decision Tree)',
                'Praktek membuat chatbot sederhana dengan Python',
                'Studi kasus implementasi AI di industri',
                'Sertifikat digital peserta',
            ],
            'is_paid' => false,
            'price' => null,
            'zoom_link' => 'https://zoom.us/j/123456789?pwd=abc123',
            'community_group_link' => 'https://chat.whatsapp.com/AI-Pemula-Community',
            'max_participants' => 100,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        // Registrations untuk Live Learning 1 (5 peserta)
        LiveLearningRegistration::create([
            'live_learning_id' => $liveLearning1->id,
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'whatsapp' => '081234567890',
            'registered_at' => now()->subDays(5),
        ]);

        LiveLearningRegistration::create([
            'live_learning_id' => $liveLearning1->id,
            'name' => 'Siti Nurhaliza',
            'email' => 'siti.nurhaliza@example.com',
            'whatsapp' => '081234567891',
            'registered_at' => now()->subDays(4),
        ]);

        LiveLearningRegistration::create([
            'live_learning_id' => $liveLearning1->id,
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad.fauzi@example.com',
            'whatsapp' => '081234567892',
            'registered_at' => now()->subDays(3),
        ]);

        LiveLearningRegistration::create([
            'live_learning_id' => $liveLearning1->id,
            'name' => 'Rina Wijaya',
            'email' => 'rina.wijaya@example.com',
            'whatsapp' => '081234567893',
            'registered_at' => now()->subDays(2),
        ]);

        LiveLearningRegistration::create([
            'live_learning_id' => $liveLearning1->id,
            'name' => 'Dimas Prasetyo',
            'email' => 'dimas.prasetyo@example.com',
            'whatsapp' => '081234567894',
            'registered_at' => now()->subDays(1),
        ]);

        echo "✅ Created: {$liveLearning1->title} with 5 registrations\n";

        // ========================================
        // 2. LIVE LEARNING: Web Development with Laravel (DRAFT)
        // ========================================
        $liveLearning2 = LiveLearning::create([
            'title' => 'Web Development dengan Laravel 10',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800&h=450&fit=crop',
            'description' => 'Pelajari framework Laravel 10 untuk membangun aplikasi web modern yang scalable dan secure. Workshop ini cocok untuk developer yang sudah familiar dengan PHP dan ingin menguasai Laravel.',
            'schedule' => '1 Desember 2025, Pukul 15:00 - 18:00 WIB',
            'materials' => [
                'Setup environment Laravel 10',
                'MVC architecture dan routing',
                'Eloquent ORM dan database migration',
                'Authentication dengan Laravel Sanctum',
                'RESTful API development',
                'Source code project lengkap',
            ],
            'is_paid' => false,
            'price' => null,
            'zoom_link' => 'https://zoom.us/j/111222333?pwd=laravel10',
            'community_group_link' => 'https://chat.whatsapp.com/Laravel-Indonesia-Dev',
            'max_participants' => 80,
            'status' => 'draft', // DRAFT - belum dipublish
            'created_by' => $admin->id,
        ]);

        echo "✅ Created: {$liveLearning2->title} (DRAFT - no registrations)\n";

        echo "\n🎉 Seeding completed!\n";
        echo "📊 Summary:\n";
        echo "   - Total Live Learnings: 2\n";
        echo "   - Published: 1\n";
        echo "   - Draft: 1\n";
        echo "   - Total Registrations: 5\n";
    }
}