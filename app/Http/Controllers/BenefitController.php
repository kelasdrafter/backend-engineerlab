<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBenefitRequest;
use App\Http\Requests\UpdateBenefitRequest;
use App\Models\Benefit;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class BenefitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 5);
            $allowedColumns = (new Benefit)->getFillable();
            $data = QueryBuilder::for(Benefit::class)
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
    public function store(StoreBenefitRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Benefit::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Benefit $benefit)
    {
        return $this->responseSuccess('Get Data Succcessfully', $benefit, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBenefitRequest $request, Benefit $benefit)
    {
        $benefit->fill($this->generateData($request));
        $benefit->save();

        return $this->responseSuccess('Update Data Succcessfully', $benefit, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Benefit $benefit)
    {
        $benefit->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $benefit, 200);
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
