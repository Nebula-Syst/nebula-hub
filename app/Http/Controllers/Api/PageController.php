<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Pages", description="Site-wide page content: terms, privacy, contact, home message")
 */
class PageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pages",
     *     tags={"Pages"},
     *     summary="Get the site-wide page content",
     *     description="Public endpoint — no authentication required.",
     *     @OA\Response(response=200, description="Page content", @OA\JsonContent(ref="#/components/schemas/Page"))
     * )
     */
    public function show()
    {
        return Page::first() ?? new Page();
    }

    /**
     * @OA\Put(
     *     path="/api/pages",
     *     tags={"Pages"},
     *     summary="Update the site-wide page content (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="terms", type="string", nullable=true),
     *             @OA\Property(property="privacy", type="string", nullable=true),
     *             @OA\Property(property="contact", type="string", nullable=true),
     *             @OA\Property(property="register", type="string", nullable=true),
     *             @OA\Property(property="home_message", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/Page")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'terms' => 'nullable|string',
            'privacy' => 'nullable|string',
            'contact' => 'nullable|string',
            'register' => 'nullable|string',
            'home_message' => 'nullable|string',
        ]);

        $page = Page::first() ?? new Page();
        $page->fill($data);
        $page->save();

        return response()->json($page);
    }
}
