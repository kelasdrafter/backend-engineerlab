<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLicenseRequest;
use App\Http\Requests\UpdateLicenseRequest;
use App\Models\License;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LicenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() : JsonResponse
    {
        try {
            $allowedColumns = (new License)->getFillable();
            $data = QueryBuilder::for(License::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active')])
                ->allowedSorts([...$allowedColumns, 'created_at', 'updated_at'])
                ->paginate();

            return $this->responseSuccess('Get Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLicenseRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = License::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(License $license)
    {
        return $this->responseSuccess('Get Data Succcessfully', $license, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLicenseRequest $request, License $license)
    {
        $license->fill($this->generateData($request));
        $license->save();

        return $this->responseSuccess('Update Data Succcessfully', $license, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(License $license)
    {
        $license->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $license, 200);
    }

    public function generateData($request)
    {
        return [
            'allow_access' => $request->allow_access,
            'client_id' => $request->client_id,
            'password' => $request->password,
            'uuid_client' => $request->uuid_client,
            'motherboard_client' => $request->motherboard_client,
            'processor_client' => $request->processor_client,
            'client_login' => $request->client_login,
            'client_logout' => $request->client_logout,
        ];
    }
}
