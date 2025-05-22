<?php

use App\Http\Controllers\AuthAdminController;
use App\Http\Controllers\AuthClientController;
use App\Http\Controllers\EntrepriseProfileController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\AuthEntrepriseControlle;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\LitigeController;
use App\Http\Controllers\RetraitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessageController;

Route::get('/email/verify/{id}/{hash}', [AuthClientController::class, 'verifyEmail'])
    ->name('verification.verify');

Route::post('/email/resend', [AuthClientController::class, 'resendVerificationEmail'])->middleware('jwtAuth:client',)
    ->name('verification.resend');
Route::prefix('auth/entreprise/')->group(function () {
    Route::post('login', [AuthEntrepriseControlle::class, 'login']);
    Route::post('register', [AuthEntrepriseControlle::class, 'register']);
    Route::middleware('jwtAuth:entreprise')->group(function () {
        Route::post('logout', [AuthEntrepriseControlle::class, 'logout']);
        Route::post('refresh', [AuthEntrepriseControlle::class, 'refresh']);
        Route::get('me', [AuthEntrepriseControlle::class, 'me']);
    });
});

Route::prefix('auth/client/')->group(function () {
    Route::post('login', [AuthClientController::class, 'login']);
    Route::post('register', [AuthClientController::class, 'register']);
    Route::middleware('jwtAuth:client')->group(function () {
        Route::post('logout', [AuthClientController::class, 'logout']);
        Route::post('refresh', [AuthClientController::class, 'refresh']);
        Route::get('me', [AuthClientController::class, 'me']);
    });
});


Route::get('enterprise/email/verify/{id}/{hash}', [AuthEntrepriseControlle::class, 'verifyEmail']);
Route::post('enterprise/email/resend', [AuthEntrepriseControlle::class, 'resendVerificationEmail'])->middleware('jwtAuth:entreprise',);

Route::prefix('auth/admins/')->group(function () {
    Route::post('login', [AuthAdminController::class, 'login']);
    Route::middleware('jwtAuth:admin')->group(function () {
        Route::post('logout', [AuthAdminController::class, 'logout']);
        Route::post('refresh', [AuthAdminController::class, 'refresh']);
        Route::get('me', [AuthAdminController::class, 'me']);
    });
});



Route::middleware('verifiedEmail')->group(function () {
    Route::get('/dashboard',function(){
        return response()->json(['Welcome Dashboard']);
    });

    Route::middleware('jwtAuth:entreprise,client')->group(function () {
        Route::get('/projets', [ProjetController::class, 'getAllProjetsWithFiltrage']);
        Route::get('/projets/{slug}', [ProjetController::class, 'getProjet']);


    });
    Route::middleware('jwtAuth:client')->group(function () {
        Route::post('/projets', [ProjetController::class, 'createProjet']);
        Route::put('/projets/{slug}', [ProjetController::class, 'updateProjet']);
        Route::delete('/projets/{slug}', [ProjetController::class, 'deleteProjet']);
        Route::get('/project/offers/{slug}', [OffreController::class, 'getOffreClient']);
    });

    Route::middleware('jwtAuth:entreprise')->group(function () {

        Route::get('/offres', [OffreController::class, 'getAllOffresOrFiltrage']);
        Route::post('/offres', [OffreController::class, 'createOffre']);
        Route::get('/offres/{offre_id}', [OffreController::class, 'getOffre']);
        Route::put('/offres/{offre_id}', [OffreController::class, 'updateOffre']);
        Route::delete('/offres/{offre_id}', [OffreController::class, 'deleteOffre']);
    });
    Route::middleware(['jwtAuth:entreprise,client'])->group(function () {
        Route::get('/contrats', [ContratController::class, 'getAllContractWithFiltrage']);
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
            ->middleware('jwtAuth:admin');
        Route::middleware('jwtAuth:entreprise,admin')->group(function () {
            Route::get('/', [RetraitController::class, 'index']);
            Route::get('/{reference}', [RetraitController::class, 'show']);
            Route::delete('/{reference}', [RetraitController::class, 'destroy']);

        });

    });
    Route::prefix('litiges')->group(function () {
        Route::put('/{reference}', [LitigeController::class, 'updateLitige'])->middleware('jwtAuth:admin');
        Route::post('/', [LitigeController::class, 'createLitige'])->middleware('jwtAuth:client,entreprise');
        Route::delete('/{reference}', [LitigeController::class, 'destroy'])->middleware('jwtAuth:client,entreprise');
        Route::middleware('jwtAuth:entreprise,admin,client')->group(function () {
            Route::get('/', [LitigeController::class, 'getAllLitigeWithFiltrage']);
            Route::get('/{reference}', [LitigeController::class, 'getLitige']);
        });

    });
    Route::prefix('admin')->group(function () {
        Route::middleware('jwtAuth:admin')->group(function () {
            Route::get('clients', [UserController::class, 'getAllClients']);
            Route::get('enterprises', [UserController::class, 'getAllEnterprises']);
            Route::get('enterprises/{id}', [UserController::class, 'getEntreprise']);
            Route::get('client/{id}', [UserController::class, 'getClient']);


            Route::put('client/{id}', [UserController::class, 'changeStatusClinet']);
            Route::put('enterprise/{id}', [UserController::class, 'changeStatusEntrprise']);
            Route::delete('client/{id}', [UserController::class, 'destroyUser']);
            Route::delete('enterprise/{id}', [UserController::class, 'destroyEntreprise']);
            Route::put('change_password', [UserController::class, 'changePasswordAdmin']);

        });

    });
    Route::prefix('client')->middleware('jwtAuth:client')->group(function () {
        Route::get('profile', [UserProfileController::class, 'getProfileUser']);
        Route::put('profile', [UserProfileController::class, 'updateProfileUser']);
        Route::put('change_password', [UserProfileController::class, 'changePassword']);

    });
    Route::prefix('enterprise')->middleware('jwtAuth:entreprise')->group(function () {
        Route::get('profile', [EntrepriseProfileController::class, 'getProfileEntreprise']);
        Route::put('profile', [EntrepriseProfileController::class, 'updateProfileEntreprise']);
        Route::put('change_password', [EntrepriseProfileController::class, 'changePassword']);
    });
    Route::middleware('jwtAuth:entreprise,admin,client')->group(function () {
        Route::post('/messages', [MessageController::class, 'send']);
        Route::get('/messages', [MessageController::class, 'index']);
        Route::get('/conversation/{receiverId}/{receiverType}', [MessageController::class, 'conversation']);
        Route::put('/messages/{id}/read', [MessageController::class, 'markAsRead']);
    });

});


