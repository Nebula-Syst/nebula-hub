<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Button;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * @OA\Tag(name="Buttons", description="Button style presets (read for any authenticated user, write for admins)")
 */
class ButtonController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/buttons",
     *     tags={"Buttons"},
     *     summary="List button style presets",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of button presets",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Button"))
     *     )
     * )
     */
    public function index()
    {
        return Button::all();
    }

    /**
     * @OA\Get(
     *     path="/api/buttons/{button}",
     *     tags={"Buttons"},
     *     summary="Get a single button style preset",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="button", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Button", @OA\JsonContent(ref="#/components/schemas/Button")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function show(Button $button)
    {
        return $button;
    }

    /**
     * @OA\Post(
     *     path="/api/buttons",
     *     tags={"Buttons"},
     *     summary="Create a button style preset (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="alt", type="string", nullable=true),
     *             @OA\Property(property="exclude", type="boolean"),
     *             @OA\Property(property="group", type="string", nullable=true),
     *             @OA\Property(property="mb", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/Button")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'alt' => 'nullable|string',
            'exclude' => 'boolean',
            'group' => 'nullable|string',
            'mb' => 'boolean',
        ]);

        return response()->json(Button::create($data), HttpResponse::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/buttons/{button}",
     *     tags={"Buttons"},
     *     summary="Update a button style preset (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="button", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="alt", type="string", nullable=true),
     *             @OA\Property(property="exclude", type="boolean"),
     *             @OA\Property(property="group", type="string", nullable=true),
     *             @OA\Property(property="mb", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/Button")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function update(Request $request, Button $button)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'alt' => 'nullable|string',
            'exclude' => 'sometimes|boolean',
            'group' => 'nullable|string',
            'mb' => 'sometimes|boolean',
        ]);

        $button->update($data);

        return response()->json($button);
    }

    /**
     * @OA\Delete(
     *     path="/api/buttons/{button}",
     *     tags={"Buttons"},
     *     summary="Delete a button style preset (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="button", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function destroy(Button $button)
    {
        $button->delete();

        return response()->json(null, HttpResponse::HTTP_NO_CONTENT);
    }
}
