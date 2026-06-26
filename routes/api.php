<?php

use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\IdentityVerificationController;
use App\Http\Controllers\Api\V1\ListingApiController;
use App\Http\Controllers\Api\V1\PhoneVerificationApiController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\TradeOfferController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    // Auth pública
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login')
        ->middleware('throttle:10,1');

    // Listados públicos
    Route::get('/listings', [ListingApiController::class, 'index'])->name('listings.index');
    Route::get('/listings/{slug}', [ListingApiController::class, 'show'])->name('listings.show');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/users/{username}', [UserController::class, 'show'])->name('users.show');

    // Autenticado
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

        Route::post('/auth/phone/send', [PhoneVerificationApiController::class, 'send'])
            ->middleware('throttle:3,1')
            ->name('auth.phone.send');
        Route::post('/auth/phone/verify', [PhoneVerificationApiController::class, 'verify'])
            ->middleware('throttle:6,1')
            ->name('auth.phone.verify');

        Route::middleware('verified.phone')->group(function () {
            // Anuncios
            Route::post('/listings', [ListingApiController::class, 'store'])->name('listings.store');
            Route::put('/listings/{listing}', [ListingApiController::class, 'update'])->name('listings.update');
            Route::delete('/listings/{listing}', [ListingApiController::class, 'destroy'])->name('listings.destroy');
            Route::post('/listings/{listing}/images', [ListingApiController::class, 'addImages'])->name('listings.images');
            Route::post('/listings/{listing}/favorite', [FavoriteController::class, 'toggle'])->name('listings.favorite');
            Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

            // Transacciones
            Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
            Route::patch('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.status');
            Route::post('/transactions/{transaction}/reviews', [ReviewController::class, 'store'])->name('transactions.reviews');

            // Trueque
            Route::get('/trade-offers', [TradeOfferController::class, 'index'])->name('trade-offers.index');
            Route::post('/trade-offers', [TradeOfferController::class, 'store'])->name('trade-offers.store');
            Route::patch('/trade-offers/{tradeOffer}/status', [TradeOfferController::class, 'updateStatus'])->name('trade-offers.status');

            // Reportes
            Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

            // Verificación de identidad
            Route::post('/identity-verifications', [IdentityVerificationController::class, 'store'])->name('identity.store');

            // Admin (solo admins)
            Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
                Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
                Route::patch('/reports/{report}/resolve', [AdminController::class, 'resolveReport'])->name('reports.resolve');
                Route::get('/identity-verifications', [AdminController::class, 'verifications'])->name('verifications');
                Route::patch('/identity-verifications/{verification}/review', [AdminController::class, 'reviewVerification'])->name('verifications.review');
                Route::patch('/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('users.status');
            });

            // Mensajería
            Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
            Route::post('/conversations', [ConversationController::class, 'store'])->name('conversations.store');
            Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'messages'])->name('conversations.messages');
            Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'sendMessage'])->name('conversations.send');
        });
    });
});
