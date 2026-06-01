<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('edad');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('whatsapp');
            $table->string('correo');
            $table->string('como_se_entero');
            $table->text('motivacion')->nullable();
            $table->string('necesidad');
            $table->text('condicion_salud')->nullable();
            $table->boolean('autoriza_fotos')->default(false);
            $table->boolean('recibir_info')->default(false);
            $table->string('estado_pago')->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
