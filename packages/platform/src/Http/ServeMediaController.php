<?php

namespace Platform\Http;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ServeMediaController
{
    public function __invoke(string $prefix, string $path): BinaryFileResponse
    {
        $expected = (string) config('media.prefix');

        if ($prefix !== $expected || $path === '' || str_contains($path, '..')) {
            abort(404);
        }

        $root = (string) config('media.path');
        $absolute = $root.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        $realRoot = realpath($root);
        $realFile = realpath($absolute);

        if ($realRoot === false || $realFile === false || ! is_file($realFile)) {
            abort(404);
        }

        $rootPrefix = strtolower(str_replace('\\', '/', $realRoot)).'/';
        $filePath = strtolower(str_replace('\\', '/', $realFile));

        if (! str_starts_with($filePath, $rootPrefix)) {
            abort(404);
        }

        return response()->file($realFile);
    }
}
