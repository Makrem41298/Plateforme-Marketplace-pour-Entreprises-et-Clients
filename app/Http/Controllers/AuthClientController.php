<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthClientController extends Controller
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

        $validation=Validator::make($credentials,[
            'email'=>'required|email',
            'password'=>'required'
        ]);
        if($validation->fails()){
            return $this->apiResponse($validation->errors()->first(),null,422);
        }


        if (! $token = auth('client')->attempt($credentials)) {
            return $this->apiResponse('email ou password sont incorrects',null,401);
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
                    $validation->errors()->first(),
                    null,
                    422
                );
            }
            DB::beginTransaction();
            $client = User::create($request->except('confirm_password'));
            event(new Registered($client));




            DB::commit();
            return $this->apiResponse('client est creation avec succes', $client,201);


        }catch (\Exception $e){
            DB::rollback();
            return $this->apiResponse('erreur',$e->getMessage(),500);

        }

    }
    public function verifyEmail( $id, $hash) {
        $user = User::findOrFail($id);
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->apiResponse('Invalid verification link',null,400) ;
        }
        if ($user->hasVerifiedEmail()) {
            return $this->apiResponse('Email  verified',null,400) ;
        }
        $user->markEmailAsVerified();
        return $this->apiResponse('Email has been verify with  successfully',null,200) ;


    }
    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->apiResponse('Email already verified', null, 400);
        }

        $user->sendEmailVerificationNotification();

        return $this->apiResponse('Verification link resent', null, 200);
    }



    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return $this->apiResponse('client récupération avec succes',auth()->user(),200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return $this->apiResponse('logout avec succes',null,200);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}

