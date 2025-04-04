<?php

use App\Http\Controllers\AuthClientControlle;
use App\Http\Controllers\EntrepriseProfileController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProjetController;
use App\Http\Controllers\AuthEntrepriseControlle;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\LitigeController;
use App\Http\Controllers\RetraitController;
use App\Http\Controllers\Admin\UserController;



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


Route::prefix('retraits')->group(function () {
    Route::post('/', [RetraitController::class, 'store'])
        ->middleware('jwtAuth:entreprise');
    Route::put('/{reference}', [RetraitController::class, 'update'])
        ->middleware('auth:admin');
    Route::middleware('jwtAuth:entreprise,admin')->group(function () {
        Route::get('/', [RetraitController::class, 'index']);
        Route::get('/{reference}', [RetraitController::class, 'show']);
        Route::delete('/{reference}', [RetraitController::class, 'destroy']);

    });

});

Route::prefix('litiges')->group(function () {
    Route::put('/{reference}', [LitigeController::class, 'updateLitige'])->middleware('jwtAuth:admin');
    Route::post('/', [LitigeController::class, 'createLitige'])->middleware('jwtAuth:user,entreprise');
    Route::delete('/{reference}', [LitigeController::class, 'destroy'])->middleware('jwtAuth:user,entreprise');
    Route::middleware('jwtAuth:entreprise,admin,users')->group(function () {
        Route::get('/', [LitigeController::class, 'getAllLitigeOrOrFiltrage']);
        Route::get('/{reference}', [LitigeController::class, 'getLitige'])
            ->middleware('auth:user,entreprise,admin');
    });

});



Route::prefix('admin')->group(function () {
    Route::middleware('jwtAuth:admin')->group(function () {
        Route::get('clients', [UserController::class, 'getAllClients']);
        Route::get('enterprises', [UserController::class, 'getAllEnterprises']);
        Route::get('enterprises/{id}', [UserController::class, 'getEntreprise']);
        Route::get('client/{id}', [UserController::class, 'getClient']);


        Route::patch('client/{id}/status', [UserController::class, 'changeStatusClinet']);
        Route::patch('enterprise/{id}/status', [UserController::class, 'changeStatusEntrprise']);
        Route::delete('client/{id}', [UserController::class, 'destroyUser']);
        Route::delete('enterprise/{id}', [UserController::class, 'destroyEntreprise']);
    });
    Route::prefix('client')->middleware('jwtAuth:client')->group(function () {
        Route::get('profile', [UserProfileController::class, 'getProfileUser']);
        Route::post('profile', [UserProfileController::class, 'updateProfileUser']);
    });
    Route::prefix('enterprise')->middleware('jwtAuth:entreprise')->group(function () {
        Route::get('profile', [EntrepriseProfileController::class, 'getProfileEntreprise']);
        Route::post('profile', [EntrepriseProfileController::class, 'updateProfileEntreprise']);
    });

});

