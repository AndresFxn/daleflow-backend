<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->string('wompi_transaction_id')->nullable()->after('estado_pago');
            $table->string('wompi_reference')->nullable()->after('wompi_transaction_id');
            $table->integer('monto_pagado')->nullable()->after('wompi_reference'); // en centavos
            $table->string('tipo_entrada')->nullable()->after('monto_pagado'); // preventa o final
            $table->timestamp('pagado_en')->nullable()->after('tipo_entrada');
        });
    }

    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropColumn([
                'wompi_transaction_id',
                'wompi_reference',
                'monto_pagado',
                'tipo_entrada',
                'pagado_en',
            ]);
        });
    }
};
