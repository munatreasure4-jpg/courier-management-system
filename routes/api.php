<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/shipments/track/{trackingNumber}', [ShipmentController::class, 'track']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Shipment routes
    Route::apiResource('shipments', ShipmentController::class);
    Route::post('/shipments/{shipment}/tracking', [ShipmentController::class, 'addTrackingUpdate']);

    // Delivery routes
    Route::apiResource('deliveries', DeliveryController::class);
    Route::get('/deliveries/{delivery}/location', [DeliveryController::class, 'getCurrentLocation']);
    Route::post('/deliveries/{delivery}/track-location', [DeliveryController::class, 'trackLocation']);
    Route::get('/deliveries/{delivery}/tracking-history', [DeliveryController::class, 'getTrackingHistory']);
    Route::put('/deliveries/{delivery}/status', [DeliveryController::class, 'updateStatus']);

    // Payment routes
    Route::apiResource('payments', PaymentController::class, ['only' => ['index', 'store', 'show']]);
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund']);
    Route::get('/payments/statistics', [PaymentController::class, 'statistics']);

    // Dashboard routes
    Route::get('/dashboard/statistics', [DashboardController::class, 'statistics']);
    Route::get('/dashboard/charts', [DashboardController::class, 'charts']);
});
