<?php

namespace App\Http\Controllers;

use App\Http\Resources\FileResource;
use App\Models\File;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new File)->getFillable();
            $data = QueryBuilder::for(File::class)
                ->allowedFilters([...$allowedColumns, AllowedFilter::exact('is_active')])
                ->allowedSorts([...$allowedColumns, 'created_at', 'updated_at'])
                ->paginate($perPage);

            return $this->responseSuccess('Get Data Succcessfully', $data, 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
            $fileReceived = $receiver->receive();

            // Cek apakah file sudah terkumpul (chunk terakhir)
            if ($fileReceived->isFinished()) {
                // File telah selesai diupload (semua chunk telah diterima)
                $file = $fileReceived->getFile();

                // Simpan file ke disk AWS S3
                $path = $file->store('public/files', ['disk' => 's3', 'visibility' => 'public']);

                // Menghasilkan data
                $data = $this->generateData($request);
                $data['path'] = $path;
                $data['mime'] = Storage::disk('s3')->mimeType($path);
                $data['size_in_bytes'] = Storage::disk('s3')->size($path);

                // Membuat record di database
                $data = File::create($data);

                // Hapus file sementara setelah digunakan
                unlink($file->getPathname());

                return $this->responseSuccess('File uploaded successfully', new FileResource($data), 200);
            }

            // Jika masih menerima chunk, kirim response sementara
            return $this->responseSuccess('Continue sending chunks', [
                'status' => 'success',
                'done' => false,
            ], 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        return $this->responseSuccess('Get Data Succcessfully', new FileResource($file), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        $file->fill($this->generateData($request));
        $file->save();

        return $this->responseSuccess('Update Data Succcessfully', $file, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        try {
            if (Storage::disk('s3')->exists($file->path)) {
                Storage::disk('s3')->delete($file->path);
            }
        } catch (Exception $exception) {}
        
        $file->delete();
        return $this->responseSuccess('Delete Data Succcessfully', $file, 200);
    }

    public function generateData($request)
    {
        return [
            'path' => $request->path,
            'mime' => $request->mime,
            'size_in_bytes' => $request->size_in_bytes,
        ];
    }
}
