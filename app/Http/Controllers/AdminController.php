<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Lista todas las inscripciones con filtros opcionales.
     * Protegido por clave secreta en el header.
     */
    public function index(Request $request)
    {
        // Verificar clave de admin
        $adminKey = $request->header('X-Admin-Key');
        if ($adminKey !== env('ADMIN_SECRET_KEY')) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $query = Inscripcion::orderBy('created_at', 'desc');

        // Filtrar por estado de pago
        if ($request->has('estado')) {
            $query->where('estado_pago', $request->estado);
        }

        $inscripciones = $query->get()->map(function ($i) {
            return [
                'id'             => $i->id,
                'nombre'         => $i->nombre,
                'correo'         => $i->correo,
                'whatsapp'       => $i->whatsapp,
                'edad'           => $i->edad,
                'como_se_entero' => $i->como_se_entero,
                'necesidad'      => $i->necesidad,
                'estado_pago'    => $i->estado_pago,
                'tipo_entrada'   => $i->tipo_entrada,
                'monto_pagado'   => $i->monto_pagado ? '$' . number_format($i->monto_pagado / 100, 0, ',', '.') : null,
                'pagado_en'      => $i->pagado_en?->format('d/m/Y H:i'),
                'inscrito_en'    => $i->created_at->format('d/m/Y H:i'),
            ];
        });

        $resumen = [
            'total_inscritos' => Inscripcion::count(),
            'total_pagados'   => Inscripcion::where('estado_pago', 'pagado')->count(),
            'total_pendientes'=> Inscripcion::where('estado_pago', 'pendiente')->count(),
            'preventa'        => Inscripcion::where('tipo_entrada', 'preventa')->where('estado_pago', 'pagado')->count(),
            'precio_final'    => Inscripcion::where('tipo_entrada', 'final')->where('estado_pago', 'pagado')->count(),
        ];

        return response()->json([
            'resumen'        => $resumen,
            'inscripciones'  => $inscripciones,
        ]);
    }
}
