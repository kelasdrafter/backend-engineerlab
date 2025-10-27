<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoucherRequest;
use App\Http\Requests\UpdateVoucherRequest;
use App\Http\Requests\CheckVoucherRequest;
use Carbon\Carbon;
use App\Models\File;
use App\Models\Course;
use App\Models\Voucher;
use App\Models\Transaction;
use App\Models\PremiumProduct;
use App\Models\PremiumTransaction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class VoucherController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
    public function index(Request $request): JsonResponse
	{
		try {
            $perPage = $request->get('per_page', 15);
			$user = Auth::user();
			if ($user && $user->role == "admin") {
				$data = QueryBuilder::for(Voucher::class)
					->allowedFilters([...(new Voucher)->getFillable(), AllowedFilter::exact('is_active')])
					->allowedSorts([...(new Voucher)->getFillable(), 'created_at', 'updated_at'])
                	->paginate($perPage);
			} else {
				$today = now()->toDateString();
				$data = QueryBuilder::for(Voucher::class)
					->where('is_public', true)
					->where('quota', '>', 0)
					->whereDate('start_at', '<=', $today)
					->whereDate('end_at', '>=', $today)
					->allowedFilters([...(new Voucher)->getFillable(), AllowedFilter::exact('is_active')])
					->allowedSorts([...(new Voucher)->getFillable(), 'created_at', 'updated_at'])
                	->paginate($perPage);
			}

			return $this->responseSuccess('Get Data Successfully', $data, 200);
		} catch (Exception $exception) {
			return $this->responseError($exception->getMessage(), [], 500);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(StoreVoucherRequest $request)
	{
		try {
			$data = $this->generateData($request);
			$data = Voucher::create($data);

			return $this->responseSuccess('Create Data Succcessfully', $data, 200);
		} catch (Exception $exeception) {
			return $this->responseError($exeception, [], 500);
		}
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Voucher $voucher)
	{
		return $this->responseSuccess('Get Data Succcessfully', $voucher, 200);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(UpdateVoucherRequest $request, Voucher $voucher)
	{
		// Cek apakah thumbnail_url baru dan tidak sama dengan yang lama
        if ($request->has('thumbnail_url') && $request->thumbnail_url != $voucher->thumbnail_url) {
            // Hapus file jika thumbnail_url di perbarui
            $url = $voucher->thumbnail_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {}
        }

		$voucher->fill($this->generateData($request));
		$voucher->save();

		return $this->responseSuccess('Update Data Succcessfully', $voucher, 200);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Voucher $voucher)
	{
		try {
            $url = $voucher->thumbnail_url;

            // Ekstrak key dari URL
            $baseUrl = rtrim(env('AWS_URL'), '/');
            $key = ltrim(str_replace($baseUrl, '', $url), '/');

            File::deleteByPath($key);
        } catch (Exception $exception) {}
		$voucher->delete();
		return $this->responseSuccess('Delete Data Succcessfully', $voucher, 200);
	}

	/**
	 * ✅ MODIFIKASI: Check voucher - support Course & Premium Product
	 */
	public function checkVoucher(CheckVoucherRequest $request)
	{
		$user = Auth::user();
		$validated = $request->validated();
		
		$code = $validated['code'];
		$courseId = $validated['course_id'] ?? null;
		$premiumProductId = $validated['premium_product_id'] ?? null;

		// Cari voucher berdasarkan kode 
		$voucher = Voucher::where('code', $code)->first();
		if (!$voucher) {
			return $this->responseError('Voucher tidak valid', [], 400);
		}
		
		// Memastikan voucher masih aktif
		if (!$voucher->is_active) {
			return $this->responseError('Voucher tidak aktif', [], 400);
		}

		// Memeriksa apakah saat ini berada dalam rentang waktu yang valid untuk voucher
		$now = Carbon::now();
		if ($now->lt(new Carbon($voucher->start_at))) {
			return $this->responseError('Voucher belum bisa digunakan sekarang', [], 400);
		}
		if ($now->gt(new Carbon($voucher->end_at))) {
			return $this->responseError('Voucher telah kedaluwarsa', [], 400);
		}

		// Periksa kuota voucher
		if ($voucher->quota == 0) {
			return $this->responseError('Kuota voucher telah habis', [], 400);
		}

		// ✅ Periksa apakah voucher bisa digunakan berulang
		if (!$voucher->is_repeatable) {
			// ✅ Cek untuk Course
			if ($courseId) {
				$transaction = Transaction::where('user_id', $user->id)
								->where('voucher_code', $code)
								->first();
				
				if ($transaction) {
					return $this->responseError('Voucher has already been used', [], 400);
				}
			}

			// ✅ Cek untuk Premium Product
			if ($premiumProductId) {
				$premiumTransaction = PremiumTransaction::where('user_id', $user->id)
								->where('voucher_code', $code)
								->first();
				
				if ($premiumTransaction) {
					return $this->responseError('Voucher has already been used', [], 400);
				}
			}
		}

		// ✅ Calculate discount berdasarkan jenis produk
		if ($courseId) {
			return $this->calculateCourseDiscount($voucher, $courseId);
		}

		if ($premiumProductId) {
			return $this->calculatePremiumProductDiscount($voucher, $premiumProductId);
		}

		return $this->responseError('Voucher tidak valid', [], 400);
	}

	/**
	 * ✅ TIDAK BERUBAH: Calculate discount untuk Course (logic existing)
	 */
	private function calculateCourseDiscount($voucher, $courseId)
	{
		$course = Course::find($courseId);

		if (!$course) {
			return $this->responseError('Kursus tidak ditemukan', [], 400);
		}

		$amount = $course->price;
		if ($course->discount_price > 0) {
			$amount = $course->discount_price;
		}

		// Jika voucher tipe "Fixed"
		if ($voucher->type === 'Fixed') {
			return $this->responseSuccess(
				'Request Data Succcessfully', 
				['discount' => $voucher->nominal, 'type' => 'Fixed'], 
				200
			);
		}

		// Jika voucher tipe "Persentase"
		if ($voucher->type === 'Persentase') {
			$discountAmount = round(($voucher->nominal / 100) * $amount, 2);
			return $this->responseSuccess(
				'Request Data Succcessfully', 
				['discount' => $discountAmount, 'type' => 'Persentase'], 
				200
			);
		}

		return $this->responseError('Voucher tidak valid', [], 400);
	}

	/**
	 * ✅ BARU: Calculate discount untuk Premium Product
	 */
	private function calculatePremiumProductDiscount($voucher, $premiumProductId)
	{
		$product = PremiumProduct::find($premiumProductId);

		if (!$product) {
			return $this->responseError('Product tidak ditemukan', [], 400);
		}

		$amount = $product->price;
		if ($product->discount_price > 0) {
			$amount = $product->discount_price;
		}

		// Jika voucher tipe "Fixed"
		if ($voucher->type === 'Fixed') {
			return $this->responseSuccess(
				'Request Data Successfully', 
				['discount' => $voucher->nominal, 'type' => 'Fixed'], 
				200
			);
		}

		// Jika voucher tipe "Persentase"
		if ($voucher->type === 'Persentase') {
			$discountAmount = round(($voucher->nominal / 100) * $amount, 2);
			return $this->responseSuccess(
				'Request Data Successfully', 
				['discount' => $discountAmount, 'type' => 'Persentase'], 
				200
			);
		}

		return $this->responseError('Voucher tidak valid', [], 400);
	}

	public function generateData($request)
	{
		return [
			'code' => $request->code,
			'type' => $request->type,
			'nominal' => $request->nominal,
			'name' => $request->name,
			'quota' => $request->quota,
			'description' => $request->description,
			'thumbnail_url' => $request->thumbnail_url,
			'start_at' => $request->start_at,
			'end_at' => $request->end_at,
			'is_public' => $request->is_public,
			'is_repeatable' => $request->is_repeatable,
			'is_active' => $request->is_active,
		];
	}
}