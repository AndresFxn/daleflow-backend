<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WompiWebhookController extends Controller
{
    /**
     * Recibe los eventos de Wompi y actualiza el estado de pago.
     *
     * Wompi envía un POST con firma HMAC-SHA256 en el header
     * x-event-checksum para verificar autenticidad.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Wompi webhook recibido', $payload);

        // Verificar firma si está configurada la clave de eventos
        $eventKey = env('WOMPI_EVENTS_KEY');
        if ($eventKey) {
            $checksum = $request->header('x-event-checksum');

            // Wompi firma así: SHA256( transactionId + status + amount + eventKey )
            $transaction = $payload['data']['transaction'] ?? [];
            $toHash = ($transaction['id']             ?? '') .
                      ($transaction['status']          ?? '') .
                      ($transaction['amount_in_cents'] ?? '') .
                      $eventKey;

            $expected = hash('sha256', $toHash);

            if (!hash_equals($expected, (string) $checksum)) {
                Log::warning('Wompi webhook: firma inválida', [
                    'received' => $checksum,
                    'expected' => $expected,
                ]);
                return response()->json(['error' => 'Firma inválida'], 401);
            }
        }

        // Solo procesar eventos de transacción
        $event = $payload['event'] ?? null;
        if ($event !== 'transaction.updated') {
            return response()->json(['status' => 'ignorado'], 200);
        }

        $transaction = $payload['data']['transaction'] ?? null;
        if (!$transaction) {
            return response()->json(['error' => 'Sin datos de transacción'], 400);
        }

        $status    = $transaction['status']         ?? null;
        $reference = $transaction['reference']      ?? null;
        $txId      = $transaction['id']             ?? null;
        $monto     = $transaction['amount_in_cents'] ?? null;

        // Determinar tipo de entrada por monto
        // Preventa: $69.000 = 6900000 centavos | Final: $79.000 = 7900000 centavos
        $tipoEntrada = null;
        if ($monto == 6900000) {
            $tipoEntrada = 'preventa';
        } elseif ($monto == 7900000) {
            $tipoEntrada = 'final';
        }

        // Buscar inscripción por referencia o por el correo en los datos del pagador
        $correo = $transaction['customer_data']['legal_id'] ?? null;
        $email  = $transaction['customer_email'] ?? null;

        // Intentar encontrar la inscripción más reciente sin pago confirmado
        $inscripcion = null;

        if ($email) {
            $inscripcion = Inscripcion::where('correo', $email)
                ->where('estado_pago', 'pendiente')
                ->latest()
                ->first();
        }

        // Si no encontró por email, tomar la más reciente pendiente
        if (!$inscripcion) {
            $inscripcion = Inscripcion::where('estado_pago', 'pendiente')
                ->latest()
                ->first();
        }

        if (!$inscripcion) {
            Log::warning('Wompi webhook: no se encontró inscripción para la transacción', [
                'transaction_id' => $txId,
                'email'          => $email,
            ]);
            return response()->json(['status' => 'inscripcion_no_encontrada'], 200);
        }

        // Actualizar estado según el resultado del pago
        $nuevoEstado = match ($status) {
            'APPROVED' => 'pagado',
            'DECLINED' => 'rechazado',
            'VOIDED'   => 'anulado',
            'ERROR'    => 'error',
            default    => 'pendiente',
        };

        $inscripcion->update([
            'estado_pago'          => $nuevoEstado,
            'wompi_transaction_id' => $txId,
            'wompi_reference'      => $reference,
            'monto_pagado'         => $monto,
            'tipo_entrada'         => $tipoEntrada,
            'pagado_en'            => $status === 'APPROVED' ? now() : null,
        ]);

        Log::info("Inscripción #{$inscripcion->id} actualizada a: {$nuevoEstado}");

        return response()->json(['status' => 'ok', 'estado' => $nuevoEstado], 200);
    }
}
