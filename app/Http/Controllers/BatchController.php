<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Models\Batch;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new Batch)->getFillable();
            $data = QueryBuilder::for(Batch::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active'), AllowedFilter::exact('course_id')])
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
    public function store(StoreBatchRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Batch::create($data);

            return $this->responseSuccess('Create Data Successfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Batch $batch)
    {
        return $this->responseSuccess('Get Data Succcessfully', $batch, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBatchRequest $request, Batch $batch)
    {
        $batch->fill($this->generateData($request));
        $batch->save();

        return $this->responseSuccess('Update Data Succcessfully', $batch, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Batch $batch)
    {
        $batch->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $batch, 200);
    }

    public function generateData($request)
    {
        return [
            'course_id' => $request->course_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'whatsapp_group_url' => $request->whatsapp_group_url,
        ];
    }
}
