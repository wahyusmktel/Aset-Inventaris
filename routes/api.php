<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', function (Request $request) {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Kredensial tidak valid'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user(),
        ]);
    });

    Route::middleware('auth:api')->group(function () {
        Route::get('me', function () {
            return response()->json(auth('api')->user());
        });

        Route::post('logout', function () {
            auth('api')->logout();
            return response()->json(['message' => 'Berhasil keluar']);
        });

        Route::post('refresh', function () {
            return response()->json([
                'access_token' => auth('api')->refresh(),
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ]);
        });
    });
});
