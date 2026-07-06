<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const VISIBLE_FIELDS = [
        'id', 'name', 'email', 'littlelink_name', 'littlelink_description',
        'role', 'block', 'email_verified_at', 'created_at', 'updated_at',
    ];

    public function index()
    {
        return User::select(self::VISIBLE_FIELDS)->get();
    }

    public function show(User $user)
    {
        return $user->only(self::VISIBLE_FIELDS);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:users,name',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'littlelink_name' => 'nullable|string|unique:users,littlelink_name',
            'littlelink_description' => 'nullable|string',
            'role' => ['nullable', Rule::in(['user', 'vip', 'admin'])],
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json($user->only(self::VISIBLE_FIELDS), 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', Rule::unique('users', 'name')->ignore($user->id)],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'littlelink_name' => ['nullable', 'string', Rule::unique('users', 'littlelink_name')->ignore($user->id)],
            'littlelink_description' => 'nullable|string',
            'role' => ['sometimes', Rule::in(['user', 'vip', 'admin'])],
            'block' => ['sometimes', Rule::in(['yes', 'no'])],
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json($user->only(self::VISIBLE_FIELDS));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }
}
