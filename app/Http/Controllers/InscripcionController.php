<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InscripcionController extends Controller
{
    /**
     * Store a newly created inscription in storage.
     */
    public function store(Request $request)
    {
        // Validación de datos
        $validator = Validator::make($request->all(), [
            'nombre'           => 'required|string|max:255',
            'edad'             => 'required|integer|min:1|max:120',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'whatsapp'         => 'required|string|max:20',
            'correo'           => 'required|email|max:255',
            'como_se_entero'   => 'required|string|max:255',
            'motivacion'       => 'nullable|string',
            'necesidad'        => 'required|string|max:255',
            'condicion_salud'  => 'nullable|string',
            'autoriza_fotos'   => 'nullable',   // acepta true/false/null
            'recibir_info'     => 'nullable',   // acepta true/false/null
        ]);

        // Si la validación falla, retornar errores
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Crear inscripción — convertir booleanos explícitamente
            $data = $validator->validated();
            $data['autoriza_fotos'] = filter_var($request->input('autoriza_fotos', false), FILTER_VALIDATE_BOOLEAN);
            $data['recibir_info']   = filter_var($request->input('recibir_info', false), FILTER_VALIDATE_BOOLEAN);

            $inscripcion = Inscripcion::create($data);

            // Retornar respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Inscripción registrada exitosamente',
                'data' => $inscripcion,
                'payment_link' => env('WOMPI_PAYMENT_LINK')
            ], 201);

        } catch (\Exception $e) {
            // Manejar errores
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la inscripción',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
