<?php

namespace Modules\Product\Application\Ports;

/**
 * One database transaction for this module's connection.
 *
 * Handlers wrap save + outbox.record so both commit or both roll back.
 * Infrastructure implements this with the product connection. Application
 * tests use an immediate (no-op) adapter. No DB facade in this layer.
 */
interface Transaction
{
    /**
     * @template T
     * @param  callable(): T  $work
     * @return T
     */
    public function run(callable $work): mixed;
}
