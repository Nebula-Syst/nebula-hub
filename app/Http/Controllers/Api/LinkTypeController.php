<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LinkType;

/**
 * @OA\Tag(name="Link Types", description="Read-only catalog of link block types available for creating links")
 */
class LinkTypeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/link-types",
     *     tags={"Link Types"},
     *     summary="List available link block types",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of link types",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/LinkType"))
     *     )
     * )
     */
    public function index()
    {
        return LinkType::get();
    }
}
