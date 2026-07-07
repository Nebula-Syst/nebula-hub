<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(name="Users", description="User management (admin only)")
 */
class UserController extends Controller
{
    private const VISIBLE_FIELDS = [
        'id', 'name', 'email', 'littlelink_name', 'littlelink_description',
        'role', 'block', 'email_verified_at', 'created_at', 'updated_at',
    ];

    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="List all users",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     ),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function index()
    {
        return User::select(self::VISIBLE_FIELDS)->get();
    }

    /**
     * @OA\Get(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Get a single user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function show(User $user)
    {
        return $user->only(self::VISIBLE_FIELDS);
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Create a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="janedoe"),
     *             @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="littlelink_name", type="string", nullable=true),
     *             @OA\Property(property="littlelink_description", type="string", nullable=true),
     *             @OA\Property(property="role", type="string", enum={"user","vip","admin"}, nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Update a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="littlelink_name", type="string", nullable=true),
     *             @OA\Property(property="littlelink_description", type="string", nullable=true),
     *             @OA\Property(property="role", type="string", enum={"user","vip","admin"}),
     *             @OA\Property(property="block", type="string", enum={"yes","no"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Delete a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Deleted")
     * )
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }
}
