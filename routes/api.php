<?php

use App\Http\Controllers\Auth\UserProfileController;
use App\Http\Controllers\EnumerationController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\FreeResourceController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\UserLessonController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\VoucherUserController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WaitingListController;
use App\Http\Controllers\StudentServiceController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\PortofolioImageController;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\DesignedForController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\KeyPointController;
use App\Http\Controllers\PrivilegeController;
use App\Http\Controllers\ScheduleChapterController;
use App\Http\Controllers\LearnCornerController;

// ✅ NEW: Import Insight Lab Controllers
use App\Http\Controllers\InsightController;
use App\Http\Controllers\InsightCommentController;
use App\Http\Controllers\InsightCategoryController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\AdminInsightCategoryController;
use App\Http\Controllers\AdminInsightController;
use App\Http\Controllers\AdminInsightCommentController;

// ✅ NEW: Import Premium Products Controllers
use App\Http\Controllers\PremiumProductController;
use App\Http\Controllers\PremiumProductGalleryController;
use App\Http\Controllers\PremiumProductVideoController;
use App\Http\Controllers\PremiumProductCompatibilityController;
use App\Http\Controllers\PremiumProductQnaController;
use App\Http\Controllers\PremiumProductReviewController;
use App\Http\Controllers\PremiumTransactionController;

// ✅ NEW: Import Live Learning Controllers
use App\Http\Controllers\LiveLearningController;
use App\Http\Controllers\LiveLearningRegistrationController;
use App\Http\Controllers\AdminLiveLearningController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

require __DIR__ . '/auth.php';

Route::post('/webhook-midtrans', [PaymentController::class, 'webhookMidtrans']);
Route::get('/public/portfolio/user/{userId}', [PortfolioController::class, 'getUserPortfolio']);

// Public Routes
Route::prefix('public')->group(function () {
    Route::apiResources([
        'enumerations' => EnumerationController::class,
        'course' => CourseController::class,
        'batch' => BatchController::class,
        'chapter' => ChapterController::class,
        'lesson' => LessonController::class,
        'student-service' => StudentServiceController::class,
        'free-resource' => FreeResourceController::class,
        'review' => ReviewController::class,
        'career' => CareerController::class,
        'certificate' => CertificateController::class,
        'voucher' => VoucherController::class,
        'portfolio' => PortfolioController::class,
        'experience' => ExperienceController::class,
        'licenses' => LicenseController::class,
    ], [
        'only' => ['index', 'show']
    ]);
});

// Learn Corner Public Routes (User bisa akses tanpa login)
Route::prefix('learn-corner')->group(function () {
    Route::get('/', [LearnCornerController::class, 'index']); // List videos
    Route::get('/{slug}', [LearnCornerController::class, 'show']); // Detail video by slug
});

// ✅ NEW: Insight Lab Public Routes (No Auth Required)
Route::prefix('insights')->group(function () {
    Route::get('/', [InsightController::class, 'index']); // List insights
    Route::get('/{slug}', [InsightController::class, 'show']); // Detail insight
    Route::post('/{id}/view', [InsightController::class, 'incrementView']); // Increment view
    Route::get('/{insightId}/comments', [InsightCommentController::class, 'index']); // Get comments (PUBLIC)
});

Route::get('/insight-categories', [InsightCategoryController::class, 'index']); // List categories
Route::get('/leaderboard', [LeaderboardController::class, 'index']); // Top 4 users

// ✅ NEW: Premium Products Public Routes (No Auth Required)
Route::prefix('premium-products')->group(function () {
    Route::get('/', [PremiumProductController::class, 'index']); // List products
    Route::get('/{slug}', [PremiumProductController::class, 'show']); // Product detail by slug
    Route::get('/{productId}/galleries', [PremiumProductGalleryController::class, 'index']); // Get galleries
    Route::get('/{productId}/videos', [PremiumProductVideoController::class, 'index']); // Get videos
    Route::get('/{productId}/compatibilities', [PremiumProductCompatibilityController::class, 'index']); // Get compatibilities
    Route::get('/{productId}/qnas', [PremiumProductQnaController::class, 'index']); // Get Q&As
    Route::get('/{productId}/reviews', [PremiumProductReviewController::class, 'index']); // Get reviews
});

// ✅ NEW: Live Learning Public Routes (No Auth Required)
Route::prefix('live-learnings')->group(function () {
    Route::get('/', [LiveLearningController::class, 'index']); // List live learnings (published only)
    Route::get('/{slug}', [LiveLearningController::class, 'show']); // Detail live learning by slug
    Route::post('/{id}/register', [LiveLearningRegistrationController::class, 'store']); // Register to live learning
});

Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [UserProfileController::class, 'show'])
        ->name('user.show');

    Route::patch('/update/profile', [UserProfileController::class, 'update'])
        ->name('user.update');

    Route::patch('/user/change-password', [UserProfileController::class, 'changePassword'])
        ->name('user.change-password');

    Route::apiResource('files', FileController::class)->middleware('increaseTimeout');

    // ✅ NEW: Insight Lab Protected Routes (Auth Required)
    Route::prefix('insights')->group(function () {
        Route::post('/', [InsightController::class, 'store']); // Create insight
        Route::put('/{id}', [InsightController::class, 'update']); // Update insight
        Route::delete('/{id}', [InsightController::class, 'destroy']); // Delete insight
    });

    // Create comment (auth required)
    Route::post('insights/{insightId}/comments', [InsightCommentController::class, 'store']); // Create comment
    
    Route::prefix('comments')->group(function () {
        Route::put('/{id}', [InsightCommentController::class, 'update']); // Update comment
        Route::delete('/{id}', [InsightCommentController::class, 'destroy']); // Delete comment
    });

    Route::get('/insights/{insightId}/mention-users', [InsightCommentController::class, 'getMentionableUsers']); // Mention autocomplete
    Route::get('/profile/insights', [InsightController::class, 'myInsights']); // My insights
});

Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::get('/check-course', [EnrollmentController::class, 'checkCourse']);
    Route::get('/enrollment/last/{courseId}', [EnrollmentController::class, 'getLatestEnrollment']);
    Route::get('/users/export', [UserController::class, 'exportUsers']);
    Route::get('/transactions/export', [TransactionController::class, 'exportTransactions']);
    Route::post('/enroll/{course}', [CourseController::class, 'enroll'])->name('enroll');
    Route::put('/course/update-about/{course}', [CourseController::class, 'updateAbout'])->name('enroll');
    Route::get('/enrollment/result/{courseId}', [EnrollmentController::class, 'exportResult']);
    Route::get('/waiting-list/result/{courseId}', [WaitingListController::class, 'exportResult']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('enumerations', EnumerationController::class);
    Route::apiResource('course', CourseController::class);
    Route::apiResource('batch', BatchController::class);
    Route::apiResource('chapter', ChapterController::class);
    Route::apiResource('lesson', LessonController::class);
    Route::apiResource('attachment', AttachmentController::class);
    Route::apiResource('student-service', StudentServiceController::class);
    Route::apiResource('free-resource', FreeResourceController::class);
    Route::apiResource('review', ReviewController::class);
    Route::apiResource('career', CareerController::class);
    Route::apiResource('certificate', CertificateController::class);
    Route::apiResource('voucher', VoucherController::class);
    Route::apiResource('voucher-user', VoucherUserController::class);
    Route::apiResource('transaction', TransactionController::class);
    Route::apiResource('payment', PaymentController::class);
    Route::apiResource('enrollments', EnrollmentController::class);
    Route::apiResource('waiting-list', WaitingListController::class);
    Route::apiResource('user-lesson', UserLessonController::class);
    Route::apiResource('portfolio', PortfolioController::class);
    Route::apiResource('experience', ExperienceController::class);
    Route::apiResource('portofolio-image', PortofolioImageController::class);
    Route::apiResource('benefit', BenefitController::class);
    Route::apiResource('designed-for', DesignedForController::class);
    Route::apiResource('goal', GoalController::class);
    Route::apiResource('key-point', KeyPointController::class);
    Route::apiResource('privilege', PrivilegeController::class);
    Route::apiResource('licenses', LicenseController::class);

    Route::get('/schedule-chapter/get-chapter-schedule', [ScheduleChapterController::class, 'getChapterSchedule']);
    Route::apiResource('schedule-chapter', ScheduleChapterController::class);

    Route::post('/user-lesson/mark-as-done', [UserLessonController::class, 'markAsDone']);
    Route::post('/user-lesson/markable', [UserLessonController::class, 'markable']);
    Route::post('/check-voucher', [VoucherController::class, 'checkVoucher']);

    Route::get('/validate-print-certificate/{courseId}', [CertificateController::class, 'validatePrintCertificate']);
    Route::post('/certificate/print', [CertificateController::class, 'generateCertificate']);

    // ✅ NEW: Premium Products User Routes (Auth + Verified Required)
    Route::prefix('premium')->group(function () {
        // Transactions
        Route::get('/transactions', [PremiumTransactionController::class, 'index']); // My transactions
        Route::get('/transactions/{id}', [PremiumTransactionController::class, 'show']); // Transaction detail
        Route::post('/checkout', [PremiumTransactionController::class, 'store']); // Create transaction & checkout
        
        // My Purchases
        Route::get('/my-purchases', [PremiumTransactionController::class, 'myPurchases']); // List my purchases
        
        // ✅ Check Access (Security: Cek apakah user punya hak download)
        Route::get('/check-access/{productId}', [PremiumTransactionController::class, 'checkAccess']); // Check download access
    });

    // ✅ NEW: Premium Products Admin Routes (Admin Only)
    Route::prefix('admin/premium-products')->group(function () {
        // 🎯 ROUTE BARU - Get single product by ID for edit
        Route::get('/{id}', [PremiumProductController::class, 'show']); // Get product by ID
        
        // Products Management
        Route::post('/', [PremiumProductController::class, 'store']); // Create product
        Route::put('/{id}', [PremiumProductController::class, 'update']); // Update product
        Route::delete('/{id}', [PremiumProductController::class, 'destroy']); // Delete product

        // Galleries Management
        Route::post('/{productId}/galleries', [PremiumProductGalleryController::class, 'store']); // Add gallery
        Route::put('/galleries/{id}', [PremiumProductGalleryController::class, 'update']); // Update gallery
        Route::delete('/galleries/{id}', [PremiumProductGalleryController::class, 'destroy']); // Delete gallery

        // Videos Management
        Route::post('/{productId}/videos', [PremiumProductVideoController::class, 'store']); // Add video
        Route::put('/videos/{id}', [PremiumProductVideoController::class, 'update']); // Update video
        Route::delete('/videos/{id}', [PremiumProductVideoController::class, 'destroy']); // Delete video

        // Compatibilities Management
        Route::post('/{productId}/compatibilities', [PremiumProductCompatibilityController::class, 'store']); // Add compatibility
        Route::put('/compatibilities/{id}', [PremiumProductCompatibilityController::class, 'update']); // Update compatibility
        Route::delete('/compatibilities/{id}', [PremiumProductCompatibilityController::class, 'destroy']); // Delete compatibility

        // Q&As Management
        Route::post('/{productId}/qnas', [PremiumProductQnaController::class, 'store']); // Add Q&A
        Route::put('/qnas/{id}', [PremiumProductQnaController::class, 'update']); // Update Q&A
        Route::delete('/qnas/{id}', [PremiumProductQnaController::class, 'destroy']); // Delete Q&A

        // Reviews Management
        Route::post('/{productId}/reviews', [PremiumProductReviewController::class, 'store']); // Add review
        Route::put('/reviews/{id}', [PremiumProductReviewController::class, 'update']); // Update review
        Route::delete('/reviews/{id}', [PremiumProductReviewController::class, 'destroy']); // Delete review
    });

Route::prefix('admin/live-learnings')->group(function () {
    Route::get('/', [AdminLiveLearningController::class, 'index']); // List all live learnings
    Route::post('/', [AdminLiveLearningController::class, 'store']); // Create live learning
    Route::get('/{id}', [AdminLiveLearningController::class, 'show']); // Get live learning by ID
    Route::put('/{id}', [AdminLiveLearningController::class, 'update']); // Update live learning
    Route::delete('/{id}', [AdminLiveLearningController::class, 'destroy']); // Delete live learning (soft delete)
    
    // Registrations Management
    Route::get('/{id}/registrations', [AdminLiveLearningController::class, 'registrations']); // Get registrations for specific live learning
    
    // ✅ TAMBAHAN BARU - Export Registrations
    Route::get('/{id}/registrations/export', [AdminLiveLearningController::class, 'exportRegistrations']); // Export registrations to Excel
});

    // ✅ NEW: Live Learning Registrations Admin Routes
    Route::prefix('admin/live-learning-registrations')->group(function () {
        Route::get('/', [AdminLiveLearningController::class, 'allRegistrations']); // Get all registrations
        Route::delete('/{id}', [AdminLiveLearningController::class, 'destroyRegistration']); // Delete registration
    });

    // Learn Corner Admin Routes (Perlu login & verified)
    Route::prefix('admin/learn-corner')->group(function () {
        Route::post('/upload-thumbnail', [LearnCornerController::class, 'uploadThumbnail']);
        Route::get('/', [LearnCornerController::class, 'adminIndex']); // List all videos (admin)
        Route::post('/', [LearnCornerController::class, 'store']); // Create video
        Route::get('/statistics', [LearnCornerController::class, 'statistics']); // Get statistics
        Route::get('/{id}', [LearnCornerController::class, 'adminShow']); // Show video by ID
        Route::put('/{id}', [LearnCornerController::class, 'update']); // Update video
        Route::delete('/{id}', [LearnCornerController::class, 'destroy']); // Delete video
        Route::patch('/{id}/toggle-active', [LearnCornerController::class, 'toggleActive']); // Toggle active status
    });

    // ✅ NEW: Insight Lab Admin Routes (Admin Only)
    Route::middleware('insight.admin')->prefix('admin')->group(function () {
        
        // Categories Management
        Route::prefix('insight-categories')->group(function () {
            Route::post('/', [AdminInsightCategoryController::class, 'store']); // Create category
            Route::put('/{id}', [AdminInsightCategoryController::class, 'update']); // Update category
            Route::delete('/{id}', [AdminInsightCategoryController::class, 'destroy']); // Delete category
        });

        // Insights Management
        Route::prefix('insights')->group(function () {
            Route::get('/', [AdminInsightController::class, 'index']); // All insights
            Route::put('/{id}', [AdminInsightController::class, 'update']); // Update any insight
            Route::delete('/{id}', [AdminInsightController::class, 'destroy']); // Delete any insight
        });

        // Comments Management
        Route::delete('/comments/{id}', [AdminInsightCommentController::class, 'destroy']); // Delete any comment
    });
});