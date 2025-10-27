<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePremiumTransactionRequest;
use App\Http\Resources\PremiumTransactionResource;
use App\Models\PremiumTransaction;
use App\Models\PremiumProduct;
use App\Models\PremiumPurchase;
use App\Models\Voucher;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PremiumTransactionController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $user = Auth::user();

            $query = QueryBuilder::for(PremiumTransaction::class)
                ->with(['user', 'product'])
                ->allowedFilters([
                    AllowedFilter::exact('status'),
                    AllowedFilter::exact('user_id'),
                    AllowedFilter::exact('premium_product_id'),
                    'voucher_code',
                ])
                ->allowedSorts(['amount', 'created_at', 'status'])
                ->defaultSort('-created_at');

            // Non-admin hanya bisa lihat transaksi sendiri
            if ($user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }

            $data = $query->paginate($perPage);

            return $this->responseSuccess('Get Data Successfully', [
                'data' => PremiumTransactionResource::collection($data),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ],
            ], 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Create transaction & checkout (User)
     */
    public function store(StorePremiumTransactionRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();

            $product = PremiumProduct::findOrFail($validated['premium_product_id']);

            // Check if user already purchased this product
            $alreadyPurchased = PremiumPurchase::where('user_id', $user->id)
                ->where('premium_product_id', $product->id)
                ->exists();

            if ($alreadyPurchased) {
                return $this->responseError('You have already purchased this product', [], 400);
            }

            // Calculate amount
            $amount = $product->discount_price > 0 ? $product->discount_price : $product->price;

            // Apply voucher if provided
            $voucherDiscount = 0;
            if (!empty($validated['voucher_code'])) {
                $voucherDiscount = $this->calculateVoucherDiscount($validated['voucher_code'], $product->id, $user->id);
                if ($voucherDiscount > 0) {
                    $amount -= $voucherDiscount;
                }
            }

            // Amount cannot be negative
            if ($amount < 0) {
                $amount = 0;
            }

            DB::beginTransaction();

            // Create transaction
            $transaction = PremiumTransaction::create([
                'user_id' => $user->id,
                'premium_product_id' => $product->id,
                'voucher_code' => $validated['voucher_code'] ?? null,
                'status' => 'pending',
                'amount' => $amount,
                'meta' => [
                    'original_price' => $product->price,
                    'discount_price' => $product->discount_price,
                    'voucher_discount' => $voucherDiscount,
                ],
            ]);

            // Request Midtrans Snap Token
            $snapToken = $this->createMidtransTransaction($transaction);
            $transaction->update(['snap_id' => $snapToken]);

            DB::commit();

            return $this->responseSuccess('Transaction created successfully', [
                'transaction' => new PremiumTransactionResource($transaction),
                'snap_token' => $snapToken,
            ], 201);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Calculate voucher discount
     */
    private function calculateVoucherDiscount($voucherCode, $productId, $userId)
    {
        try {
            $voucher = Voucher::where('code', $voucherCode)
                ->where('is_active', true)
                ->where('quota', '>', 0)
                ->first();

            if (!$voucher) {
                return 0;
            }

            // Check if voucher is still valid (date range)
            $now = now();
            if ($now->lt($voucher->start_at) || $now->gt($voucher->end_at)) {
                return 0;
            }

            // Check if user already used this voucher
            if (!$voucher->is_repeatable) {
                $alreadyUsed = PremiumTransaction::where('user_id', $userId)
                    ->where('voucher_code', $voucherCode)
                    ->exists();

                if ($alreadyUsed) {
                    return 0;
                }
            }

            $product = PremiumProduct::find($productId);
            $amount = $product->discount_price > 0 ? $product->discount_price : $product->price;

            if ($voucher->type === 'Fixed') {
                return $voucher->nominal;
            } elseif ($voucher->type === 'Persentase') {
                return round(($voucher->nominal / 100) * $amount, 2);
            }

            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Create Midtrans transaction
     * ✅ FIX: Added callback URL
     */
private function createMidtransTransaction($transaction)
{
    \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
    \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
    \Midtrans\Config::$isSanitized = env('MIDTRANS_IS_SANITIZED', true);
    \Midtrans\Config::$is3ds = env('MIDTRANS_IS_3DS', true);

    $params = [
        'transaction_details' => [
            'order_id' => $transaction->id,
            'gross_amount' => (int) $transaction->amount,
        ],
        'customer_details' => [
            'first_name' => $transaction->user->name,
            'email' => $transaction->user->email,
            'phone' => $transaction->user->phone ?? '',
        ],
        'item_details' => [
            [
                'id' => $transaction->product->id,
                'price' => (int) $transaction->amount,
                'quantity' => 1,
                'name' => $transaction->product->name,
            ]
        ],
        'callbacks' => [
            'finish' => env('APP_FRONTEND_URL') . '/premium-assets/my-assets',
        ],
    ];

    $snapToken = \Midtrans\Snap::getSnapToken($params);
    return $snapToken;
}

    /**
     * Show transaction detail
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $transaction = PremiumTransaction::with(['user', 'product', 'purchase'])->findOrFail($id);

            // Non-admin hanya bisa lihat transaksi sendiri
            if ($user->role !== 'admin' && $transaction->user_id !== $user->id) {
                return $this->responseError('Unauthorized', [], 403);
            }

            return $this->responseSuccess('Get Data Successfully', new PremiumTransactionResource($transaction), 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 404);
        }
    }

    /**
     * Get user's purchases (products that user has access to)
     */
    public function myPurchases(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);

            $purchases = QueryBuilder::for(PremiumPurchase::class)
                ->with(['product', 'transaction'])
                ->where('user_id', $user->id)
                ->allowedFilters([
                    AllowedFilter::exact('status'),
                    AllowedFilter::exact('premium_product_id'),
                ])
                ->allowedSorts(['created_at'])
                ->defaultSort('-created_at')
                ->paginate($perPage);

            return $this->responseSuccess('Get Data Successfully', $purchases, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Check if user has access to download product
     */
    public function checkAccess($productId): JsonResponse
    {
        try {
            $user = Auth::user();

            $hasAccess = PremiumPurchase::hasAccess($user->id, $productId);

            if ($hasAccess) {
                $purchase = PremiumPurchase::with(['product'])
                    ->where('user_id', $user->id)
                    ->where('premium_product_id', $productId)
                    ->where('status', 'ACTIVE')
                    ->first();

                return $this->responseSuccess('User has access', [
                    'has_access' => true,
                    'purchase' => $purchase,
                    'download_url' => $purchase->product->file_url,
                ], 200);
            }

            return $this->responseSuccess('User does not have access', [
                'has_access' => false,
            ], 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }
}