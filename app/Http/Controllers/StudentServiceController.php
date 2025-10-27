<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentServiceRequest;
use App\Http\Requests\UpdateStudentServiceRequest;
use App\Models\File;
use App\Models\StudentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StudentServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new StudentService)->getFillable();
            $data = QueryBuilder::for(StudentService::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active')])
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
    public function store(StoreStudentServiceRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = StudentService::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StudentService $studentService)
    {
        return $this->responseSuccess('Get Data Succcessfully', $studentService, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentServiceRequest $request, StudentService $studentService)
    {
        // Cek apakah thumbnail_url baru dan tidak sama dengan yang lama
        if ($request->has('thumbnail_url') && $request->thumbnail_url != $studentService->thumbnail_url) {
            // Hapus file jika thumbnail_url di perbarui
            $url = $studentService->thumbnail_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {}
        }

        $studentService->fill($this->generateData($request));
        $studentService->save();

        return $this->responseSuccess('Update Data Succcessfully', $studentService, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentService $studentService)
    {
        try {
            $url = $studentService->thumbnail_url;

            // Ekstrak key dari URL
            $baseUrl = rtrim(env('AWS_URL'), '/');
            $key = ltrim(str_replace($baseUrl, '', $url), '/');

            File::deleteByPath($key);
        } catch (Exception $exception) {}

        $studentService->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $studentService, 200);
    }

    public function generateData($request)
    {
        return [
            'name' => $request->name,
            'redirect_url' => $request->redirect_url,
            'thumbnail_url' => $request->thumbnail_url,
        ];
    }
}
