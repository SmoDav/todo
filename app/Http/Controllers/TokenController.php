<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokenController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(Auth::attempt($request->only(['email', 'password'])), 401);

        $user = Auth::user();

        $token = $user->createToken('Application token');

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
            ]
        ]);
    }
}
