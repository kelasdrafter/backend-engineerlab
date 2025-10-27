<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCareerRequest;
use App\Http\Requests\UpdateCareerRequest;
use App\Http\Resources\CareerResource;
use App\Models\Career;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new Career)->getFillable();
            $data = QueryBuilder::for(Career::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active'), AllowedFilter::exact('category_id')])
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
    public function store(StoreCareerRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Career::create($data);

            return $this->responseSuccess('Create Data Succcessfully', new CareerResource($data), 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Career $career)
    {
        return $this->responseSuccess('Get Data Succcessfully', new CareerResource($career), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCareerRequest $request, Career $career)
    {
        $career->fill($this->generateData($request));
        $career->save();

        return $this->responseSuccess('Update Data Succcessfully', $career, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Career $career)
    {
        $career->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $career, 200);
    }

    public function generateData($request)
    {
        return [
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
            'category_id' => $request->category_id,
            'is_active' => $request->is_active,
        ];
    }
}
