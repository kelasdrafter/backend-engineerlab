<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Enrollment;
use App\Models\Batch;

class DetailCourseResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		$userId = auth()->id();
		$lastLessonId = null;
		
		// Cek apakah user terdaftar pada kursus ini
		$isRegistered = false;
		$isCanAccess = false;
		if ($userId) {
			$lastLesson = $this->lessons()
				->join('user_lessons', 'lessons.id', '=', 'user_lessons.lesson_id')
				->where('user_lessons.user_id', $userId)
				->orderBy('user_lessons.updated_at', 'desc')
				->first(['lessons.*', 'user_lessons.updated_at as last_updated']);
			
			$lastLessonId = $lastLesson ? $lastLesson->id : null;
			
			// Menambahkan logika untuk mengecek keberadaan di enrollments
			$enrollment = Enrollment::where('user_id', $userId)
				->where('course_id', $this->id)
				->first();
			
			if ($enrollment) {
                if ($this->is_direct_class) {
                    $isCanAccess = true;
                } else {
                    $batch = Batch::where('id', $enrollment->batch_id)
                    ->where('start_date', '<=', now())
                    ->first();
                
                    $isCanAccess = $batch ? true : false;
                }
                $isRegistered = true;
			}
		}

		return [
			'id' => $this->id,
			'name' => $this->name,
			'slug' => $this->slug,
			'next_batch' => $this->next_batch,
			'description' => $this->description,
			'short_description' => $this->short_description,
			'price' => $this->price,
			'discount_price' => $this->discount_price,
			'whatsapp_group_url' => $this->whatsapp_group_url,
			'trailer_url' => $this->trailer_url,
			'thumbnail_url' => $this->thumbnail_url,
			'syllabus_url' => $this->syllabus_url,
			'total_minutes' => $this->total_minutes,
			'total_chapters' => $this->chapters_count,
			'total_lessons' => $this->lessons_count,
			'is_can_checkout'  => (bool) $this->is_can_checkout,
			'is_active' => (bool) $this->is_active,
			'is_registered' => (bool) $isRegistered,
			'is_can_access' => (bool) $isCanAccess,
			'is_direct_class' => (bool) $this->is_direct_class,
			'last_lesson_id' => $lastLessonId,
			'designedFors' => $this->designedFors->where('is_active', true)->map(function ($designedFor) {
				return [
					'id' => $designedFor->id, 
					'description' => $designedFor->text
				];
			})->values(),
			'goals' => $this->goals->where('is_active', true)->map(function ($goal) {
				return [
					'id' => $goal->id, 
					'image_url' => $goal->image_url
				];
			})->values(),
			'keyPoints' => $this->keyPoints->where('is_active', true)->map(function ($keyPoint) {
				return [
					'id' => $keyPoint->id, 
					'description' => $keyPoint->text
				];
			})->values(),
			'benefits' => [
				'text' => $this->benefit, 
				'data' => $this->benefits->where('is_active', true)->map(function ($benefit) {
					return [
						'id' => $benefit->id, 
						'description' => $benefit->text
					];
				})->values()
			],
			'privileges' => [
				'text' => $this->privilege, 
				'data' => $this->privileges->where('is_active', true)->map(function ($privilege) {
					return [
						'id' => $privilege->id, 
						'description' => $privilege->text
					];
				})->values()
			],
			'category' => [
				'id' => $this->category->id,
				'name' => $this->category->name,
				'value' => $this->category->value,
				'group' => $this->category->group,
				'is_active' => (bool) $this->category->is_active,
			],
		];
	}
}
