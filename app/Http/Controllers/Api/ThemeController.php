<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use ZipArchive;

/**
 * @OA\Tag(name="Themes", description="Page theme management (admin only)")
 */
class ThemeController extends Controller
{
    /**
     * Mismo formato que usa studio/theme.blade.php: carpetas dentro de
     * themes/, con nombre tomado de "Theme Name:" en su readme.md, más la
     * entrada sintética "default" que no es una carpeta real.
     */
    private static function listThemes(): array
    {
        $themes = [[
            'key'         => 'default',
            'name'        => 'Default Theme',
            'preview_url' => url('assets/linkstack/images/themes/default.png'),
        ]];

        $themesPath = base_path('themes');
        if (!is_dir($themesPath)) {
            return $themes;
        }

        foreach (scandir($themesPath) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $dir = $themesPath . '/' . $entry;
            if (!is_dir($dir)) {
                continue;
            }

            $name = $entry;
            $readme = $dir . '/readme.md';
            if (file_exists($readme) && preg_match('/Theme Name:(.*)/', file_get_contents($readme), $m)) {
                $name = trim($m[1]);
            }

            $themes[] = [
                'key'         => $entry,
                'name'        => $name,
                'preview_url' => file_exists($dir . '/preview.png')
                    ? url('themes/' . $entry . '/preview.png')
                    : url('assets/linkstack/images/themes/no-preview.png'),
            ];
        }

        return $themes;
    }

    /**
     * @OA\Get(
     *     path="/api/themes",
     *     tags={"Themes"},
     *     summary="List installed themes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of themes")
     * )
     */
    public function index()
    {
        return response()->json(self::listThemes());
    }

    /**
     * @OA\Post(
     *     path="/api/themes",
     *     tags={"Themes"},
     *     summary="Upload and install a theme package",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(required={"zip"}, @OA\Property(property="zip", type="string", format="binary"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Installed"),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Message"))
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'zip' => 'required|mimes:zip',
        ]);

        $themesPath = base_path('themes');
        $tmpPath = $themesPath . '/temp.zip';
        $request->file('zip')->move($themesPath, 'temp.zip');

        $zip = new ZipArchive;
        $zip->open($tmpPath);
        $zip->extractTo($themesPath);
        $zip->close();
        unlink($tmpPath);

        // Quita números de versión del nombre de carpeta — mismo
        // postprocesado que UserController::editTheme() en el flujo web.
        $regex = '/[0-9.-]/';
        foreach (array_diff(scandir($themesPath), ['.', '..']) as $basename) {
            $filePath = $themesPath . '/' . $basename;

            if (!is_dir($filePath)) {
                try {
                    File::delete($filePath);
                } catch (\Exception $e) {
                    // fichero suelto (ej. residuo), no es crítico
                }
                continue;
            }

            if (preg_match($regex, $basename)) {
                $newPath = $themesPath . '/' . preg_replace($regex, '', $basename);
                File::copyDirectory($filePath, $newPath);
                File::deleteDirectory($filePath);
            }
        }

        return response()->json(self::listThemes(), 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/themes/{key}",
     *     tags={"Themes"},
     *     summary="Remove an installed theme",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="key", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Deleted"),
     *     @OA\Response(response=422, description="Cannot delete the default theme")
     * )
     */
    public function destroy(string $key)
    {
        abort_if($key === 'default', 422, 'No se puede eliminar el tema por defecto.');

        $dir = base_path('themes') . '/' . basename($key);
        if (File::exists($dir)) {
            File::deleteDirectory($dir);
        }

        return response()->json(self::listThemes());
    }
}
