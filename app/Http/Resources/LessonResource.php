<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Enrollment;
use App\Models\ScheduleChapter;
use Carbon\Carbon;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $userIsLoggedIn = auth()->check();
        $user = auth()->user();

        // Menentukan apakah supporting_file_url dan video_url dapat ditampilkan
        $showUrls = true;
        $is_schedule_on = true;

        if (!$this->is_public) {

            if ($userIsLoggedIn) {
                $courseId = $this->chapter->course->id;
                $enrollment = Enrollment::where('user_id', $user->id)
                    ->where('course_id', $courseId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($enrollment) {

                    // apakah dia kelas bebas? (tidak terikat batch)

                    if ($enrollment->batch_id == null) {
                        $showUrls = true;
                        $is_schedule_on = true;
                    } else {



                        // Periksa apakah tanggal batch belum mulai



                        if (Carbon::parse($enrollment->batch->start_date) > now()) {
                            // Batch belum mulai
                            $showUrls = false;
                            $is_schedule_on = false;
                        } else {
                            // Batch sudah mulai, periksa schedule chapter
                            $scheduleChapter = ScheduleChapter::where('chapter_id', $this->chapter_id)
                                ->where('batch_id', $enrollment->batch_id)
                                ->first();

                            if ($scheduleChapter) {
                                // Periksa apakah schedule chapter belum mulai
                                if (Carbon::parse($scheduleChapter->started_at) > now()) {
                                    // Schedule chapter belum mulai
                                    $showUrls = false;
                                    $is_schedule_on = false;
                                } else {
                                    // Schedule chapter sudah mulai
                                    $showUrls = true;
                                    $is_schedule_on = true;
                                }
                            } else {
                                // Tidak ada schedule chapter, anggap true
                                $showUrls = true;
                                $is_schedule_on = true;
                            }
                        }
                    }
                } else {
                    // Tidak ada enrollment
                    $showUrls = false;
                    $is_schedule_on = false;
                }
            } else {
                // User tidak login
                $showUrls = false;
                $is_schedule_on = false;
            }
        }

        return [
            'id' => $this->id,
            'chapter_id' => $this->chapter_id,
            'name' => $this->name,
            'sequence' => $this->sequence,
            'embed_url' => $showUrls ? $this->embed_url : null,
            'video_url' => $showUrls ? $this->video_url : null,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'supporting_file_url' => $showUrls ? $this->supporting_file_url : null,
            'is_schedule_on' => (bool) $is_schedule_on,
            'is_public' => (bool) $this->is_public,
            'is_active' => (bool) $this->is_active,
            'require_completion' => (int) $this->require_completion, // ← BARIS BARU INI
            'is_done' => (bool) $userIsLoggedIn ? $this->isDoneByUser($user->id) : false,
            'is_markable' => (bool) $userIsLoggedIn ? $this->isMarkableByUser($user->id) : false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}