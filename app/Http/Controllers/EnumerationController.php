<?php

namespace App\Http\Controllers;

use App\Models\Enumeration;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Http\Requests\StoreEnumerationRequest;
use App\Http\Requests\UpdateEnumerationRequest;
use Illuminate\Http\Request;

class EnumerationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new Enumeration)->getFillable();
            $data = QueryBuilder::for(Enumeration::class)
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
    public function store(StoreEnumerationRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Enumeration::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Enumeration $enumeration)
    {
        return $this->responseSuccess('Get Data Succcessfully', $enumeration, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnumerationRequest $request, Enumeration $enumeration)
    {
        $enumeration->fill($this->generateData($request));
        $enumeration->save();

        return $this->responseSuccess('Update Data Succcessfully', $enumeration, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enumeration $enumeration)
    {
        $enumeration->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $enumeration, 200);
    }

    public function generateData($request)
    {
        return [
            'name' => $request->name,
            'value' => $request->value,
            'group' => $request->group,
            'is_active' => $request->is_active,
        ];
    }
}
