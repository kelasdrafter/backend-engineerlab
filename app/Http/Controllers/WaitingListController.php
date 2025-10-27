<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWaitingListRequest;
use App\Http\Requests\UpdateWaitingListRequest;
use App\Http\Resources\WaitingListResource;
use App\Models\Batch;
use App\Models\WaitingList;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EnrollmentResultExport;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class WaitingListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new WaitingList)->getFillable();
            $data = QueryBuilder::for(WaitingList::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active'), AllowedFilter::exact('course_id'), AllowedFilter::exact('user_id')])
                ->allowedSorts([...$allowedColumns, 'created_at', 'updated_at'])
                ->paginate($perPage);

            // return $this->responseSuccess('Get Data Succcessfully', $data, 200);
            return $this->responseSuccess(
                'Get Data Successfully',
                [
                    "current_page" => $data->currentPage(),
                    "data" => WaitingListResource::collection($data),
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
    public function store(StoreWaitingListRequest $request)
    {
        // Inisialisasi $batch
        $batch = Batch::where('course_id', $request->course_id)
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->first();

        if ($batch == null) {
            return $this->responseError('Saat ini kursus tidak ada batch yang sedang berjalan', [], 400);
        }
        $batch_id = $batch->id;

        try {
            $data = $this->generateData($request, $batch_id);
            $waitingList = WaitingList::create($data);

            return $this->responseSuccess('Create Data Successfully', [
                'waiting_list' => new WaitingListResource($data),
                'batch' => [
                    "id" => $batch_id,
                    "name" => $batch->name,
                    "start_date" => $batch->start_date,
                    "whatsapp_group_url" => $batch->whatsapp_group_url,
                ],
            ], 200);
        } catch (QueryException $exception) {
            if ($exception->errorInfo[1] == 1062) {
                return $this->responseError('Kamu sudah join ke waiting list', [
                    'batch' => $batch ? [
                        "id" => $batch->id,
                        "name" => $batch->name,
                        "start_date" => $batch->start_date,
                        "whatsapp_group_url" => $batch->whatsapp_group_url,
                    ] : null,
                ], 409);
            } else {
                return $this->responseError('Terdapat error pada permintaan', $exception->getMessage(), 500);
            }
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WaitingList $waitingList)
    {
        return $this->responseSuccess('Get Data Succcessfully', new WaitingListResource($waitingList), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWaitingListRequest $request, WaitingList $waitingList)
    {
        $waitingList->fill($this->generateData($request));
        $waitingList->save();

        return $this->responseSuccess('Update Data Succcessfully', new WaitingListResource($waitingList), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WaitingList $waitingList)
    {
        $waitingList->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $waitingList, 200);
    }

    public function exportResult($courseId)
    {
        // Ambil data enrollments berdasarkan course_id
        $enrollments = WaitingList::with(['user', 'course'])
            ->where('course_id', $courseId)
            ->get();

        // Buat array data untuk diexport ke excel
        $data = [
            ['user name', 'user email', 'user phone', 'course name', 'waiting list at']
        ];

        // Tambahkan data dari enrollments ke array
        foreach ($enrollments as $enrollment) {
            $data[] = [
                $enrollment->user->name ?? "",
                $enrollment->user->email ?? "",
                $enrollment->user->phone ?? "",
                $enrollment->course->name ?? "",
                $enrollment->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return Excel::download(new EnrollmentResultExport($data), "{$enrollment->course->name}.xlsx");
    }

    public function generateData($request, $batchId)
    {
        $userId = auth()->id();
        
        return [
            'course_id' => $request->course_id,
            'user_id' => $userId,
            'batch_id' => $batchId,
        ];
    }
}
