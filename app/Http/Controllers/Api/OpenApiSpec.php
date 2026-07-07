<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Info(
 *     title="Nebula Hub API",
 *     version="2.0.0",
 *     description="REST API for Nebula Hub (LinkStack fork). Authenticate via POST /api/login, then send the returned token as `Authorization: Bearer <token>` on every other request."
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="token"
 * )
 *
 * @OA\Schema(
 *     schema="Message",
 *     @OA\Property(property="message", type="string")
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     @OA\Property(property="id", type="integer", example=123456),
 *     @OA\Property(property="name", type="string", example="janedoe"),
 *     @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
 *     @OA\Property(property="littlelink_name", type="string", nullable=true),
 *     @OA\Property(property="littlelink_description", type="string", nullable=true),
 *     @OA\Property(property="role", type="string", enum={"user","vip","admin"}),
 *     @OA\Property(property="block", type="string", enum={"yes","no"}),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Link",
 *     @OA\Property(property="id", type="integer", example=123456789),
 *     @OA\Property(property="link", type="string", example="https://example.com"),
 *     @OA\Property(property="title", type="string", example="My website"),
 *     @OA\Property(property="type", type="string", nullable=true, example="link"),
 *     @OA\Property(property="type_params", type="string", nullable=true),
 *     @OA\Property(property="order", type="integer"),
 *     @OA\Property(property="click_number", type="integer"),
 *     @OA\Property(property="up_link", type="string", enum={"yes","no"}),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="button_id", type="integer", nullable=true),
 *     @OA\Property(property="custom_css", type="string"),
 *     @OA\Property(property="custom_icon", type="string")
 * )
 *
 * @OA\Schema(
 *     schema="Button",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="alt", type="string", nullable=true),
 *     @OA\Property(property="exclude", type="boolean"),
 *     @OA\Property(property="group", type="string", nullable=true),
 *     @OA\Property(property="mb", type="boolean")
 * )
 *
 * @OA\Schema(
 *     schema="Page",
 *     @OA\Property(property="terms", type="string", nullable=true),
 *     @OA\Property(property="privacy", type="string", nullable=true),
 *     @OA\Property(property="contact", type="string", nullable=true),
 *     @OA\Property(property="register", type="string", nullable=true),
 *     @OA\Property(property="home_message", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="LinkType",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="typename", type="string", example="link"),
 *     @OA\Property(property="title", type="string", nullable=true),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="icon", type="string", nullable=true),
 *     @OA\Property(property="custom_html", type="boolean"),
 *     @OA\Property(property="ignore_container", type="boolean"),
 *     @OA\Property(property="include_libraries", type="array", @OA\Items(type="string"))
 * )
 */
class OpenApiSpec
{
    //
}
