<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Course::factory()->create([
            'slug' => 'teknik-sipil-dasar',
            'name' => 'Teknik Sipil Dasar',
            'description' => 'Pelajari dasar-dasar teknik sipil untuk membangun struktur yang kuat dan tahan lama.',
            'price' => 500000,
            'discount_price' => 350000,
            'total_minutes' => 480,
            'category_id' => 1,
            'trailer_url' => 'https://kelasdrafter.com/syllabus/teknik-sipil.pdf',
            'whatsapp_group_url' => 'https://chat.whatsapp.com/ExampleGroup',
            'thumbnail_url' => 'https://www.liberiangeek.net/wp-content/uploads/2019/03/Librecad-Tutorial-Everything-You-Need-to-Know.png',
            'syllabus_url' => 'https://kelasdrafter.com/syllabus/teknik-sipil.pdf',
            'is_can_checkout' => true,
            'is_active' => true,
        ]);
    }
}
