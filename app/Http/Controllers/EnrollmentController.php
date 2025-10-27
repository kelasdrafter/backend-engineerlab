<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Requests\UpdateEnrollmentRequest;
use App\Http\Resources\EnrollmentResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EnrollmentResultExport;
use App\Models\Enrollment;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new Enrollment)->getFillable();
            $data = QueryBuilder::for(Enrollment::class)
                ->allowedFilters([
                    ...$allowedColumns,
                    AllowedFilter::exact('is_active'),
                    AllowedFilter::exact('user_id'),
                    AllowedFilter::exact('course_id'),
                    AllowedFilter::exact('batch_id'),
                    AllowedFilter::callback('email', function ($query, $value) {
                        $query->whereHas('user', function ($query) use ($value) {
                            $query->where('email', 'like', "%{$value}%");
                        });
                    }),
                ])
                ->allowedSorts([...$allowedColumns, 'created_at', 'updated_at'])
                ->defaultSort('-created_at')
                ->paginate($perPage);

            // return $this->responseSuccess('Get Data Succcessfully', $data, 200);
            return $this->responseSuccess(
                'Get Data Successfully',
                [
                    "current_page" => $data->currentPage(),
                    "data" => EnrollmentResource::collection($data),
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
    public function store(StoreEnrollmentRequest $request)
    {
        try {
            $data = $this->generateData($request);
            $data = Enrollment::create($data);

            return $this->responseSuccess('Create Data Succcessfully', new EnrollmentResource($data), 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Enrollment $enrollment)
    {
        return $this->responseSuccess('Get Data Succcessfully', new EnrollmentResource($enrollment), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment)
    {
        $enrollment->fill($this->generateData($request));
        $enrollment->save();

        return $this->responseSuccess('Update Data Succcessfully', $enrollment, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $enrollment, 200);
    }

    public function exportResult($courseId)
    {
        // Ambil data enrollments berdasarkan course_id
        $enrollments = Enrollment::with(['user', 'course', 'batch'])
            ->where('course_id', $courseId)
            ->get();

        // Buat array data untuk diexport ke excel
        $data = [
            ['user name', 'user email', 'user phone', 'course name', 'batch', 'join at']
        ];

        // Tambahkan data dari enrollments ke array
        foreach ($enrollments as $enrollment) {
            $data[] = [
                $enrollment->user->name ?? "",
                $enrollment->user->email ?? "",
                $enrollment->user->phone ?? "",
                $enrollment->course->name ?? "",
                $enrollment->batch->name ?? "",
                $enrollment->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return Excel::download(new EnrollmentResultExport($data), "{$enrollment->course->name}.xlsx");
    }

    /**
     * Mengambil enrollment terakhir dari user yang sedang login.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLatestEnrollment($courseId)
    {
        // Mendapatkan ID user yang sedang login
        $userId = Auth::id();

        // Memastikan user sudah login
        if (!$userId) {
            return $this->responseError("Unauthorized", [], 401);
        }

        // Mengambil enrollment terakhir berdasarkan created_at
        $latestEnrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->orderBy('created_at', 'desc')
            ->first();

        // Jika enrollment tidak ditemukan
        if (!$latestEnrollment) {
            return $this->responseError("No enrollments found", [], 404);
        }

        // Mengembalikan data enrollment dalam format JSON
        return $this->responseSuccess('Create Data Succcessfully', new EnrollmentResource($latestEnrollment), 200);
    }

    /**
    * Mengecek apakah user sudah terdaftar pada course tertentu.
    */
    public function checkCourse(Request $request)
    {
        // Mengambil nilai course_id dan user_id dari query string
        $courseId = $request->query('course_id');
        $userId   = $request->query('user_id');

        // Validasi input
        if (!$courseId || !$userId) {
            return $this->responseError("Parameter course_id dan user_id wajib disertakan.", [], 404);
        }

        // Mencari enrollment berdasarkan course_id dan user_id
        $enrollment = Enrollment::where('course_id', $courseId)
                                ->where('user_id', $userId)
                                ->first();

        // Mengembalikan true jika enrollment ditemukan, false jika tidak
        return $this->responseSuccess(
            'Check Data Succcessfully', 
            [
                'is_register' => (bool) $enrollment
            ], 
            200
        );
    }

    public function generateData($request)
    {
        return [
            'course_id' => $request->course_id,
            'user_id' => $request->user_id,
            'batch_id' => $request->batch_id,
            'transaction_id' => $request->transaction_id,
            'expired_at' => $request->expired_at,
            'is_active' => $request->is_active,
        ];
    }
}
