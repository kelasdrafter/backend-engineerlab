<?php

namespace App\Http\Controllers;

use App\Models\ScheduleChapter;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Http\Requests\StoreScheduleChapterRequest;
use App\Http\Requests\UpdateScheduleChapterRequest;
use App\Models\Chapter;
use Illuminate\Http\Request;

class ScheduleChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $allowedColumns = (new ScheduleChapter)->getFillable();
            $data = QueryBuilder::for(ScheduleChapter::class)
                ->allowedFilters([...$allowedColumns])
                ->allowedSorts([...$allowedColumns, 'created_at', 'updated_at'])
                ->paginate();

            return $this->responseSuccess('Get Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function getChapterSchedule(Request $request): JsonResponse
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'course_id' => 'required'
        ]);

        $batchId = $request->query('batch_id');
        $courseId = $request->query('course_id');

        try {
            $chapters = Chapter::when($courseId, function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })->get();

            $data = $chapters->map(function ($chapter) use ($batchId) {
                $scheduleChapter = $chapter->scheduleChapters()->where('batch_id', $batchId)->with('batch')->first();
                return [
                    'chapter' => [
                        'name' => $chapter->name,
                        'id' => $chapter->id,
                    ],
                    'batch' => optional($scheduleChapter)?->batch?->only(['id', 'name', 'start_date']) ?? null,
                    'started_at' => $scheduleChapter ? $scheduleChapter->started_at : null
                ];
            });



            return $this->responseSuccess('Get Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScheduleChapterRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = ScheduleChapter::updateOrCreate(
                [
                    'chapter_id' => $request->chapter_id,
                    'batch_id' => $request->batch_id,
                ],
                [
                    'started_at' => $request->started_at,
                ]
            );

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ScheduleChapter $scheduleChapter)
    {
        return $this->responseSuccess('Get Data Succcessfully', $scheduleChapter, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScheduleChapterRequest $request, ScheduleChapter $scheduleChapter)
    {
        $scheduleChapter->fill($this->generateData($request));
        $scheduleChapter->save();

        return $this->responseSuccess('Update Data Succcessfully', $scheduleChapter, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScheduleChapter $scheduleChapter)
    {
        $scheduleChapter->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $scheduleChapter, 200);
    }

    public function generateData($request)
    {
        return [
            'chapter_id' => $request->chapter_id,
            'batch_id' => $request->batch_id,
            'started_at' => $request->started_at,
        ];
    }
}
