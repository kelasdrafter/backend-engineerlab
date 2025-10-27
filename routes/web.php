<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login/google', [UserController::class, 'redirectToGoogle']);
Route::get('/login/google/callback', [UserController::class, 'handleGoogleCallback']);

// ===========================
// 📧 EMAIL PREVIEW ROUTES (DEVELOPMENT ONLY)
// ===========================
// ⚠️ IMPORTANT: DELETE THESE ROUTES BEFORE PUSHING TO PRODUCTION!
// These routes are for previewing email design in browser

Route::get('/email-preview/course', function () {
    // Get sample data from database
    $user = App\Models\User::first();
    $course = App\Models\Course::first();
    $batch = App\Models\Batch::first();
    
    // Create dummy transaction for preview
    $transaction = new App\Models\Transaction();
    $transaction->id = 'PREVIEW-' . uniqid();
    $transaction->user_id = $user->id;
    $transaction->course_id = $course->id;
    $transaction->amount = 350000;
    $transaction->status = 'success';
    $transaction->created_at = now();
    
    // Return email preview
    return new App\Mail\CourseEnrollmentMail($user, $course, $batch, $transaction);
});

Route::get('/email-preview/premium', function () {
    // Get sample user from database
    $user = App\Models\User::first();
    
    // Create dummy premium product for preview
    $product = new App\Models\PremiumProduct();
    $product->name = 'Template AutoCAD Premium';
    $product->category = 'Template';
    $product->price = 150000;
    $product->file_url = 'https://kelasdrafter.id/download/template-autocad';
    $product->is_active = 1;
    $product->created_at = now();
    
    // Create dummy transaction for preview
    $transaction = new App\Models\PremiumTransaction();
    $transaction->id = 'PREVIEW-' . uniqid();
    $transaction->user_id = $user->id;
    $transaction->premium_product_id = 1;
    $transaction->amount = 150000;
    $transaction->status = 'success';
    $transaction->created_at = now();
    
    // Return email preview
    return new App\Mail\PremiumPurchaseMail($user, $product, $transaction);
});

// ===========================
// END OF EMAIL PREVIEW ROUTES
// ===========================