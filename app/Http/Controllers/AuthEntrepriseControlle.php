<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthEntrepriseControlle extends Controller
{
    use apiResponse;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth('entreprise')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
    public function register(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'name' => 'required|string|min:3',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'confirm_password' => 'required|same:password',
            ], [
                'name.required' => 'Veuillez saisir un nom',
                'email.required' => 'Veuillez saisir un email',
                'email.email' => 'Veuillez saisir un email valide',
                'email.unique' => 'Cet email est déjà utilisé',
                'password.required' => 'Veuillez saisir un mot de passe',
                'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
                'confirm_password.required' => 'Veuillez confirmer le mot de passe',
                'confirm_password.same' => 'Les mots de passe ne correspondent pas',
            ]);

            if ($validation->fails()) {
                return $this->apiResponse(
                    'Erreurs de validation',
                    ['errors' => $validation->errors()],
                    422
                );
            }
            DB::beginTransaction();
            $client=Entreprise::create($request->except('confirm_password'));
            DB::commit();
            return $this->apiResponse('Entreprise est creation avec succes', $client,201);


        }catch (\Exception $e){
            DB::rollback();
            return $this->apiResponse('erreur',$e->getMessage(),500);

        }

    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('entreprise')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('entreprise')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('entreprise')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('entreprise')->factory()->getTTL() * 60
        ]);
    }
}

