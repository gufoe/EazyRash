<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class SessionController extends Controller
{
    public function __construct()
    {
    }

    public function login(Request $req)
    {
        $user = User::where('email', trim($req->input('email')))->firstOrFail();
        if (!$user->login($req->input('password'))) {
            return error('Invalid login.');
        }
        return success([
            'user'  => $user,
            'token' => $user->generateToken(),
        ]);
    }

    public function logout()
    {
        if (user()) {
            user()->update(['api_token' => null]);
        }
        return success();
    }
}
