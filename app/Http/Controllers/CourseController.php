<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\DetailCourseResource;
use Carbon\Carbon;
use App\Models\File;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Voucher;
use App\Models\Enrollment;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new Course)->getFillable();
            $data = QueryBuilder::for(Course::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active')])
                ->allowedSorts([...$allowedColumns, 'created_at', 'updated_at'])
                ->withCount(['chapters', 'lessons'])
                ->paginate($perPage);

            // return $this->responseSuccess('Get Data Succcessfully', $data, 200);
            return $this->responseSuccess(
                'Get Data Successfully',
                [
                    "current_page" => $data->currentPage(),
                    "data" => CourseResource::collection($data),
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
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Course::create($data);

            return $this->responseSuccess('Create Data Succcessfully', new CourseResource($data), 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        $course = Course::withCount(['chapters', 'lessons'])->find($course->id);
        return $this->responseSuccess('Get Data Successfully', new DetailCourseResource($course), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        // Cek apakah thumbnail_url baru dan tidak sama dengan yang lama
        if ($request->has('thumbnail_url') && $request->thumbnail_url != $course->thumbnail_url) {
            // Hapus file jika thumbnail_url di perbarui
            $url = $course->thumbnail_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {
            }
        }

        $course->fill($this->generateData($request));
        $course->save();

        return $this->responseSuccess('Update Data Succcessfully', $course, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        try {
            $url = $course->thumbnail_url;

            // Ekstrak key dari URL
            $baseUrl = rtrim(env('AWS_URL'), '/');
            $key = ltrim(str_replace($baseUrl, '', $url), '/');

            File::deleteByPath($key);
        } catch (Exception $exception) {
        }

        // Update slug directly in the database
        $newSlug = $course->slug . '-deleted-' . time();
        Course::where('id', $course->id)->update(['slug' => $newSlug]);

        $course->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $course, 200);
    }

    public function generateData($request)
    {
        return [
            'id' => $request->id,
            'name' => $request->name,
            'privilege' => $request->privilege,
            'benefit' => $request->benefit,
            'description' => $request->description,
            'short_description' => $request->short_description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'category_id' => $request->category_id,
            'whatsapp_group_url' => $request->whatsapp_group_url,
            'trailer_url' => $request->trailer_url,
            'thumbnail_url' => $request->thumbnail_url,
            'syllabus_url' => $request->syllabus_url,
            'total_minutes' => $request->total_minutes,
            'is_can_checkout'  => $request->is_can_checkout,
            'is_active'  => $request->is_active,
            "is_direct_class" => $request->is_direct_class,
        ];
    }



    public function enroll(Course $course, Request $request)
    {
        $user = Auth::user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
        if ($enrollment) {
            $batch = Batch::where('id', $enrollment->batch_id)
                ->where('start_date', '<=', now())
                ->first();

            if ($batch) {
                return $this->responseError('Kamu sudah membeli kursus pada batch saat ini', [], 400);
            }
        }

        $batch = Batch::where('course_id', $course->id)
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->first();

        $is_direct_class = $course->is_direct_class;

        if (!$batch && !$is_direct_class) {
            return $this->responseError('Saat ini kursus tidak ada batch yang sedang berjalan', [], 400);
        }

        $amount = $course->price;
        if ($course->discount_price > 0) {
            $amount = $course->price;
        }

        $code = "";

        if ($request->has('voucher_code') && $request->voucher_code != '') {
            $code = $request->voucher_code;
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

            // Periksa apakah voucher bisa digunakan berulang
            if (!$voucher->is_repeatable) {
                // Periksa apakah user sudah pernah menggunakan voucher
                $transaction = Transaction::where('user_id', $user->id)
                    ->where('voucher_code', $code)
                    ->first();

                if ($transaction) {
                    return $this->responseError('Voucher sudah pernah digunakan', [], 400);
                }
            }
            $discount = 0;

            // Jika voucher tipe "Fixed"
            if ($voucher->type === 'Fixed') {
                $discount = $voucher->nominal;
            }

            // Jika voucher tipe "Persentase"
            if ($voucher->type === 'Persentase') {
                $amount_course = $course->price;
                if ($course->discount_price > 0) {
                    $amount_course = $course->price;
                }
                $discount = round(($voucher->nominal / 100) * $amount_course, 2);
            }

            $amount = $amount - $discount;
        }

        // Jika jumlah dibawah 10.000 dan diatas 0
        if ($amount < 10000 && $amount > 0) {
            return $this->responseError('Payment gateway tidak bisa memproses transaksi dibawah Rp 10.000', [], 400);

            // Jika jumlah dibawah atau sama dengan 0
        } elseif ($amount <= 0) {
            // Kurangi kuota voucher, ketika transaksi berhasil
            if ($request->has('voucher_code')) {
                $code = $request->voucher_code;

                if ($code != '') {
                    $voucher = Voucher::where('code', $code)->first();
                    $voucher->decrement('quota');
                }
            }

            // Buat data transaksi
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'voucher_code' => $code,
                'amount' => 0,
                'status' => 'success',
                'meta' => json_encode($course)
            ]);

            $batch_id = 0;
            $batch = Batch::where('course_id', $course->id)
                ->where('start_date', '>=', now())
                ->orderBy('start_date', 'asc')
                ->first();

            if ($batch) {
                $batch_id = $batch->id;
            }

            // Register user to course
            Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'batch_id' => $batch_id,
                'transaction_id' => $transaction->id,
                'status' => 'ACTIVE',
            ]);
       

            return $this->responseSuccess('Checkout Success', $transaction, 200);
        }

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'voucher_code' => $code,
            'amount' => $amount,
            'status' => 'pending',
            'meta' => json_encode($course)
        ]);

        $transactionDetails = [
            'order_id' => $transaction->id,
            'gross_amount' => $amount
        ];

        $itemDetails = [
            [
                'id' => $course->id,
                'price' => $amount,
                'quantity' => 1,
                'name' => $course->name,
                'brand' => env('APP_NAME'),
                'category' => $course->category->name
            ]
        ];

        $customerDetails = [
            'first_name' => $user->full_name,
            'email' => $user->email
        ];

        $midtransParams = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails
        ];

        try {
            $snap_id = $this->getMidtransSnapToken($midtransParams);

            $transaction->snap_id = $snap_id;
            $transaction->save();

            // Kurangi kuota voucher, ketika transaksi berhasil
            if ($request->has('voucher_code')) {
                $code = $request->voucher_code;

                if ($code != '') {
                    $voucher = Voucher::where('code', $code)->first();
                    $voucher->decrement('quota');
                }
            }

            return $this->responseSuccess('Checkout Success', $transaction, 200);
        } catch (Throwable $e) {
            $transaction->status = "Failed";
            $transaction->save();

            return $this->responseError('Failed create transaction, Midtrans detect duplicate transaction', $request->all(), 400);
        }
    }

    private function getMidtransSnapToken($params)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_PRODUCTION');
        \Midtrans\Config::$is3ds = (bool) env('MIDTRANS_3DS');

        return \Midtrans\Snap::getSnapToken($params);
    }

    public function updateAbout(Course $course, Request $request)
    {
        $dataToUpdate = [];
        if ($request->has('privilege')) {
            $dataToUpdate['privilege'] = $request->input('privilege');
        }
        if ($request->has('benefit')) {
            $dataToUpdate['benefit'] = $request->input('benefit');
        }

        $course->fill($dataToUpdate);
        $course->save();

        return $this->responseSuccess('Update Data Successfully', $course, 200);
    }
}
