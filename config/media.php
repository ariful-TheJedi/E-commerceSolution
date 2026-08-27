<?php

$root = (string) env('MEDIA_ROOT', 'media');
$absolute = str_starts_with($root, '/')
    || str_starts_with($root, '\\')
    || preg_match('/^[A-Za-z]:[\\\\\\/]/', $root) === 1
        ? $root
        : base_path($root);

$prefix = trim((string) env('MEDIA_PREFIX', 'app'), '/\\');
$urlPrefix = '/'.trim((string) env('MEDIA_URL_PREFIX', '/media'), '/');

if ($prefix === '') {
    throw new RuntimeException('MEDIA_PREFIX must not be empty.');
}

$absolute = rtrim(str_replace('\\', '/', $absolute), '/');

return [

    /*
    | All uploaded files live under one master folder, namespaced by prefix.
    | Disk path:  {MEDIA_ROOT}/{MEDIA_PREFIX}/…
    | Public URL: {MEDIA_URL_PREFIX}/{MEDIA_PREFIX}/…
    |
    | Change MEDIA_PREFIX per project or environment so copies of this host
    | never share a directory.
    */

    'root' => $absolute,
    'prefix' => $prefix,
    'path' => $absolute.'/'.$prefix,
    'url_prefix' => $urlPrefix,
    'url' => rtrim((string) env('APP_URL', 'http://localhost'), '/').$urlPrefix.'/'.$prefix,
    'disk' => 'media',

];
