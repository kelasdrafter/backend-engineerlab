<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Models\Goal;
use App\Models\File;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 5);
            $allowedColumns = (new Goal)->getFillable();
            $data = QueryBuilder::for(Goal::class)
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
    public function store(StoreGoalRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Goal::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Goal $goal)
    {
        return $this->responseSuccess('Get Data Succcessfully', $goal, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGoalRequest $request, Goal $goal)
    {
        // Cek apakah thumbnail_url baru dan tidak sama dengan yang lama
        if ($request->has('image_url') && $request->image_url != $goal->image_url) {
            // Hapus file jika image_url di perbarui
            $url = $goal->image_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {}
        }

        $goal->fill($this->generateData($request));
        $goal->save();

        return $this->responseSuccess('Update Data Succcessfully', $goal, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Goal $goal)
    {
        try {
            $url = $goal->image_url;

            // Ekstrak key dari URL
            $baseUrl = rtrim(env('AWS_URL'), '/');
            $key = ltrim(str_replace($baseUrl, '', $url), '/');

            File::deleteByPath($key);
        } catch (Exception $exception) {}

        $goal->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $goal, 200);
    }

    public function generateData($request)
    {
        return [
            'course_id' => $request->course_id,
            'image_url' => $request->image_url,
            'is_active' => $request->is_active,
        ];
    }
}
