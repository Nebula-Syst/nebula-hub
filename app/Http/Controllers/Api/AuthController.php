<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas.'], 401);
        }

        if ($user->block === 'yes') {
            return response()->json(['message' => 'Usuario bloqueado.'], 403);
        }

        $token = $user->createToken($request->device_name ?? 'api');

        return response()->json([
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }
}
