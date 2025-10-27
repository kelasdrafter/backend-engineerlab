<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use Carbon\Carbon;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new Transaction)->getFillable();
            $data = QueryBuilder::for(Transaction::class)
                ->allowedFilters([
                    ...$allowedColumns,
                    AllowedFilter::exact('is_active'),
                    AllowedFilter::exact('course_id'),
                    AllowedFilter::exact('is_active'),
                    AllowedFilter::callback('email', function ($query, $value) {
                        $query->whereHas('user', function ($query) use ($value) {
                            $query->where('email', $value);
                        });
                    })
                ])
                ->allowedSorts([...$allowedColumns, 'created_at', 'updated_at'])
                ->defaultSort('-created_at')
                ->paginate($perPage);

            // return $this->responseSuccess('Get Data Succcessfully', $data, 200);
            return $this->responseSuccess(
                'Get Data Successfully',
                [
                    "current_page" => $data->currentPage(),
                    "data" => TransactionResource::collection($data),
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
    public function store(StoreTransactionRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Transaction::create($data);

            return $this->responseSuccess('Create Data Succcessfully', new TransactionResource($data), 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        return $this->responseSuccess('Get Data Succcessfully', new TransactionResource($transaction), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $transaction->fill($this->generateData($request));
        $transaction->save();

        return $this->responseSuccess('Update Data Succcessfully', new TransactionResource($transaction), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $transaction, 200);
    }

    public function exportTransactions(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Transaction::with(['user', 'course'])
            ->where('status', 'success');

        // Validasi dan parsing tanggal
        try {
            if ($startDate) {
                $startDate = Carbon::parse($startDate)->startOfDay();
            }
            if ($endDate) {
                $endDate = Carbon::parse($endDate)->endOfDay();
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format.'], 400);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        // Tambahkan pengurutan berdasarkan 'created_at' dari terlama ke terbaru
        $query->orderBy('created_at', 'asc');
        $transactions = $query->get();

        // Cek apakah ada data transaksi
        if ($transactions->isEmpty()) {
            return response()->json(['message' => 'No transactions found for the given criteria.'], 404);
        }

        $data = [];
        foreach ($transactions as $transaction) {
            $course = $transaction->course;
            $data[] = [
                'full_name' => optional($transaction->user)->name ?? '',
                'email' => optional($transaction->user)->email ?? '',
                'phone' => optional($transaction->user)->phone ?? '',
                'total' => $transaction->amount,
                'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                'voucher_code' => $transaction->voucher_code ?? '',
            ];
        }

        return Excel::download(new TransactionsExport($data), 'transactions.xlsx');
    }

    public function generateData($request)
    {
        return [
            'user_id' => $request->user_id,
            'course_id' => $request->course_id,
            'voucher_code' => $request->voucher_code,
            'status' => $request->status,
            'meta' => $request->meta,
            'amount' => $request->amount,
        ];
    }
}
