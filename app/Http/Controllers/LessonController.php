<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Models\File;
use App\Models\Lesson;
use App\Http\Resources\LessonResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new Lesson)->getFillable();
            $data = QueryBuilder::for(Lesson::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active'), AllowedFilter::exact('chapter_id')])
                ->allowedSorts([...$allowedColumns, 'created_at', 'updated_at'])
                ->paginate($perPage);

            // return $this->responseSuccess('Get Data Successfully', LessonResource::collection($data), 200);
            return $this->responseSuccess(
                'Get Data Successfully',
                [
                    "current_page" => $data->currentPage(),
                    "data" => LessonResource::collection($data),
                    "first_page_url" => $data->url(1),
                    "from" => $data->firstItem(),
                    "last_page" => $data->lastPage(),
                    "last_page_url" => $data->url($data->lastPage()),
                    "links" => $data->linkCollection(),
                    "next_page_url" => $data->nextPageUrl(),
                    "path" => $data->path(),
                    "per_page" => $data->perPage(),
                    "prev_page_url" => $data->previousPageUrl(),
                    "to" => $data->lastItem(),
                    "total" => $data->total()
                ],
                200
            );
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLessonRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Lesson::create($data);

            return $this->responseSuccess('Create Data Successfully', new LessonResource($data), 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        return $this->responseSuccess('Get Data Successfully', new LessonResource($lesson), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLessonRequest $request, Lesson $lesson)
    {
        // Cek apakah supporting_file_url baru dan tidak sama dengan yang lama
        if ($request->has('supporting_file_url') && $request->supporting_file_url != $lesson->supporting_file_url) {
            // Hapus file jika supporting_file_url di perbarui
            $url = $lesson->supporting_file_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {}
        }

        // Cek apakah video_url baru dan tidak sama dengan yang lama
        if ($request->has('video_url') && $request->video_url != $lesson->video_url) {
            // Hapus file jika video_url di perbarui
            $url = $lesson->video_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {}
        }

        // Cek apakah thumbnail_url baru dan tidak sama dengan yang lama
        if ($request->has('thumbnail_url') && $request->thumbnail_url != $lesson->thumbnail_url) {
            // Hapus file jika thumbnail_url di perbarui
            $url = $lesson->thumbnail_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {}
        }

        $lesson->fill($this->generateData($request));
        $lesson->save();

        return $this->responseSuccess('Update Data Succcessfully', $lesson, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        try {
            $url = $lesson->supporting_file_url;

            // Ekstrak key dari URL
            $baseUrl = rtrim(env('AWS_URL'), '/');
            $key = ltrim(str_replace($baseUrl, '', $url), '/');

            File::deleteByPath($key);
        } catch (Exception $exception) {}

        try {
            $url = $lesson->video_url;

            // Ekstrak key dari URL
            $baseUrl = rtrim(env('AWS_URL'), '/');
            $key = ltrim(str_replace($baseUrl, '', $url), '/');

            File::deleteByPath($key);
        } catch (Exception $exception) {}

        try {
            $url = $lesson->thumbnail_url;

            // Ekstrak key dari URL
            $baseUrl = rtrim(env('AWS_URL'), '/');
            $key = ltrim(str_replace($baseUrl, '', $url), '/');

            File::deleteByPath($key);
        } catch (Exception $exception) {}

        $lesson->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $lesson, 200);
    }

public function generateData($request)
{
    return [
        'chapter_id' => $request->chapter_id,
        'name' => $request->name,
        'sequence' => $request->sequence,
        'embed_url' => $request->embed_url,
        'video_url' => $request->video_url,
        'description' => $request->description,
        'thumbnail_url' => $request->thumbnail_url,
        'supporting_file_url' => $request->supporting_file_url,
        'is_public' => $request->is_public,
        'is_active' => $request->is_active,
        'require_completion' => $request->require_completion,  // ← TAMBAH INI
    ];
}
}
