<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExperienceRequest;
use App\Http\Requests\UpdateExperienceRequest;
use App\Models\Experience;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new Experience)->getFillable();
            $data = QueryBuilder::for(Experience::class)
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
    public function store(StoreExperienceRequest $request)
    {
        try {
            $user_id = $request->user_id ?? auth()->id();

            $data = $this->generateData(array_merge($request->all(), [
                'user_id' => $user_id,
            ]));
            $data = Experience::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Experience $experience)
    {
        return $this->responseSuccess('Get Data Succcessfully', $experience, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExperienceRequest $request, Experience $experience)
    {
        $user_id = $request->user_id ?? auth()->id();
        $data = $this->generateData(array_merge($request->all(), [
            'user_id' => $user_id,
        ]));

        $experience->fill($data);
        $experience->save();

        return $this->responseSuccess('Update Data Succcessfully', $experience, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Experience $experience)
    {
        $experience->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $experience, 200);
    }

    public function generateData($request)
    {
        return [
            'user_id' => $request['user_id'],
            'job_title' => $request['job_title'],
            'company_name' => $request['company_name'],
            'employment_type' => $request['employment_type'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'location' => $request['location'],
        ];
    }
}
