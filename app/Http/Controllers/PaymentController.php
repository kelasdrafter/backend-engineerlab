<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
// ✅ TAMBAHAN: Import model premium
use App\Models\PremiumTransaction;
use App\Models\PremiumPayment;
use App\Models\PremiumPurchase;
use App\Models\PremiumProduct;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
// ✅ PHASE 1: Import untuk Email Notification System
use Illuminate\Support\Facades\Mail;
use App\Mail\CourseEnrollmentMail;
use App\Mail\PremiumPurchaseMail;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new Payment)->getFillable();
            $data = QueryBuilder::for(Payment::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active'), AllowedFilter::exact('user_id')])
                ->allowedSorts([...$allowedColumns, 'created_at', 'updated_at'])
                ->defaultSort('-created_at')
                ->paginate($perPage);

            return $this->responseSuccess('Get Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Payment::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        return $this->responseSuccess('Get Data Succcessfully', $payment, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        $payment->fill($this->generateData($request));
        $payment->save();

        return $this->responseSuccess('Update Data Succcessfully', $payment, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $payment, 200);
    }

    public function generateData($request)
    {
        return [
            'transaction_id' => $request->transaction_id,
            'user_id' => $request->user_id,
            'payment_method' => $request->payment_method,
            'status' => $request->status,
            'raw_response' => $request->raw_response,
            'raw_request' => $request->raw_request,
        ];
    }

    // ✅ MODIFIKASI: Webhook utama untuk deteksi jenis transaksi
    public function webhookMidtrans(Request $request)
    {
        $data = $request->all();
        $orderId = $data['order_id'];

        // ✅ DETEKSI: Cek apakah ini transaksi Course atau Premium Product
        $courseTransaction = Transaction::find($orderId);
        if ($courseTransaction) {
            // Transaksi Course - gunakan logic existing
            return $this->handleCourseWebhook($data);
        }

        $premiumTransaction = PremiumTransaction::find($orderId);
        if ($premiumTransaction) {
            // Transaksi Premium Product - gunakan logic baru
            return $this->handlePremiumWebhook($data);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'order id not found'
        ], 404);
    }

    // ✅ PHASE 4: Update handleCourseWebhook dengan email notification baru
    private function handleCourseWebhook($data)
    {
        $signatureKey = $data['signature_key'];
        $orderId = $data['order_id'];
        $statusCode = $data['status_code'];
        $grossAmount = $data['gross_amount'];
        $serverKey = env('MIDTRANS_SERVER_KEY');

        $mySignatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $transactionStatus = $data['transaction_status'];
        $type = $data['payment_type'];
        $fraudStatus = $data['fraud_status'];

        if ($signatureKey !== $mySignatureKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'invalid signature'
            ], 400);
        }

        $order = Transaction::find($orderId);
        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'order id not found'
            ], 404);
        }

        if ($order->status === 'success') {
            return response()->json([
                'status' => 'error',
                'message' => 'operation not permitted'
            ], 405);
        }

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $order->status = 'challenge';
            } else if ($fraudStatus == 'accept') {
                $order->status = 'success';
            }
        } else if ($transactionStatus == 'settlement') {
            $order->status = 'success';
        } else if (
            $transactionStatus == 'cancel' ||
            $transactionStatus == 'deny' ||
            $transactionStatus == 'expire'
        ) {
            $order->status = 'failure';
        } else if ($transactionStatus == 'pending') {
            $order->status = 'pending';
        }

        $logData = [
            'status' => $transactionStatus,
            'raw_response' => json_encode($data),
            'transaction_id' => $orderId,
            'payment_method' => $type,
            'user_id' => $order->user_id
        ];

        Payment::create($logData);
        $order->save();

        if ($order->status === 'success') {
            $batch_id = 0;
            $batch = Batch::where('course_id', $order->course_id)
                ->where('start_date', '>=', now())
                ->orderBy('start_date', 'asc')
                ->first();

            if ($batch) {
                $batch_id = $batch->id;
            }

            // Register user to course
            Enrollment::create([
                'user_id' => $order->user_id,
                'course_id' => $order->course_id,
                'batch_id' => $batch_id,
                'transaction_id' => $order->id,
                'status' => 'ACTIVE',
            ]);

            // get batch 
            $batch = Batch::find($batch_id);
            // get course from model
            $course = Course::find($order->course_id);
            // get user from model
            $user = User::find($order->user_id);

            // ✅ PHASE 4: Kirim email menggunakan CourseEnrollmentMail (BARU)
            try {
                Mail::to($user->email)->send(new CourseEnrollmentMail($user, $course, $batch, $order));
                Log::info('Course enrollment email sent successfully', [
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'transaction_id' => $order->id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send course enrollment email', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'course_id' => $course->id
                ]);
            }
        }

        return response()->json('Ok');
    }

    // ✅ PHASE 4: Update handlePremiumWebhook dengan email notification baru
    private function handlePremiumWebhook($data)
    {
        $signatureKey = $data['signature_key'];
        $orderId = $data['order_id'];
        $statusCode = $data['status_code'];
        $grossAmount = $data['gross_amount'];
        $serverKey = env('MIDTRANS_SERVER_KEY');

        $mySignatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $transactionStatus = $data['transaction_status'];
        $type = $data['payment_type'];
        $fraudStatus = $data['fraud_status'] ?? null;

        // Validate signature
        if ($signatureKey !== $mySignatureKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'invalid signature'
            ], 400);
        }

        $transaction = PremiumTransaction::find($orderId);
        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'order id not found'
            ], 404);
        }

        // Prevent double processing
        if ($transaction->status === 'success') {
            return response()->json([
                'status' => 'error',
                'message' => 'operation not permitted'
            ], 405);
        }

        // Update transaction status
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $transaction->status = 'challenge';
            } else if ($fraudStatus == 'accept') {
                $transaction->status = 'success';
            }
        } else if ($transactionStatus == 'settlement') {
            $transaction->status = 'success';
        } else if (
            $transactionStatus == 'cancel' ||
            $transactionStatus == 'deny' ||
            $transactionStatus == 'expire'
        ) {
            $transaction->status = 'failure';
        } else if ($transactionStatus == 'pending') {
            $transaction->status = 'pending';
        }

        // Create payment log
        PremiumPayment::create([
            'premium_transaction_id' => $orderId,
            'user_id' => $transaction->user_id,
            'payment_method' => $type,
            'status' => $transactionStatus,
            'raw_response' => $data,
            'raw_request' => null,
        ]);

        $transaction->save();

        // If payment success, create purchase record
        if ($transaction->status === 'success') {
            PremiumPurchase::create([
                'user_id' => $transaction->user_id,
                'premium_product_id' => $transaction->premium_product_id,
                'premium_transaction_id' => $transaction->id,
                'status' => 'ACTIVE',
            ]);

            // Increment purchase count
            $product = PremiumProduct::find($transaction->premium_product_id);
            if ($product) {
                $product->incrementPurchaseCount();
            }

            // ✅ PHASE 4: Kirim email menggunakan PremiumPurchaseMail (BARU)
            $user = User::find($transaction->user_id);
            
            try {
                Mail::to($user->email)->send(new PremiumPurchaseMail($user, $product, $transaction));
                Log::info('Premium purchase email sent successfully', [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'transaction_id' => $transaction->id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send premium purchase email', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'product_id' => $product->id
                ]);
            }
        }

        return response()->json('Ok');
    }

    // ⚠️ DEPRECATED: Function lama, tidak digunakan lagi (bisa dihapus atau dibiarkan)
    private function sendEnrollmentEmail($email , object $course)
    {
        // send email
        $subject = 'Selamat Datang di Kursus Kami!';

      // return view mailjoin
   $maildata =  view('mailJoin', ['course' => $course]);
        Mail::send($maildata, [], function ($mail) use ($email, $subject) {
            $mail->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->to($email)->subject($subject);
        });
    }
}