<?php

namespace App\Http\Resources;

use App\Models\Lesson;
use App\Models\UserLesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $courseId = $this->course->id;
        $userId = $this->user?->id ?? 0;

        // Hitung jumlah lesson pada course
        $totalLessons = Lesson::whereHas('chapter', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->count();

        // Hitung jumlah lesson yang telah diselesaikan oleh user
        $completedLessons = UserLesson::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('is_done', true)
            ->count();

        // Periksa apakah ada lesson yang pending
        $pendingLesson = UserLesson::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('is_done', false)
            ->exists();

        $is_eligible = true;
        if ($pendingLesson || $totalLessons !== $completedLessons) {
            $is_eligible = false;
        }

        return [
            'id' => $this->id,
            'course' => [
                'id' => $this->course->id,
                'name' => $this->course->name,
                'description' => $this->course->description,
                'short_description' => $this->course->short_description,
                'thumbnail_url' => $this->course->thumbnail_url,
            ],
            'is_finish' => (bool) $is_eligible,
            'user_id' => $this->user_id,
            'batch_id' => $this->batch_id,
            'transaction_id' => $this->transaction_id,
            'user' => $this->user ? [
                "id" => $this->user->id,
                "name" => $this->user->name,
                "email" => $this->user->email,
                "phone" => $this->user->phone,
                "city" => $this->user->city,
                "birthdate" => $this->user->birthdate,
            ] : null,
            'batch' => $this->batch ? [
                "id" => $this->batch->id,
                "name" => $this->batch->name,
                "start_date" => $this->batch->start_date,
                "whatsapp_group_url" => $this->batch->whatsapp_group_url,
            ] : null,
            'transaction' => $this->transaction ? [
                'id' => $this->transaction->id,
                'voucher_code' => $this->transaction->voucher_code,
                'amount' => $this->transaction->amount,
            ] : null,
            'expired_at' => $this->expired_at,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
