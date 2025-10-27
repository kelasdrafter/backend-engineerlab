<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EnrollmentResultExport implements WithMultipleSheets
{
    use Exportable;

    private $data;

    public function __construct($data)
    {
        // Contoh passing data
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Lewati data ke EnrollmentResultSheet
        $sheets[] = new EnrollmentResultSheet($this->data);

        return $sheets;
    }
}
