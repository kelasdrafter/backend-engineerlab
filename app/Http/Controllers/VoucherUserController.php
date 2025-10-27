<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoucherUserRequest;
use App\Http\Requests\UpdateVoucherUserRequest;
use App\Models\VoucherUser;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class VoucherUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new VoucherUser)->getFillable();
            $data = QueryBuilder::for(VoucherUser::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active'), AllowedFilter::exact('voucher_id')])
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
    public function store(StoreVoucherUserRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = VoucherUser::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VoucherUser $voucherUser)
    {
        return $this->responseSuccess('Get Data Succcessfully', $voucherUser, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVoucherUserRequest $request, VoucherUser $voucherUser)
    {
        $voucherUser->fill($this->generateData($request));
        $voucherUser->save();

        return $this->responseSuccess('Update Data Succcessfully', $voucherUser, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VoucherUser $voucherUser)
    {
        $voucherUser->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $voucherUser, 200);
    }

    public function generateData($request)
    {
        return [
            'voucher_id' => $request->voucher_id,
            'email' => $request->email,
        ];
    }
}
