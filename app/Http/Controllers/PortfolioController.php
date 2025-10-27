<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePortfolioRequest;
use App\Http\Requests\UpdatePortfolioRequest;
use App\Http\Resources\PortfolioResource;
use App\Http\Resources\UserPortfolioResource;
use App\Models\Portfolio;
use App\Models\PortofolioImage;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new Portfolio)->getFillable();
            $data = QueryBuilder::for(Portfolio::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active')])
                ->allowedSorts([...$allowedColumns, 'created_at', 'updated_at'])
                ->paginate($perPage);

            // return $this->responseSuccess('Get Data Succcessfully', $data, 200);
            return $this->responseSuccess(
                'Get Data Successfully',
                [
                    "current_page" => $data->currentPage(),
                    "data" => PortfolioResource::collection($data),
                    "first_page_url" => $data->url(1),
                    "from" => $data->firstItem(),
                    "last_page" => $data->lastPage(),
                    "last_page_url" => $data->url($data->lastPage()),
                    "links" => $data->linkCollection(),
                    "next_page_url" => $data->nextPageUrl(),
                    "path" => $data->path(),
                    "per_page" => $data->perPage(),
                    "prev_page_url" => $data->previousPageUrl(),
                    "to" => $data->lastItem(),
                    "total" => $data->total()
                ],
                200
            );
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePortfolioRequest $request)
    {
        try {
            $user_id = $request->user_id ?? auth()->id();
            $status = $request->status ?? 'on_review';

            // Periksa apakah ada key "images" dan validasi isinya
            if (!$request->has('images') || !is_array($request->images)) {
                return $this->responseError("Images are required and must be an array of URLs.", [], 422);
            }

            $data = $this->generateData(array_merge($request->all(), [
                'user_id' => $user_id,
                'status' => $status,
            ]));
            $data = Portfolio::create($data);

            // Looping list dari images dan buat data ke Portfolio Image
            foreach ($request->images as $imageUrl) {
                PortofolioImage::create([
                    'portfolio_id' => $data->id,
                    'image_url' => $imageUrl
                ]);
            }

            return $this->responseSuccess('Create Data Successfully', new PortfolioResource($data), 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Portfolio $portfolio)
    {
        return $this->responseSuccess('Get Data Succcessfully', new PortfolioResource($portfolio), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePortfolioRequest $request, Portfolio $portfolio)
    {
        $user_id = $request->user_id ?? auth()->id();
        $data = $this->generateData(array_merge($request->all(), [
            'user_id' => $user_id,
        ]));

        $portfolio->fill($data);
        $portfolio->save();

        return $this->responseSuccess('Update Data Succcessfully', new PortfolioResource($portfolio), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Portfolio $portfolio)
    {
        $portfolio->delete();
        return $this->responseSuccess('Delete Data Succcessfully', new PortfolioResource($portfolio), 200);
    }

    public function getUserPortfolio($userId)
    {
        $data = Portfolio::where('user_id', $userId)
                ->where('status', 'publish')
                ->paginate();

        if (!$data->isEmpty()) {
            $portfolio = [
                "current_page" => $data->currentPage(),
                "data" => [
                    "user" => $data[0]->user,
                    "experiences" => $data[0]->user->experiences,
                    "portfolio" => UserPortfolioResource::collection($data)
                ],
                "first_page_url" => $data->url(1),
                "from" => $data->firstItem(),
                "last_page" => $data->lastPage(),
                "last_page_url" => $data->url($data->lastPage()),
                "links" => $data->linkCollection(),
                "next_page_url" => $data->nextPageUrl(),
                "path" => $data->path(),
                "per_page" => $data->perPage(),
                "prev_page_url" => $data->previousPageUrl(),
                "to" => $data->lastItem(),
                "total" => $data->total()
            ];

            return $this->responseSuccess('Get Data Succcessfully', $portfolio, 200);
        } else {
            $data = User::where('id', $userId)->first();
            $portfolio = [
                "data" => [
                    "user" => $data,
                    "experiences" => $data->experiences,
                    "portfolio" => []
                ],
            ];

            return $this->responseSuccess('Get Data Succcessfully', $portfolio, 200);
        }
    }

    public function generateData($request)
    {
        return [
            'user_id' => $request['user_id'],
            'title' => $request['title'],
            'status' => $request['status'],
            'description' => $request['description'] ?? null,
        ];
    }
}
