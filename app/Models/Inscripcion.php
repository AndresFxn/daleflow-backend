<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'inscripciones';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'edad',
        'fecha_nacimiento',
        'whatsapp',
        'correo',
        'como_se_entero',
        'motivacion',
        'necesidad',
        'condicion_salud',
        'autoriza_fotos',
        'recibir_info',
        'estado_pago',
        'wompi_transaction_id',
        'wompi_reference',
        'monto_pagado',
        'tipo_entrada',
        'pagado_en',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'edad' => 'integer',
        'fecha_nacimiento' => 'date',
        'autoriza_fotos' => 'boolean',
        'recibir_info' => 'boolean',
    ];
}
