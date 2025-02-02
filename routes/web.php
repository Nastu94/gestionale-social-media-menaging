<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PubblicazioneController;
use App\Http\Controllers\MediaPubblicazioneController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermessoClienteController;
use App\Http\Controllers\ChatPubblicazioneController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\AssetClienteController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GuestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotte Pubbliche
|--------------------------------------------------------------------------
*/

/**
 * Rotta per la pagina di benvenuto.
 */
Route::get('/welcome', function () {
    return view('welcome');
});

Route::prefix('guest')->group(function () {
    Route::get('{token}', [GuestController::class, 'showPublications'])->name('guest.publications');
    Route::post('accept/{pubblicazione}', [GuestController::class, 'accept'])->name('guest.accept');
    Route::post('reject/{pubblicazione}', [GuestController::class, 'reject'])->name('guest.reject');
});

Route::get('/file/{path}', [FileController::class, 'showFile'])->where('path', '.*')->name('file.show');

/**
 * Rotta per la dashboard principale.
 */
Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/**
 * Rotta per ottenere i dettagli di una pubblicazione.
 */
Route::get('/get-publication-details/{id}', [DashboardController::class, 'getPublicationDetails'])
    ->middleware(['auth', 'verified'])
    ->name('get.publication.details');

/**
 * Rotta per filtrare le pubblicazioni in base al cliente.
 */
Route::get('/filter-publications/{cliente?}', [DashboardController::class, 'filterByCliente'])
    ->middleware(['auth', 'verified'])
    ->name('filter.publications');

/**
 * Rotta per ottenere i commenti di una pubblicazione.
 */
Route::get('/pubblicazioni/{pubblicazione}/commenti', [ChatPubblicazioneController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('commenti.index');

/**
 * Rotta per salvare un nuovo commento di una pubblicazione.
 */
Route::post('/pubblicazioni/{pubblicazione}/commenti', [ChatPubblicazioneController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('commenti.store');

/*
|--------------------------------------------------------------------------
| Rotte Protette - Profilo Utente
|--------------------------------------------------------------------------
*/

/**
 * Rotte per la gestione del profilo utente.
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Rotte Protette - Amministrazione
|--------------------------------------------------------------------------
*/

/**
 * Rotte per la gestione degli utenti e dei permessi (solo per amministratori).
 */
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::resource('utenti', UserController::class);
    Route::get('clienti/create', [ClienteController::class, 'create'])->name('clienti.create');
    Route::post('clienti', [ClienteController::class, 'store'])->name('clienti.store');
    Route::get('clienti/{cliente}/edit', [ClienteController::class, 'edit'])->name('clienti.edit');
    Route::put('clienti/{cliente}', [ClienteController::class, 'update'])->name('clienti.update');
    Route::delete('clienti/{cliente}', [ClienteController::class, 'destroy'])->name('clienti.destroy');
    Route::resource('permessi', PermessoClienteController::class);
});

/*
|--------------------------------------------------------------------------
| Rotte Protette - Clienti
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    /**
     * Rotte per la gestione dei clienti e dei loro media.
     */
    Route::prefix('clienti')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('clienti.index');
        Route::get('{cliente}', [ClienteController::class, 'show'])->name('clienti.show');
        Route::get('{cliente}/media_pubblicazioni', [MediaPubblicazioneController::class, 'index'])->name('media_pubblicazioni.index');
        Route::get('{cliente}/media_pubblicazioni/create', [MediaPubblicazioneController::class, 'create'])->name('media_pubblicazioni.create');
        Route::post('{cliente}/media_pubblicazioni', [MediaPubblicazioneController::class, 'store'])->name('media_pubblicazioni.store');
        Route::prefix('{cliente}/assets')->name('assets.')->group(function () {
            Route::get('/', [AssetClienteController::class, 'index'])->name('index');
            Route::get('/create', [AssetClienteController::class, 'create'])->name('create');
            Route::post('/', [AssetClienteController::class, 'store'])->name('store');
            Route::get('/{asset}/edit', [AssetClienteController::class, 'edit'])->name('edit');
            Route::put('/{asset}', [AssetClienteController::class, 'update'])->name('update');
            Route::delete('/{asset}', [AssetClienteController::class, 'destroy'])->name('destroy');
        });
    });

    /**
     * Rotte per la gestione dei media delle pubblicazioni.
     */
    Route::prefix('media_pubblicazioni')->group(function () {
        Route::get('{mediaPubblicazione}', [MediaPubblicazioneController::class, 'show'])->name('media_pubblicazioni.show');
        Route::delete('{mediaPubblicazione}', [MediaPubblicazioneController::class, 'destroy'])->name('media_pubblicazioni.destroy');
    });

    /**
     * Rotte per la gestione delle pubblicazioni.
     */
    Route::prefix('pubblicazioni')->group(function () {
        Route::get('/', [PubblicazioneController::class, 'index'])->name('pubblicazioni.index');
        Route::get('clienti/{cliente}/create', [PubblicazioneController::class, 'create'])->name('pubblicazioni.create');
        Route::post('/', [PubblicazioneController::class, 'store'])->name('pubblicazioni.store');
        Route::get('{pubblicazione}', [PubblicazioneController::class, 'show'])->name('pubblicazioni.show');
        Route::get('{pubblicazione}/edit', [PubblicazioneController::class, 'edit'])->name('pubblicazioni.edit');
        Route::put('{pubblicazione}', [PubblicazioneController::class, 'update'])->name('pubblicazioni.update');
        Route::post('{pubblicazione}/commenti', [ChatPubblicazioneController::class, 'store'])->name('commenti.store');
        Route::get('{pubblicazione}/pianifica', [PubblicazioneController::class, 'pianifica'])->name('pubblicazioni.pianifica');
        Route::post('{id}/generate-gpt', [PubblicazioneController::class, 'generateGpt'])
            ->name('pubblicazioni.generate-gpt');
    });

    /**
     * Rotte per la gestione delle autenticazioni tramite social.
     */
    Route::prefix('auth')->group(function () {
        Route::get('facebook', [SocialiteController::class, 'redirectToFacebook'])->name('login.facebook');
        Route::post('facebook/callback', [SocialiteController::class, 'handleFacebookCallback'])->name('login.facebook.callback');
    });

    /**
     * Rotte per la gestione dei file tramite Nextcloud.
     */
    Route::prefix('files')->group(function () {
        Route::get('/', [FileController::class, 'index'])->name('files.index');
        Route::get('/download/{filePath}', [FileController::class, 'download'])->name('files.download');
    });


});

require __DIR__.'/auth.php';
