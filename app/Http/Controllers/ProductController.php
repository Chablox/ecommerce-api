<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        path: '/api/products',
        operationId: 'getProducts',
        tags: ['Products'],
        summary: 'Listar productos',
        description: 'Obtiene todos los productos activos disponibles.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de productos obtenida correctamente'
            )
        ]
    )]
    public function index()
    {
        $products = Product::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    }

    #[OA\Post(
        path: '/api/products',
        operationId: 'createProduct',
        tags: ['Products'],
        summary: 'Crear producto',
        description: 'Crea un nuevo producto. Requiere autenticación JWT.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'price', 'stock'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Laptop Pro'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Laptop profesional de alto rendimiento'
                    ),
                    new OA\Property(
                        property: 'price',
                        type: 'number',
                        format: 'float',
                        example: 899.99
                    ),
                    new OA\Property(
                        property: 'stock',
                        type: 'integer',
                        example: 10
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        example: true
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Producto creado correctamente'
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validación'
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado'
            ),
        ]
    )]
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Producto creado correctamente',
            'product' => $product,
        ], 201);
    }

    #[OA\Get(
        path: '/api/products/{id}',
        operationId: 'getProduct',
        tags: ['Products'],
        summary: 'Obtener producto',
        description: 'Obtiene un producto activo por su ID.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID del producto',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Producto obtenido correctamente'
            ),
            new OA\Response(
                response: 404,
                description: 'Producto no encontrado'
            ),
        ]
    )]
    public function show(string $id)
    {
        $product = Product::find($id);

        if (!$product || !$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

    #[OA\Put(
        path: '/api/products/{id}',
        operationId: 'updateProduct',
        tags: ['Products'],
        summary: 'Actualizar producto',
        description: 'Actualiza un producto existente. Requiere autenticación JWT.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID del producto',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Laptop Pro Actualizada'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Descripción actualizada'
                    ),
                    new OA\Property(
                        property: 'price',
                        type: 'number',
                        format: 'float',
                        example: 999.99
                    ),
                    new OA\Property(
                        property: 'stock',
                        type: 'integer',
                        example: 15
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        example: true
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Producto actualizado correctamente'
            ),
            new OA\Response(
                response: 404,
                description: 'Producto no encontrado'
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validación'
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado'
            ),
        ]
    )]
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado',
            ], 404);
        }

        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado correctamente',
            'product' => $product->fresh(),
        ]);
    }

    #[OA\Delete(
        path: '/api/products/{id}',
        operationId: 'deleteProduct',
        tags: ['Products'],
        summary: 'Eliminar producto',
        description: 'Realiza una eliminación lógica del producto. Requiere autenticación JWT.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID del producto',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Producto eliminado correctamente'
            ),
            new OA\Response(
                response: 404,
                description: 'Producto no encontrado'
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado'
            ),
        ]
    )]
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado',
            ], 404);
        }

        $product->update([
            'is_active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado correctamente',
        ]);
    }
}