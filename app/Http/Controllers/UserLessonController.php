<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserLessonRequest;
use App\Http\Requests\UpdateUserLessonRequest;
use App\Models\UserLesson;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Validator;

class UserLessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new UserLesson)->getFillable();
            $data = QueryBuilder::for(UserLesson::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active'), AllowedFilter::exact('user_id'), AllowedFilter::exact('lesson_id'), AllowedFilter::exact('course_id')])
                ->allowedSorts([...$allowedColumns, 'created_at', 'updated_at'])
                ->paginate($perPage);

            return $this->responseSuccess('Get Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserLessonRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = UserLesson::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserLesson $userLesson)
    {
        return $this->responseSuccess('Get Data Succcessfully', $userLesson, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserLessonRequest $request, UserLesson $userLesson)
    {
        $userLesson->fill($this->generateData($request));
        $userLesson->save();

        return $this->responseSuccess('Update Data Succcessfully', $userLesson, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserLesson $userLesson)
    {
        $userLesson->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $userLesson, 200);
    }

    public function markAsDone(Request $request)
    {
        // Validasi request
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        if ($validator->fails()) {
            return $this->responseError("lesson_id not valid", [], 400);
        }

        $userId = auth()->id();
        $lessonId = $request->lesson_id;
        $courseId = $request->course_id;

        $userLesson = UserLesson::firstOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId, 'course_id' => $courseId],
            ['is_done' => true]
        );

        // If the entry already exists but is not marked as done, update it
        if (!$userLesson->wasRecentlyCreated && !$userLesson->is_done) {
            $userLesson->update(['is_done' => true]);
        }

        return $this->responseSuccess('Lesson marked as done', [], 200);
    }

    public function markable(Request $request)
    {
        // Validasi request
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        if ($validator->fails()) {
            return $this->responseError("lesson_id not valid", [], 400);
        }

        $userId = auth()->id();
        $lessonId = $request->lesson_id;
        $courseId = $request->course_id;

        $userLesson = UserLesson::firstOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId, 'course_id' => $courseId],
            ['is_markable' => true]
        );

        // If the entry already exists but is not marked as done, update it
        if (!$userLesson->wasRecentlyCreated && !$userLesson->is_markable) {
            $userLesson->update(['is_markable' => true]);
        }

        return $this->responseSuccess('Lesson can be marked as done', [], 200);
    }

    public function generateData($request)
    {
        return [
            'user_id' => $request->user_id,
            'lesson_id' => $request->lesson_id,
            'course_id' => $request->course_id,
            "is_markable" => $request->is_markable,
            'is_done' => $request->is_done,
            
        ];
    }
}
