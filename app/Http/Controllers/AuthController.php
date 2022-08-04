<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
        // users can access these routes without authentication
    }



    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */


// LOGIN: Logins user in with the email and password and return the token
    public function login(Request $request)

    {
          $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    // REGISTERs the new user with email and password
    public function register(Request $req){
$validator =  Validator::make($req->all(), ['email'=>'required|string|email|unique:users', 'password'=>'required|min:8','name'=>'required|min:3']);
if($validator->fails()){
    return response()->json($validator->errors()->toJson(),401);
}


        User::create(array_merge($req->all(), ['password'=>bcrypt($req->password)]));
        return response()->json(['message'=>'User Registration Successful.']) ;
    }


    // get the currently logged in user
    public function me()
    {
     return Auth::user();

    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */

    //  logouts user with token
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
// refresh the token and returns the new token
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

// gets the token
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }
}
