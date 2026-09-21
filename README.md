# API de E-commerce Segura

API RESTful para una plataforma de comercio electrónico desarrollada con Laravel, MySQL y autenticación JWT. El proyecto incluye gestión de productos, órdenes, pagos mediante Stripe y documentación interactiva con Swagger/OpenAPI.

## Tecnologías utilizadas

* PHP 8.3+
* Laravel 13
* MySQL
* JWT Authentication
* Stripe PHP SDK
* Swagger/OpenAPI
* L5-Swagger
* Composer

## Funcionalidades

### Autenticación

* Registro de usuarios.
* Inicio de sesión mediante JWT.
* Consulta del usuario autenticado.
* Cierre de sesión.
* Protección de rutas mediante Bearer Token.

### Productos

* Listado público de productos activos.
* Consulta de un producto.
* Creación de productos.
* Actualización de productos.
* Eliminación lógica de productos.
* Validación de datos mediante Form Requests.

### Órdenes

* Creación de órdenes para usuarios autenticados.
* Verificación de existencia y disponibilidad de productos.
* Validación del stock.
* Descuento automático del stock.
* Cálculo automático del total.
* Historial de órdenes del usuario.
* Consulta individual de órdenes.

### Pagos

* Integración con Stripe mediante PaymentIntent.
* Asociación del pago con una orden.
* Validación de propiedad de la orden.
* Prevención de pagos duplicados exitosos.
* Generación del `client_secret` para completar el pago desde el cliente.

> Para ejecutar pagos reales o pruebas con Stripe se necesitan credenciales válidas de una cuenta Stripe elegible. Las claves no deben publicarse en el repositorio.

### Manejo de errores

La API utiliza respuestas JSON consistentes para:

* Errores de validación.
* Recursos no encontrados.
* Errores de autenticación.
* Errores HTTP.
* Errores internos del servidor.

## Estructura principal

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── OrderController.php
│   │   ├── PaymentController.php
│   │   └── ProductController.php
│   └── Requests/
│       ├── StoreOrderRequest.php
│       ├── StorePaymentRequest.php
│       ├── StoreProductRequest.php
│       └── UpdateProductRequest.php
├── Models/
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Payment.php
│   ├── Product.php
│   └── User.php
└── Swagger/
    └── OpenApi.php

database/
├── migrations/
└── seeders/
    ├── DatabaseSeeder.php
    └── ProductSeeder.php

routes/
└── api.php
```

## Instalación

### 1. Clonar el proyecto

```bash
git clone URL_DEL_REPOSITORIO
cd ecommerce-api
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar el archivo `.env`

Copiar `.env.example` como `.env`.

En Windows:

```bash
copy .env.example .env
```

Configurar la conexión MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3308
DB_DATABASE=ecommerce_api
DB_USERNAME=root
DB_PASSWORD=
```

También configurar las variables de JWT y Stripe:

```env
JWT_SECRET=
JWT_ALGO=HS256

STRIPE_KEY=
STRIPE_SECRET=
```

### 4. Generar la clave de Laravel

```bash
php artisan key:generate
```

### 5. Generar la clave JWT

```bash
php artisan jwt:secret
```

### 6. Ejecutar las migraciones

```bash
php artisan migrate
```

### 7. Ejecutar los seeders

```bash
php artisan db:seed
```

El seeder principal crea un usuario de prueba y carga productos de ejemplo.

También puede ejecutarse únicamente el seeder de productos:

```bash
php artisan db:seed --class=ProductSeeder
```

### 8. Generar la documentación Swagger

```bash
php artisan l5-swagger:generate
```

### 9. Iniciar el servidor

```bash
php artisan serve
```

La API estará disponible en:

```text
http://127.0.0.1:8000
```

## Documentación Swagger

La documentación interactiva está disponible en:

```text
http://127.0.0.1:8000/api/documentation
```

Desde Swagger UI se pueden consultar y probar los endpoints documentados.

Para endpoints protegidos:

1. Ejecutar `/api/auth/login`.
2. Copiar el token JWT obtenido.
3. Seleccionar `Authorize` en Swagger.
4. Introducir el token como Bearer Token.
5. Ejecutar los endpoints protegidos.

## Endpoints principales

### Autenticación

| Método | Endpoint             | Acceso  |
| ------ | -------------------- | ------- |
| POST   | `/api/auth/register` | Público |
| POST   | `/api/auth/login`    | Público |
| GET    | `/api/auth/me`       | JWT     |
| POST   | `/api/auth/logout`   | JWT     |

### Productos

| Método | Endpoint             | Acceso  |
| ------ | -------------------- | ------- |
| GET    | `/api/products`      | Público |
| GET    | `/api/products/{id}` | Público |
| POST   | `/api/products`      | JWT     |
| PUT    | `/api/products/{id}` | JWT     |
| DELETE | `/api/products/{id}` | JWT     |

### Órdenes

| Método | Endpoint           | Acceso |
| ------ | ------------------ | ------ |
| GET    | `/api/orders`      | JWT    |
| POST   | `/api/orders`      | JWT    |
| GET    | `/api/orders/{id}` | JWT    |

### Pagos

| Método | Endpoint        | Acceso |
| ------ | --------------- | ------ |
| POST   | `/api/payments` | JWT    |

## Ejemplo de registro

```json
{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
}
```

## Ejemplo de creación de orden

```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        }
    ]
}
```

## Ejemplo de creación de pago

```json
{
    "order_id": 1
}
```

## Autenticación JWT

Los endpoints protegidos requieren el encabezado:

```text
Authorization: Bearer TOKEN_JWT
```

El token se obtiene mediante:

```text
POST /api/auth/login
```

## Base de datos

El proyecto utiliza MySQL y contiene las siguientes entidades principales:

* Users
* Products
* Orders
* Order Items
* Payments

Las relaciones entre las entidades se implementan mediante Eloquent ORM y claves foráneas.

## Seguridad

El proyecto implementa:

* Autenticación mediante JWT.
* Rutas protegidas con middleware `auth:api`.
* Validación mediante Form Requests.
* Contraseñas almacenadas mediante hashing.
* Control de acceso a las órdenes pertenecientes al usuario autenticado.
* Validación de stock antes de crear una orden.
* Protección contra pagos duplicados exitosos.
* Variables sensibles almacenadas en `.env`.
* `.env` excluido del repositorio.
* Respuestas JSON consistentes para errores de API.

## Stripe

La integración utiliza el paquete oficial `stripe/stripe-php`.

El endpoint:

```text
POST /api/payments
```

crea un `PaymentIntent` asociado a una orden del usuario autenticado.

Las claves de Stripe deben configurarse únicamente en `.env`:

```env
STRIPE_KEY=
STRIPE_SECRET=
```

Nunca deben incluirse claves privadas dentro del código fuente o del repositorio público.

## Swagger/OpenAPI

La documentación utiliza:

* L5-Swagger
* OpenAPI
* Atributos PHP de `swagger-php`

Los controladores contienen la documentación de sus respectivos endpoints.

## Seeder de productos

El proyecto incluye productos de prueba:

* Laptop Pro
* Mouse Inalámbrico
* Teclado Mecánico
* Monitor 24 Pulgadas

Los productos se pueden cargar mediante:

```bash
php artisan db:seed
```

## Licencia

Este proyecto fue desarrollado con fines académicos y educativos.
