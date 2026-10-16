<?php

namespace App\Http\Controllers;

use App\Services\PapiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Payements;
use Throwable;

class PayementController extends Controller
{
    protected PapiService $papiService;

    public function __construct(PapiService $papiService)
    {
        $this->papiService = $papiService;
    }


    public function createPayment(Request $request)
    {
        $validated = $request->validate([
            'amount'          => 'required|numeric|min:1',
            'clientName'      => 'required|string|max:255',
            'reference'       => 'required|string|max:100',
            'description'     => 'nullable|string',
            'payerEmail'      => 'nullable|email|max:255',
            'payerPhone'      => 'nullable|string|max:30',
            'deliveryType'    => 'nullable|string|max:30',
            'deliveryAddress' => 'nullable|string',
            'notes'            => 'nullable|string',
        ]);
    
        try {
            // Éviter de créer deux commandes avec la même référence.
            $existingOrder = DB::table('commandes')
                ->where('reference', $validated['reference'])
                ->first();
    
            if ($existingOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette référence de commande existe déjà.',
                ], 409);
            }
    
            // 1. Enregistrer la commande avant de contacter Papi.
            $orderId = DB::table('commandes')->insertGetId([
                'reference'        => $validated['reference'],
                'client_name'      => $validated['clientName'],
                'client_email'     => $validated['payerEmail'] ?? null,
                'client_phone'     => $validated['payerPhone'] ?? null,
                'total'            => $validated['amount'],
                'payment_method'   => 'PAPI',
                'status'           => 'EN_ATTENTE_PAIEMENT',
                'delivery_type'    => $validated['deliveryType'] ?? null,
                'delivery_address' => $validated['deliveryAddress'] ?? null,
                'notes'            => $validated['notes']
                    ?? ($validated['description'] ?? null),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
    
            // 2. Demander à Papi de créer le lien de paiement.
            $response = $this->papiService->createPaymentLink($validated);
    
            if ($response->failed()) {
                Log::error('Papi : création du lien échouée', [
                    'order_id' => $orderId,
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);
    
                return response()->json([
                    'success' => false,
                    'orderId' => $orderId,
                    'message' => 'La commande est enregistrée, mais le lien de paiement n’a pas pu être créé.',
                ], 502);
            }
    
            $result = $response->json();
            $papiData = $result['data'] ?? [];
    
            $paymentLink = $papiData['paymentLink'] ?? null;
            $notificationToken = $papiData['notificationToken'] ?? null;
    
            if (
                !is_string($paymentLink) ||
                filter_var($paymentLink, FILTER_VALIDATE_URL) === false ||
                parse_url($paymentLink, PHP_URL_SCHEME) !== 'https' ||
                !is_string($notificationToken) ||
                $notificationToken === ''
            ) {
                Log::error('Papi : réponse de paiement invalide', [
                    'order_id' => $orderId,
                    'response' => $result,
                ]);
    
                return response()->json([
                    'success' => false,
                    'orderId' => $orderId,
                    'message' => 'Commande enregistrée, mais la réponse de paiement Papi est invalide.',
                ], 502);
            }
    
            // 3. Enregistrer le paiement avec le bon order_id.
            DB::table('payements')->insert([
                'order_id'                   => $orderId,
                'merchant_payment_reference' => $validated['reference'],
                'notification_token'         => $notificationToken,
                'payment_status'             => 'PENDING',
                'payment_method'              => null,
                'currency'                    => $papiData['currency'] ?? 'MGA',
                'amount'                      => $validated['amount'],
                'fee'                         => 0,
                'client_name'                 => $validated['clientName'],
                'payer_email'                 => $validated['payerEmail'] ?? null,
                'payer_phone'                 => $validated['payerPhone'] ?? null,
                'description'                 => $validated['description'] ?? null,
                'payment_link'                => $paymentLink,
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);
    
            // 4. Renvoyer le lien à Angular.
            return response()->json([
                'success'   => true,
                'orderId'   => $orderId,
                'reference' => $validated['reference'],
                'paymentLink' => $paymentLink,
                'data'      => $papiData,
            ], 201);
    
        } catch (\Throwable $e) {
            Log::error('Erreur lors de la création de la commande ou du paiement', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de la commande ou du paiement.',
            ], 500);
        }
    }


/**
 * Vérifie la signature envoyée par Papi.
 */
private function verifyPapiSignature(
    string $rawBody,
    ?string $header,
    string $secret
): bool {
    if (empty($header) || empty($secret)) {
        return false;
    }

    $parts = [];

    foreach (explode(',', $header) as $item) {
        $pair = explode('=', trim($item), 2);

        if (count($pair) === 2) {
            $parts[$pair[0]] = $pair[1];
        }
    }

    $timestamp = $parts['t'] ?? '';
    $signature = $parts['v1'] ?? '';

    if (
        !ctype_digit($timestamp) ||
        !preg_match('/\A[0-9a-f]{64}\z/', $signature)
    ) {
        return false;
    }

    // Refuser les notifications datant de plus de 5 minutes
    if (abs(time() - (int) $timestamp) > 300) {
        return false;
    }

    // Conserver exactement le corps brut reçu
    $signedMessage = $timestamp . '.' . $rawBody;

    $expectedSignature = hash_hmac(
        'sha256',
        $signedMessage,
        $secret
    );

    return hash_equals($expectedSignature, $signature);
}


/**
 * Reçoit et traite les notifications Papi.
 */
    public function notification(Request $request)
    {
        // Lire le corps brut AVANT de parser le JSON
        $rawBody = $request->getContent();

        $secret = (string) config('services.papi.webhook_secret');

        // 1. Vérifier la signature avant de faire confiance au contenu
        if (!$this->verifyPapiSignature(
            $rawBody,
            $request->header('X-Papi-Signature'),
            $secret
        )) {
            Log::warning('Notification Papi rejetée : signature invalide.');

            return response()->json([
                'success' => false,
                'message' => 'Signature invalide.',
            ], 401);
        }

        // 2. Parser le JSON seulement après vérification
        $data = json_decode($rawBody, true);

        if (!is_array($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Corps JSON invalide.',
            ], 400);
        }

        $reference = $data['merchantPaymentReference'] ?? null;
        $token = $data['notificationToken'] ?? null;
        $status = strtoupper((string) ($data['paymentStatus'] ?? ''));
        $linkStatus = strtoupper((string) ($data['linkStatus'] ?? ''));

        if (
            !is_string($reference) ||
            $reference === '' ||
            !is_string($token) ||
            $token === '' ||
            !is_numeric($data['amount'] ?? null)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Informations de paiement incomplètes.',
            ], 400);
        }

        try {
            $result = DB::transaction(function () use (
                $data,
                $reference,
                $token,
                $status,
                $linkStatus
            ) {
                // 3. Retrouver le paiement et verrouiller sa ligne
                $payment = DB::table('payements')
                    ->where('merchant_payment_reference', $reference)
                    ->lockForUpdate()
                    ->first();

                if (!$payment) {
                    return 'NOT_FOUND';
                }

                // 4. Vérifier le jeton enregistré lors de la création du lien
                if (
                    empty($payment->notification_token) ||
                    !hash_equals($payment->notification_token, $token)
                ) {
                    return 'INVALID_TOKEN';
                }

                // 5. Vérifier le montant et la devise
                if (
                    round((float) $payment->amount, 2) !==
                    round((float) $data['amount'], 2)
                ) {
                    return 'AMOUNT_MISMATCH';
                }

                if (
                    isset($data['currency']) &&
                    strtoupper($data['currency']) !==
                    strtoupper($payment->currency)
                ) {
                    return 'CURRENCY_MISMATCH';
                }

                // Ne jamais rétrograder un paiement déjà confirmé
                if ($payment->payment_status === 'SUCCESS') {
                    return 'ALREADY_PAID';
                }

                // N'accepter que les statuts connus
                $allowedStatuses = [
                    'SUCCESS',
                    'FAILED',
                    'PENDING',
                ];

                if (!in_array($status, $allowedStatuses, true)) {
                    return 'UNKNOWN_STATUS';
                }

                // Un succès doit aussi correspondre à un lien marqué PAID
                if ($status === 'SUCCESS' && $linkStatus !== 'PAID') {
                    return 'INCONSISTENT_STATUS';
                }

                $updates = [
                    'payment_status' => $status,
                    'payment_method' => $data['paymentMethod'] ?? null,
                    'fee' => $data['fee'] ?? 0,
                    'client_name' => $data['clientName']
                        ?? $payment->client_name,
                    'payer_email' => $data['payerEmail']
                        ?? $payment->payer_email,
                    'payer_phone' => $data['payerPhone']
                        ?? $payment->payer_phone,
                    'description' => $data['description']
                        ?? $payment->description,
                    'message' => $data['message'] ?? null,
                    'notification_received_at' => now(),
                    'updated_at' => now(),
                ];

                // Papi nomme ce champ papiPaymentReference
                if (!empty($data['papiPaymentReference'])) {
                    $updates['payment_reference'] =
                        $data['papiPaymentReference'];
                }

                if ($status === 'SUCCESS') {
                    $updates['paid_at'] = now();
                }

                DB::table('payements')
                    ->where('id', $payment->id)
                    ->update($updates);

                return 'UPDATED';
            });

            if ($result === 'NOT_FOUND') {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement introuvable.',
                ], 404);
            }

            if ($result === 'INVALID_TOKEN') {
                return response()->json([
                    'success' => false,
                    'message' => 'Jeton de notification invalide.',
                ], 403);
            }

            if ($result === 'AMOUNT_MISMATCH' ||
                $result === 'CURRENCY_MISMATCH') {
                Log::warning('Notification Papi : montant ou devise incorrect.');

                return response()->json([
                    'success' => false,
                    'message' => 'Montant ou devise incorrect.',
                ], 400);
            }

            if ($result === 'UNKNOWN_STATUS' ||
                $result === 'INCONSISTENT_STATUS') {
                return response()->json([
                    'success' => false,
                    'message' => 'Statut de paiement invalide.',
                ], 400);
            }

            // Une notification répétée ne doit pas retraiter le paiement
            if ($result === 'ALREADY_PAID') {
                return response()->json([
                    'success' => true,
                    'message' => 'Paiement déjà confirmé.',
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification traitée.',
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Erreur lors du traitement de la notification Papi', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement de la notification.',
            ], 500);
        }
    }
}
