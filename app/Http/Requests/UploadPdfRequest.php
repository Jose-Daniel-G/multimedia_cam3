<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\NotificacionesPorAviso;

class UploadPdfRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Permitir la validación
    }

    public function rules()
    {
        return [
            'pdf' => [
                'required',
                'file',
                'mimes:pdf',
                function ($attribute, $value, $fail) {
                    $filename = pathinfo($value->getClientOriginalName(), PATHINFO_FILENAME);

                    // Buscar en la BD si existe el nom_con
                    $exists = NotificacionesPorAviso::where('nom_con', $filename)->exists();

                    if (!$exists) {
                        $fail("El nombre del archivo '{$filename}' no coincide con ningún registro en la base de datos.");
                    }
                }
            ]
        ];
    }
}
