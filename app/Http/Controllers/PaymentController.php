<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use OpenApi\Attributes as OA;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    #[OA\Post(
        path: '/api/payments',
        operationId: 'createPayment',
        tags: ['Payments'],
        summary: 'Crear PaymentIntent de Stripe',
        description: 'Crea un PaymentIntent en Stripe para una orden perteneciente al usuario autenticado.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['order_id'],
                properties: [
                    new OA\Property(
                        property: 'order_id',
                        type: 'integer',
                        example: 1
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'PaymentIntent creado correctamente'
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado'
            ),
            new OA\Response(
                response: 404,
                description: 'Orden no encontrada'
            ),
            new OA\Response(
                response: 422,
                description: 'La orden ya fue pagada o tiene un pago exitoso'
            ),
        ]
    )]
    public function store(StorePaymentRequest $request)
    {
        $order = Order::with('items.product')
            ->where('user_id', auth('api')->id())
            ->find($request->order_id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Orden no encontrada',
            ], 404);
        }

        if ($order->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'La orden ya fue pagada',
            ], 422);
        }

        $existingPayment = Payment::where('order_id', $order->id)
            ->where('status', 'succeeded')
            ->first();

        if ($existingPayment) {
            return response()->json([
                'success' => false,
                'message' => 'La orden ya tiene un pago exitoso',
            ], 422);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => (int) round($order->total * 100),
            'currency' => 'usd',
            'metadata' => [
                'order_id' => $order->id,
                'user_id' => auth('api')->id(),
            ],
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'stripe_payment_id' => $paymentIntent->id,
            'amount' => $order->total,
            'currency' => 'usd',
            'status' => $paymentIntent->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PaymentIntent creado correctamente',
            'payment' => $payment,
            'client_secret' => $paymentIntent->client_secret,
        ], 201);
    }
}