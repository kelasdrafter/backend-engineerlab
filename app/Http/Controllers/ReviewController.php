<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new Review)->getFillable();
            $data = QueryBuilder::for(Review::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active'), AllowedFilter::exact('user_id'), AllowedFilter::exact('batch_id')])
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
    public function store(StoreReviewRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Review::create($data);

            return $this->responseSuccess('Create Data Succcessfully', new ReviewResource($data), 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        return $this->responseSuccess('Get Data Succcessfully', new ReviewResource($review), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        $review->fill($this->generateData($request));
        $review->save();

        return $this->responseSuccess('Update Data Succcessfully', $review, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        $review->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $review, 200);
    }

    public function generateData($request)
    {
        return [
            'user_id' => $request->user_id,
            'batch_id' => $request->batch_id,
            'review' => $request->review,
            'rating' => $request->rating,
        ];
    }
}
