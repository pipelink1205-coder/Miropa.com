<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\PhoneVerificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FashionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IdentityDocumentController;
use App\Http\Controllers\IdentityVerificationController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\ListingContactController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TradeOfferController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/como-funciona', [LegalController::class, 'howItWorks'])->name('legal.how');
Route::get('/terminos', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacidad', [LegalController::class, 'privacy'])->name('legal.privacy');

// OAuth — invitados
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->whereIn('provider', ['google', 'facebook', 'microsoft', 'apple'])
        ->name('social.redirect');
    Route::match(['get', 'post'], '/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->whereIn('provider', ['google', 'facebook', 'microsoft', 'apple'])
        ->name('social.callback');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// Auth — autenticados (verificaciones)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::post('/email/verificar', [EmailVerificationController::class, 'confirm'])
        ->middleware('throttle:6,1')
        ->name('verification.confirm');

    Route::get('/telefono/verificar', [PhoneVerificationController::class, 'show'])->name('phone.verify.notice');
    Route::post('/telefono/verificar/enviar', [PhoneVerificationController::class, 'send'])
        ->middleware('throttle:3,1')
        ->name('phone.verify.send');
    Route::post('/telefono/verificar', [PhoneVerificationController::class, 'verify'])
        ->middleware('throttle:6,1')
        ->name('phone.verify.confirm');

    Route::get('/cuenta', [AccountController::class, 'index'])->name('account.index');
    Route::get('/cuenta/verificar-identidad', [IdentityVerificationController::class, 'show'])->name('identity.verify.show');
    Route::post('/cuenta/verificar-identidad', [IdentityVerificationController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('identity.verify.store');

    Route::get('/admin/verificaciones/{verification}/documento/{side}', [IdentityDocumentController::class, 'show'])
        ->whereIn('side', ['front', 'back'])
        ->middleware('can:admin')
        ->name('identity.document');
});

// Auth — acciones del marketplace (email + celular verificados)
Route::middleware(['auth', 'verified', 'verified.phone'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('/listings', ListingController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy']);

    Route::get('/mensajes', [MessagesController::class, 'index'])->name('messages.index');
    Route::get('/mensajes/{conversation}', [MessagesController::class, 'show'])->name('messages.show');
    Route::post('/mensajes/{conversation}/messages', [MessagesController::class, 'storeMessage'])->name('messages.send');
    Route::get('/mensajes/{conversation}/messages', [MessagesController::class, 'fetchMessages'])->name('messages.fetch');
    Route::post('/anuncios/{listing:slug}/contactar', ListingContactController::class)->name('listings.contact');
    Route::post('/anuncios/{listing:slug}/favorito', [ListingController::class, 'toggleFavorite'])->name('listings.favorite');

    Route::get('/trueques', [TradeOfferController::class, 'index'])->name('trade-offers.index');
    Route::post('/trueques', [TradeOfferController::class, 'store'])->name('trade-offers.store');
    Route::patch('/trueques/{tradeOffer}/status', [TradeOfferController::class, 'updateStatus'])->name('trade-offers.status');
});

Route::get('/moda/{department}', [FashionController::class, 'department'])
    ->whereIn('department', ['mujer', 'hombre', 'ninos'])
    ->name('fashion.department');
Route::get('/moda/universos/{universe}', [FashionController::class, 'universe'])
    ->name('fashion.universe');

// Rutas públicas
Route::get('/anuncios', SearchController::class)->name('search');
Route::get('/anuncios/{slug}', [ListingController::class, 'show'])->name('listings.show');
Route::get('/u/{username}', [ProfileController::class, 'show'])->name('profile.show');
