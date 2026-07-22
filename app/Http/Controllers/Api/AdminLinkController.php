<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * @OA\Tag(name="Admin / User Links", description="Manage another user's links as an admin")
 */
class AdminLinkController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users/{user}/links",
     *     tags={"Admin / User Links"},
     *     summary="List a user's links, ordered",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="List of links",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Link"))
     *     )
     * )
     */
    public function index(Request $request, User $user)
    {
        return $user->links()->orderBy('order')->get();
    }

    /**
     * @OA\Get(
     *     path="/api/users/{user}/links/{link}",
     *     tags={"Admin / User Links"},
     *     summary="Get a single link belonging to the given user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="link", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Link", @OA\JsonContent(ref="#/components/schemas/Link")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function show(Request $request, User $user, Link $link)
    {
        $this->authorizeOwnership($user, $link);

        return $link;
    }

    /**
     * @OA\Post(
     *     path="/api/users/{user}/links",
     *     tags={"Admin / User Links"},
     *     summary="Create a link for the given user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"link","title"},
     *             @OA\Property(property="link", type="string", example="https://example.com"),
     *             @OA\Property(property="title", type="string", example="My website"),
     *             @OA\Property(property="type", type="string", nullable=true, example="link"),
     *             @OA\Property(property="button_id", type="integer", nullable=true),
     *             @OA\Property(property="custom_icon", type="string", nullable=true),
     *             @OA\Property(property="custom_css", type="string", nullable=true),
     *             @OA\Property(property="type_params", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/Link")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function store(Request $request, User $user)
    {
        $data = $this->validated($request);

        $data['user_id'] = $user->id;
        $data['order'] = (int) ($user->links()->max('order')) + 1;

        $link = Link::create($data);

        return response()->json($link, HttpResponse::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{user}/links/{link}",
     *     tags={"Admin / User Links"},
     *     summary="Update a link belonging to the given user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="link", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="link", type="string"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="type", type="string", nullable=true),
     *             @OA\Property(property="button_id", type="integer", nullable=true),
     *             @OA\Property(property="custom_icon", type="string", nullable=true),
     *             @OA\Property(property="custom_css", type="string", nullable=true),
     *             @OA\Property(property="type_params", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/Link")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function update(Request $request, User $user, Link $link)
    {
        $this->authorizeOwnership($user, $link);

        $data = $this->validated($request, sometimes: true);

        $link->update($data);

        return response()->json($link);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{user}/links/{link}",
     *     tags={"Admin / User Links"},
     *     summary="Delete a link belonging to the given user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="link", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Deleted")
     * )
     */
    public function destroy(Request $request, User $user, Link $link)
    {
        $this->authorizeOwnership($user, $link);

        $link->delete();

        return response()->json(null, HttpResponse::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{user}/links/reorder",
     *     tags={"Admin / User Links"},
     *     summary="Reorder the given user's links",
     *     description="Accepts the full, ordered list of the user's link IDs and rewrites their `order` column accordingly.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"link_ids"},
     *             @OA\Property(property="link_ids", type="array", @OA\Items(type="integer"), example={12,5,7})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="New order",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Link"))
     *     ),
     *     @OA\Response(response=422, description="One or more IDs do not belong to the given user", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function reorder(Request $request, User $user)
    {
        $request->validate([
            'link_ids' => 'required|array|min:1',
            'link_ids.*' => 'integer',
        ]);

        $ownLinkIds = $user->links()->pluck('id')->all();
        $invalid = array_diff($request->input('link_ids'), $ownLinkIds);

        if ($invalid) {
            return response()->json([
                'message' => 'Algunos IDs no pertenecen al usuario indicado.',
            ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        foreach (array_values($request->input('link_ids')) as $order => $linkId) {
            Link::where('id', $linkId)->update(['order' => $order]);
        }

        return response()->json($user->links()->orderBy('order')->get());
    }

    private function authorizeOwnership(User $user, Link $link): void
    {
        abort_unless($link->user_id === $user->id, HttpResponse::HTTP_NOT_FOUND);
    }

    private function validated(Request $request, bool $sometimes = false): array
    {
        $rule = fn (string $r) => $sometimes ? "sometimes|$r" : "required|$r";

        $data = $request->validate([
            // Nullable, no required: bloques como heading/spacer/text no llevan
            // link real (ver blocks/{heading,spacer,text}/handler.php en Hub —
            // ninguno incluye la clave 'link' en su $linkData).
            'link' => 'nullable|string',
            'title' => $rule('string'),
            'type' => 'nullable|string',
            'button_id' => ['nullable', 'integer', Rule::exists('buttons', 'id')],
            'custom_icon' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'type_params' => 'nullable|array',
        ]);

        if (array_key_exists('type_params', $data)) {
            $data['type_params'] = json_encode($data['type_params']);
        }

        return $data;
    }
}
