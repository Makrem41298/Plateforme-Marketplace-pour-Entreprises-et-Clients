<?php

use App\Http\Controllers\AuthClientControlle;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProjetController;
use App\Http\Controllers\AuthEntrepriseControlle;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\ContratController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('auth/client/')->group(function () {
    Route::post('login', [AuthClientControlle::class, 'login']);
    Route::post('register', [AuthClientControlle::class, 'register']);
    Route::middleware('jwtAuth:client')->group(function () {
        Route::post('logout', [AuthClientControlle::class, 'logout']);
        Route::post('refresh', [AuthClientControlle::class, 'refresh']);
        Route::get('me', [AuthClientControlle::class, 'me']);
    });
});
Route::prefix('auth/entreprise/')->group(function () {
    Route::post('login', [AuthEntrepriseControlle::class, 'login']);
    Route::post('register', [AuthEntrepriseControlle::class, 'register']);
    Route::middleware('jwtAuth:entreprise')->group(function () {
        Route::post('logout', [AuthEntrepriseControlle::class, 'logout']);
        Route::post('refresh', [AuthEntrepriseControlle::class, 'refresh']);
        Route::get('me', [AuthEntrepriseControlle::class, 'me']);
    });
});

Route::middleware('jwtAuth:client')->group(function () {
    Route::get('/projets', [ProjetController::class, 'getAllProjetsOrFiltrage']);
    Route::post('/projets', [ProjetController::class, 'createProjet']);
    Route::get('/projets/{slug}', [ProjetController::class, 'getProjet']);
    Route::put('/projets/{slug}', [ProjetController::class, 'updateProjet']);
    Route::delete('/projets/{slug}', [ProjetController::class, 'deleteProjet']);
});


Route::middleware('jwtAuth:entreprise')->group(function () {

    Route::get('/offres', [OffreController::class, 'getAllOffresOrFiltrage']);
    Route::post('/offres', [OffreController::class, 'createOffre']);
    Route::get('/offres/{offre_id}', [OffreController::class, 'getOffre']);
    Route::put('/offres/{offre_id}', [OffreController::class, 'updateOffre']);
    Route::delete('/offres/{offre_id}', [OffreController::class, 'deleteOffre']);
});


Route::middleware(['jwtAuth:entreprise,client'])->group(function () {
    Route::get('/contrats', [ContratController::class, 'getAllContractOrFiltrage']);
    Route::get('/contrats/{reference}', [ContratController::class, 'getContrat']);
    Route::put('/contrats/{reference}', [ContratController::class, 'updateContrat']);
});
Route::middleware(['jwtAuth:entreprise'])->group(function () {
    Route::post('/contrats', [ContratController::class, 'createContrat']);
});
