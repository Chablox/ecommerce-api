<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    #[OA\Get(
        path: '/api/orders',
        operationId: 'getOrders',
        tags: ['Orders'],
        summary: 'Obtener historial de órdenes',
        description: 'Obtiene las órdenes pertenecientes al usuario autenticado.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Historial de órdenes obtenido correctamente'
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado'
            ),
        ]
    )]
    public function index()
    {
        $orders = Order::with('items.product')
            ->where('user_id', auth('api')->id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    #[OA\Post(
        path: '/api/orders',
        operationId: 'createOrder',
        tags: ['Orders'],
        summary: 'Crear una orden',
        description: 'Crea una nueva orden, verifica el stock disponible y descuenta las cantidades de los productos.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['items'],
                properties: [
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        minItems: 1,
                        items: new OA\Items(
                            type: 'object',
                            required: ['product_id', 'quantity'],
                            properties: [
                                new OA\Property(
                                    property: 'product_id',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'quantity',
                                    type: 'integer',
                                    minimum: 1,
                                    example: 2
                                ),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Orden creada correctamente'
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado'
            ),
            new OA\Response(
                response: 404,
                description: 'Producto no encontrado'
            ),
            new OA\Response(
                response: 422,
                description: 'Stock insuficiente o error de validación'
            ),
        ]
    )]
    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {

            $total = 0;
            $itemsData = [];

            foreach ($request->validated()['items'] as $item) {

                $product = Product::find($item['product_id']);

                if (!$product || !$product->is_active) {
                    abort(404, 'Producto no encontrado');
                }

                if ($product->stock < $item['quantity']) {
                    abort(422, 'Stock insuficiente para: ' . $product->name);
                }

                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];

                $product->decrement('stock', $item['quantity']);
            }

            $order = Order::create([
                'user_id' => auth('api')->id(),
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($itemsData as $itemData) {
                $order->items()->create($itemData);
            }

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => 'Orden creada correctamente',
            'order' => $order->load('items.product'),
        ], 201);
    }

    #[OA\Get(
        path: '/api/orders/{id}',
        operationId: 'getOrder',
        tags: ['Orders'],
        summary: 'Obtener una orden',
        description: 'Obtiene una orden específica perteneciente al usuario autenticado.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID de la orden',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Orden obtenida correctamente'
            ),
            new OA\Response(
                response: 404,
                description: 'Orden no encontrada'
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado'
            ),
        ]
    )]
    public function show(string $id)
    {
        $order = Order::with('items.product')
            ->where('user_id', auth('api')->id())
            ->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Orden no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }
}