<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class AhspSourceException extends Exception
{
    /**
     * The HTTP status code
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Additional error data
     *
     * @var array
     */
    protected $errorData;

    /**
     * Create a new AHSP source exception instance.
     *
     * @param  string  $message
     * @param  int  $statusCode
     * @param  array  $errorData
     * @return void
     */
    public function __construct(string $message = "AHSP Source Error", int $statusCode = 400, array $errorData = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errorData = $errorData;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'errors' => $this->errorData,
            'status_code' => $this->statusCode,
        ], $this->statusCode);
    }

    /**
     * AHSP source not found exception
     *
     * @param  int|null  $id
     * @return static
     */
    public static function notFound(?int $id = null): static
    {
        $message = $id 
            ? "AHSP Source dengan ID {$id} tidak ditemukan."
            : "AHSP Source tidak ditemukan.";

        return new static($message, 404, [
            'ahsp_source_id' => ['AHSP Source tidak valid.']
        ]);
    }

    /**
     * AHSP source is inactive exception
     *
     * @param  int  $id
     * @return static
     */
    public static function inactive(int $id): static
    {
        return new static(
            "AHSP Source dengan ID {$id} tidak aktif.",
            422,
            ['ahsp_source_id' => ['AHSP Source ini sedang tidak aktif dan tidak dapat digunakan.']]
        );
    }

    /**
     * AHSP source code already exists exception
     *
     * @param  string  $code
     * @return static
     */
    public static function codeExists(string $code): static
    {
        return new static(
            "Kode AHSP Source '{$code}' sudah digunakan.",
            422,
            ['code' => ["Kode '{$code}' sudah digunakan. Silakan gunakan kode lain."]]
        );
    }

    /**
     * AHSP source is being used exception
     *
     * @param  int  $id
     * @param  array  $usage
     * @return static
     */
    public static function inUse(int $id, array $usage = []): static
    {
        $usageDetails = [];
        
        if (isset($usage['master_ahsp']) && $usage['master_ahsp'] > 0) {
            $usageDetails[] = "{$usage['master_ahsp']} Master AHSP";
        }
        
        if (isset($usage['projects']) && $usage['projects'] > 0) {
            $usageDetails[] = "{$usage['projects']} Project";
        }
        
        if (isset($usage['templates']) && $usage['templates'] > 0) {
            $usageDetails[] = "{$usage['templates']} Template";
        }

        $detailMessage = !empty($usageDetails) 
            ? ' Digunakan di: ' . implode(', ', $usageDetails) . '.'
            : '';

        return new static(
            "AHSP Source tidak dapat dihapus karena sedang digunakan.{$detailMessage}",
            422,
            [
                'ahsp_source_id' => [
                    'AHSP Source sedang digunakan dan tidak dapat dihapus. Silakan nonaktifkan saja.'
                ],
                'usage' => $usage
            ]
        );
    }

    /**
     * Unauthorized access to AHSP source exception
     *
     * @param  int  $id
     * @return static
     */
    public static function unauthorized(int $id): static
    {
        return new static(
            "Anda tidak memiliki akses ke AHSP Source ini.",
            403,
            ['ahsp_source_id' => ['Anda tidak memiliki izin untuk mengakses AHSP Source ini.']]
        );
    }

    /**
     * Invalid AHSP source data exception
     *
     * @param  array  $errors
     * @return static
     */
    public static function invalidData(array $errors): static
    {
        return new static(
            "Data AHSP Source tidak valid.",
            422,
            $errors
        );
    }

    /**
     * Cannot change AHSP source exception
     *
     * @param  string  $reason
     * @return static
     */
    public static function cannotChange(string $reason = ''): static
    {
        $message = "AHSP Source tidak dapat diubah.";
        
        if ($reason) {
            $message .= " {$reason}";
        }

        return new static($message, 422, [
            'ahsp_source_id' => [$message]
        ]);
    }

    /**
     * AHSP source creation failed exception
     *
     * @param  string  $reason
     * @return static
     */
    public static function creationFailed(string $reason = ''): static
    {
        $message = "Gagal membuat AHSP Source.";
        
        if ($reason) {
            $message .= " {$reason}";
        }

        return new static($message, 500, [
            'error' => [$message]
        ]);
    }

    /**
     * AHSP source update failed exception
     *
     * @param  string  $reason
     * @return static
     */
    public static function updateFailed(string $reason = ''): static
    {
        $message = "Gagal mengupdate AHSP Source.";
        
        if ($reason) {
            $message .= " {$reason}";
        }

        return new static($message, 500, [
            'error' => [$message]
        ]);
    }

    /**
     * AHSP source deletion failed exception
     *
     * @param  string  $reason
     * @return static
     */
    public static function deletionFailed(string $reason = ''): static
    {
        $message = "Gagal menghapus AHSP Source.";
        
        if ($reason) {
            $message .= " {$reason}";
        }

        return new static($message, 500, [
            'error' => [$message]
        ]);
    }

    /**
     * Get the status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the error data
     *
     * @return array
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }
}