<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePortofolioImageRequest;
use App\Http\Requests\UpdatePortofolioImageRequest;
use App\Models\PortofolioImage;
use App\Models\File;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class PortofolioImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new PortofolioImage)->getFillable();
            $data = QueryBuilder::for(PortofolioImage::class)
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
    public function store(StorePortofolioImageRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = PortofolioImage::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PortofolioImage $portofolioImage)
    {
        return $this->responseSuccess('Get Data Succcessfully', $portofolioImage, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePortofolioImageRequest $request, PortofolioImage $portofolioImage)
    {
        // Cek apakah image_url baru dan tidak sama dengan yang lama
        if ($request->has('image_url') && $request->image_url != $portofolioImage->image_url) {
            // Hapus file jika image_url di perbarui
            $url = $portofolioImage->image_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {}
        }

        $portofolioImage->fill($this->generateData($request));
        $portofolioImage->save();

        return $this->responseSuccess('Update Data Succcessfully', $portofolioImage, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PortofolioImage $portofolioImage)
    {
        try {
            $url = $portofolioImage->image_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {}
        } catch (Exception $exception) {}

        $portofolioImage->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $portofolioImage, 200);
    }

    public function generateData($request)
    {
        return [
            'portfolio_id' => $request->portfolio_id,
            'image_url' => $request->image_url,
        ];
    }
}
