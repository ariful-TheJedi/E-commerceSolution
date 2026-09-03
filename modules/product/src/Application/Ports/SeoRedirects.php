<?php

namespace Modules\Product\Application\Ports;

/**
 * Records old product paths when SEO slugs change. The adapter owns storage.
 */
interface SeoRedirects
{
    public function record(string $fromPath, string $toPath): void;
}