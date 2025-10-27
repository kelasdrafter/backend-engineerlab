<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDesignedForRequest;
use App\Http\Requests\UpdateDesignedForRequest;
use App\Models\DesignedFor;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class DesignedForController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 5);
            $allowedColumns = (new DesignedFor)->getFillable();
            $data = QueryBuilder::for(DesignedFor::class)
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
    public function store(StoreDesignedForRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = DesignedFor::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DesignedFor $designedFor)
    {
        return $this->responseSuccess('Get Data Succcessfully', $designedFor, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDesignedForRequest $request, DesignedFor $designedFor)
    {
        $designedFor->fill($this->generateData($request));
        $designedFor->save();

        return $this->responseSuccess('Update Data Succcessfully', $designedFor, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DesignedFor $designedFor)
    {
        $designedFor->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $designedFor, 200);
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
