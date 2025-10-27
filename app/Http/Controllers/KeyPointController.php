<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKeyPointRequest;
use App\Http\Requests\UpdateKeyPointRequest;
use App\Models\KeyPoint;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class KeyPointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 5);
            $allowedColumns = (new KeyPoint)->getFillable();
            $data = QueryBuilder::for(KeyPoint::class)
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
    public function store(StoreKeyPointRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = KeyPoint::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(KeyPoint $keyPoint)
    {
        return $this->responseSuccess('Get Data Succcessfully', $keyPoint, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKeyPointRequest $request, KeyPoint $keyPoint)
    {
        $keyPoint->fill($this->generateData($request));
        $keyPoint->save();

        return $this->responseSuccess('Update Data Succcessfully', $keyPoint, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KeyPoint $keyPoint)
    {
        $keyPoint->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $keyPoint, 200);
    }

    public function generateData($request)
    {
        return [
            'course_id' => $request->course_id,
            'text' => $request->text,
            'is_active' => $request->is_active,
        ];
    }
}
