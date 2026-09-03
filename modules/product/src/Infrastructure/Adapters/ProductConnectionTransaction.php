<?php

namespace Modules\Product\Infrastructure\Adapters;

use Illuminate\Support\Facades\DB;
use Modules\Product\Application\Ports\Transaction;

/**
 * Runs work on the product connection so listing rows and platform.outbox
 * (written on that same connection) share one transaction.
 */
final class ProductConnectionTransaction implements Transaction
{
    /** @template T @param callable(): T $work @return T */
    public function run(callable $work): mixed
    {
        return DB::connection('product')->transaction(static fn (): mixed => $work());
    }
}
