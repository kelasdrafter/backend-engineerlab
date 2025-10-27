<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrivilegeRequest;
use App\Http\Requests\UpdatePrivilegeRequest;
use App\Models\Privilege;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class PrivilegeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 5);
            $allowedColumns = (new Privilege)->getFillable();
            $data = QueryBuilder::for(Privilege::class)
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
    public function store(StorePrivilegeRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Privilege::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Privilege $privilege)
    {
        return $this->responseSuccess('Get Data Succcessfully', $privilege, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePrivilegeRequest $request, Privilege $privilege)
    {
        $privilege->fill($this->generateData($request));
        $privilege->save();

        return $this->responseSuccess('Update Data Succcessfully', $privilege, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Privilege $privilege)
    {
        $privilege->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $privilege, 200);
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
