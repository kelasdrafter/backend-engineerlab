<?php

namespace App\Http\Controllers;

use Imagick;
use setasign\Fpdi\Fpdi;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use App\Http\Requests\StoreCertificateRequest;
use App\Http\Requests\UpdateCertificateRequest;
use App\Models\Certificate;
use App\Models\UserLesson;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\File as FileModel;
use App\Models\Course;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new Certificate)->getFillable();
            $data = QueryBuilder::for(Certificate::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active'), AllowedFilter::exact('user_id'), AllowedFilter::exact('batch_id')])
                ->allowedSorts([...$allowedColumns, 'created_at', 'updated_at'])
                ->where('user_id', Auth::user()->id)
                ->paginate($perPage);

            return $this->responseSuccess('Get Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCertificateRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Certificate::create($data);

            return $this->responseSuccess('Create Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Certificate $certificate)
    {
        return $this->responseSuccess('Get Data Succcessfully', $certificate, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCertificateRequest $request, Certificate $certificate)
    {
        // Cek apakah file_url baru dan tidak sama dengan yang lama
        if ($request->has('file_url') && $request->file_url != $certificate->file_url) {
            // Hapus file jika file_url di perbarui
            $url = $certificate->file_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {
            }
        }

        // Cek apakah thumbnail_url baru dan tidak sama dengan yang lama
        if ($request->has('thumbnail_url') && $request->thumbnail_url != $certificate->thumbnail_url) {
            // Hapus file jika thumbnail_url di perbarui
            $url = $certificate->thumbnail_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {
            }
        }

        $certificate->fill($this->generateData($request));
        $certificate->save();

        return $this->responseSuccess('Update Data Succcessfully', $certificate, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate)
    {
        try {
            $url = $certificate->file_url;

            // Ekstrak key dari URL
            $baseUrl = rtrim(env('AWS_URL'), '/');
            $key = ltrim(str_replace($baseUrl, '', $url), '/');

            FileModel::deleteByPath($key);
        } catch (Exception $exception) {
        }

        try {
            $url = $certificate->thumbnail_url;

            // Ekstrak key dari URL
            $baseUrl = rtrim(env('AWS_URL'), '/');
            $key = ltrim(str_replace($baseUrl, '', $url), '/');

            FileModel::deleteByPath($key);
        } catch (Exception $exception) {
        }

        $certificate->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $certificate, 200);
    }


    public function validatePrintCertificate($courseId): JsonResponse
    {
        try {
            $userId = auth()->id();

            $course = Course::find($courseId);
            if (!$course) {
                return $this->responseError("Kursus tidak ditemukan", [], 404);
            }

            // VALIDASI 1: Cek enrollment
            $enrollment = Enrollment::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->first();
            if (!$enrollment) {
                return $this->responseError("Kamu tidak terdaftar di kursus", [], 403);
            }

            // HITUNG PROGRESS untuk frontend (hanya lesson dengan require_completion = true)
            $totalLessons = Lesson::where('is_active', true)
                ->where('require_completion', true)
                ->whereHas('chapter', function ($q) use ($courseId) {
                    $q->where('course_id', $courseId)
                      ->where('is_active', true);
                })
                ->count();

            $completedLessons = UserLesson::where('user_id', $userId)
                ->where('is_done', true)
                ->whereHas('lesson', function ($q) use ($courseId) {
                    $q->where('is_active', true)
                      ->where('require_completion', true)
                      ->whereHas('chapter', function ($c) use ($courseId) {
                          $c->where('course_id', $courseId)
                            ->where('is_active', true);
                      });
                })
                ->distinct('lesson_id')
                ->count('lesson_id');

            // VALIDASI 2: Cek certificate existing
            $isDirectClass = $course->is_direct_class;
            $is_user_has_print = false;
            $certificate_id = null;

            if ($isDirectClass) {
                $certificate = Certificate::where('user_id', $userId)
                    ->where('course_id', $courseId)
                    ->first();
            } else {
                $certificate = Certificate::where('user_id', $userId)
                    ->where('batch_id', $enrollment->batch_id)
                    ->first();
            }

            if ($certificate) {
                $is_user_has_print = true;
                $certificate_id = $certificate->id;
            }

            // User eligible jika:
            // 1. Belum pernah print certificate
            // 2. Progress sudah 100% (completed >= total)
            // 3. Ada lesson yang harus diselesaikan (total > 0)
            $is_eligible = !$is_user_has_print 
                        && ($completedLessons >= $totalLessons) 
                        && ($totalLessons > 0);

            $message = $is_eligible 
                ? 'User sudah bisa mencetak sertifikat' 
                : ($is_user_has_print 
                    ? 'User sudah pernah mencetak sertifikat' 
                    : 'User belum menyelesaikan semua materi');

            return $this->responseSuccess($message, [
                'is_eligible' => $is_eligible,
                'is_user_has_print' => $is_user_has_print,
                'certificate_id' => $certificate_id,
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'completion_percentage' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0
            ], 200);

        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    /**
     * Helper function untuk convert bulan ke angka Romawi
     */
    private function getRomanMonth($month)
    {
        $romans = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $romans[$month] ?? 'I';
    }

    public function generateCertificate(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string',
            'course_id' => 'required|exists:courses,id',
        ]);
        $courseId = $request->course_id;
        $participantName = $request->full_name;

        // Memanggil validatePrintCertificate untuk validasi user
        $validationResponse = $this->validatePrintCertificate($courseId);

        // Mengubah response menjadi array
        $validationData = $validationResponse->getData(true);
        if ($validationData['meta']['code'] != 200) {
            return $validationResponse;
        }

        // Periksa jika user tidak memenuhi syarat untuk mencetak sertifikat
        if (!$validationData['data']['is_eligible']) {
            return $this->responseError("User tidak bisa mencetak sertifikat", [], 403);
        }

        // Mendapatkan nama pengguna yang sedang login
        $user = Auth::user();
        $userId = $user->id;

        // Mendapatkan nama kursus menggunakan courseId
        $course = Course::find($courseId);
        if (!$course) {
            return $this->responseError("Kursus tidak ditemukkan", [], 404);
        }
        $courseName = ucwords(strtolower($course->name));  // ← Jadi "The Complete Autocad"
        $isDirectClass = $course->is_direct_class;

        // Mendapatkan tanggal saat ini dengan format yang diinginkan
        $date = Carbon::now()->format('d F Y');
        $currentYear = Carbon::now()->format('Y');
        $currentMonth = Carbon::now()->format('n'); // Numeric month
        $romanMonth = $this->getRomanMonth($currentMonth);

        // Periksa data Enrollment
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
        if (!$enrollment) {
            return $this->responseError("Kamu tidak terdaftar di kursus", [], 403);
        }

        // Periksa data Certificate
        if ($isDirectClass) {
            $certificate = Certificate::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->first();
            if ($certificate) {
                return $this->responseError("Kamu sudah pernah cetak sertifikat", $certificate, 403);
            }

            // Membuat data Certificate
            $certificate = Certificate::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'user_name' => $request->full_name,
            ]);
        } else {
            $certificate = Certificate::where('user_id', $userId)
                ->where('batch_id', $enrollment->batch_id)
                ->first();
            if ($certificate) {
                return $this->responseError("Kamu sudah pernah cetak sertifikat", $certificate, 403);
            }

            // Membuat data Certificate
            $certificate = Certificate::create([
                'user_id' => $userId,
                'batch_id' => $enrollment->batch_id,
                'course_id' => $courseId,
                'user_name' => $request->full_name,
            ]);
        }

        // Generate nomor sertifikat
        // Format: 001/26/KD-CERT/AZNZQSFNHS/IX/2025
        
        // Hitung nomor urut (berdasarkan course, bulan, dan tahun yang sama)
        $count = Certificate::where('course_id', $courseId)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();
        
        $sequenceNumber = str_pad($count, 3, '0', STR_PAD_LEFT); // 001, 002, dst
        
        // Ambil kode unik dari certificate (KelasDrafter-AZNZQSFNHS -> AZNZQSFNHS)
        $uniqueCode = explode('-', $certificate->code)[1] ?? 'XXXXXXXXXX';
        
        // Buat nomor sertifikat lengkap
        $certificateNumber = "{$sequenceNumber}/{$courseId}/KD-CERT/{$uniqueCode}/{$romanMonth}/{$currentYear}";

        // Generate QR Code
        $link = env('APP_FRONTEND_URL') . "/certificate/{$certificate->id}";
        $qrImageFilePath = storage_path("app/qr-image/certificate-qr-{$certificate->id}.png");
        QrCode::format('png')->size(400)->generate($link, $qrImageFilePath);

        // Menambahkan halaman baru
        $pdf = new Fpdi();
        $pdf->AddPage();

        // Mengatur file template PDF
        $pdf->setSourceFile(public_path('certificate/TemplateCertificate.pdf'));
        $tplId = $pdf->importPage(1);
        $pdf->useTemplate($tplId, ['adjustPageSize' => true]);

        // Tambahkan QR Code
        $pdf->Image($qrImageFilePath, 17, 95, 30, 30);

        // Tambahkan Nomor Sertifikat
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(91, 40);
        $pdf->Write(0, "No: {$certificateNumber}");

        // Set font dan warna untuk $participantName
        $pdf->SetFont('Times', 'B', 24);
        $pdf->SetTextColor(11, 106, 192);
        $pdf->SetXY(91, 73);
        $pdf->Write(0, $participantName);

        // Set font dan warna untuk $courseName
        $pdf->SetFont('Times', 'B', 28);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(90, 110);
        $pdf->Write(0, $courseName);

        // Set font dan warna untuk $finishDate
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTextColor(123, 123, 123);
        $pdf->SetXY(91, 117);
        $pdf->Write(0, "Course was completed on {$date}");

        $localPdfPath = storage_path("app/public/certificate-{$certificate->id}.pdf");
        $pdf->Output('F', $localPdfPath);

        // Konversi PDF ke JPG menggunakan Imagick
        $imagick = new Imagick();
        $imagick->readImage($localPdfPath . '[0]');
        $imagick->setImageFormat('jpg');
        $imagick->setResolution(300, 300);

        $jpgFileName = "certificate-{$certificate->id}.jpg";
        $jpgFilePath = storage_path("app/public/{$jpgFileName}");
        $imagick->writeImage($jpgFilePath);
        $imagick->clear();
        $imagick->destroy();

        try {
            $pdfFileName = $participantName . '-' . $courseName . '-' . $certificate->id . '.pdf';
            $pdfPath = Storage::disk('s3')->putFileAs(
                'public/files/certificate',
                new File($localPdfPath),
                $pdfFileName,
                'public'
            );

            $thumbnailPath = Storage::disk('s3')->putFileAs(
                'public/files/certificate-thumbnails',
                new File($jpgFilePath),
                $jpgFileName,
                'public'
            );
        } catch (\Exception $e) {
            return $this->responseError($e, [], 404);
        }

        // Menghapus gambar QR dan file temporary
        unlink($localPdfPath);
        unlink($jpgFilePath);
        Storage::delete('qr-image/certificate-qr-' . $certificate->id . '.png');

        $fileUrl = Storage::disk('s3')->url($pdfPath);
        $certificate->update(['file_url' => $fileUrl]);

        $thumbnailUrl = Storage::disk('s3')->url($thumbnailPath);
        $certificate->update(['thumbnail_url' => $thumbnailUrl]);

        return $this->responseSuccess('Print Certificate Successfully', $certificate, 200);
    }

    public function generateData($request)
    {
        return [
            'user_id' => $request->user_id,
            'batch_id' => $request->batch_id,
            'course_id' => $request->course_id,
            'user_name' => $request->user_name,
            'code' => $request->code,
            'file_url' => $request->file_url,
            'thumbnail_url' => $request->thumbnail_url,
        ];
    }
}