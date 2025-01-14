<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class AuthController extends Controller
{
    

    /**
     * Register a new user and issue an authentication token.
     *
     * @param Request $request The incoming request containing user registration data.
     * 
     * @return array An array containing the newly created user and their authentication token.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        $user = User::create($fields);

        $token = $user->createToken($request->name);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
        
    }

    /**
     * Attempt to authenticate a user and issue an authentication token.
     *
     * @param Request $request The incoming request containing user credentials.
     * 
     * @return array An array containing either the authenticated user and their authentication token or an error message.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $fields = $request->validate([            
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

       $user = User::where('email', $request->email)->first();

       if (!$user || !\Hash::check($request->password, $user->password)) {
        return [
            'errors' => [
                'email' => ['The provided credentials are incorrect']
            ]
        ];
       }

       $token = $user->createToken($user->name);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }

    /**
     * Delete the user's tokens.
     *
     * @param Request $request The incoming request containing the authenticated user.
     * 
     * @return array An array containing a success message.
     */
    public function logout(Request $request)
    {
        $user = $request->user()->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }
}
