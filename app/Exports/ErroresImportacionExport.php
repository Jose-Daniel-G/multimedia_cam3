<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ErroresImportacionExport implements FromCollection, WithHeadings
{
    protected $errores;

    public function __construct(array $errores)
    {
        $this->errores = $errores;
    }

    public function collection()
    {
        return collect($this->errores);
    }

    public function headings(): array
    {
        return ['Fila', 'Campo', 'Error'];
    }
}
