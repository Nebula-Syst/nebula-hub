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
     * VISIBLE_FIELDS + avatar_url, calculado con el mismo helper findAvatar()
     * que usa la vista de autoservicio (el avatar es un fichero en
     * assets/img/{id}_*.ext, no una columna).
     */
    private static function withAvatar(User $user): array
    {
        $data = $user->only(self::VISIBLE_FIELDS);
        $avatar = findAvatar($user->id);
        $data['avatar_url'] = $avatar !== 'error.error' ? url($avatar) : null;

        return $data;
    }

    /**
     * Borra los ficheros de avatar de un usuario leyendo el directorio
     * directamente (scandir), sin pasar por findAvatar()/preloadDirectoryFiles().
     * Esa cache usa una variable static por proceso, así que una segunda
     * llamada dentro de la MISMA request (tras subir/borrar el fichero)
     * devuelve el listado previo a la mutación: un update silenciosamente
     * "no ve" el fichero recién subido, y un delete puede reintentar en
     * bucle infinito un fichero que ya no existe.
     */
    private static function deleteAvatarFiles(int $userId): void
    {
        $dir = base_path('assets/img');
        $pattern = '/^' . preg_quote((string) $userId, '/') . '(_\w+)?\.\w+$/i';

        foreach (scandir($dir) as $file) {
            if (preg_match($pattern, $file)) {
                @unlink($dir . '/' . $file);
            }
        }
    }

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
        return User::all()->map(fn (User $user) => self::withAvatar($user))->values();
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
        return self::withAvatar($user);
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

        return response()->json(self::withAvatar($user), 201);
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

        return response()->json(self::withAvatar($user));
    }

    /**
     * @OA\Post(
     *     path="/api/users/{user}/avatar",
     *     tags={"Users"},
     *     summary="Upload/replace a user's avatar",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(required={"image"}, @OA\Property(property="image", type="string", format="binary"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function updateAvatar(Request $request, User $user)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        self::deleteAvatarFiles($user->id);

        $photo = $request->file('image');
        $fileName = $user->id . '_' . time() . '.' . $photo->extension();
        $photo->move(base_path('assets/img'), $fileName);

        $data = $user->only(self::VISIBLE_FIELDS);
        $data['avatar_url'] = url('assets/img/' . $fileName);

        return response()->json($data);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{user}/avatar",
     *     tags={"Users"},
     *     summary="Remove a user's avatar",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/User"))
     * )
     */
    public function destroyAvatar(User $user)
    {
        self::deleteAvatarFiles($user->id);

        $data = $user->only(self::VISIBLE_FIELDS);
        $data['avatar_url'] = null;

        return response()->json($data);
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
