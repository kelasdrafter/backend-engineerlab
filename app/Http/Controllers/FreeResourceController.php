<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFreeResourceRequest;
use App\Http\Requests\UpdateFreeResourceRequest;
use App\Models\File;
use App\Models\FreeResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class FreeResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new FreeResource)->getFillable();
            $data = QueryBuilder::for(FreeResource::class)
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
    public function store(StoreFreeResourceRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = FreeResource::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FreeResource $freeResource)
    {
        return $this->responseSuccess('Get Data Succcessfully', $freeResource, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFreeResourceRequest $request, FreeResource $freeResource)
    {
        // Cek apakah assets_url baru dan tidak sama dengan yang lama
        if ($request->has('assets_url') && $freeResource->assets_url != $freeResource->assets_url) {
            // Hapus file jika assets_url di perbarui
            $url = $freeResource->assets_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {}
        }

        // Cek apakah thumbnail_url baru dan tidak sama dengan yang lama
        if ($request->has('thumbnail_url') && $request->thumbnail_url != $freeResource->thumbnail_url) {
            // Hapus file jika thumbnail_url di perbarui
            $url = $freeResource->thumbnail_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {}
        }

        $freeResource->fill($this->generateData($request));
        $freeResource->save();

        return $this->responseSuccess('Update Data Succcessfully', $freeResource, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FreeResource $freeResource)
    {
        try {
            $url = $freeResource->thumbnail_url;

            // Ekstrak key dari URL
            $baseUrl = rtrim(env('AWS_URL'), '/');
            $key = ltrim(str_replace($baseUrl, '', $url), '/');

            File::deleteByPath($key);
        } catch (Exception $exception) {}

        try {
            $url = $freeResource->assets_url;

            // Ekstrak key dari URL
            $baseUrl = rtrim(env('AWS_URL'), '/');
            $key = ltrim(str_replace($baseUrl, '', $url), '/');

            File::deleteByPath($key);
        } catch (Exception $exception) {}

        $freeResource->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $freeResource, 200);
    }

    public function generateData($request)
    {
        return [
            'name' => $request->name,
            'thumbnail_url' => $request->thumbnail_url,
            'assets_url' => $request->assets_url,
            'tags' => $request->tags,
            'description' => $request->description,
        ];
    }
}
