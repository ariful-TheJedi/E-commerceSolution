<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Wall test: product_app must not read another schema.
 * Requires Postgres with bootstrap applied:
 *   database/bootstrap/platform/schema.sql
 *   modules/product/database/bootstrap/schema.sql
 */
it('refuses queries into another schema', function () {
    try {
        DB::connection('product')->select('select 1 from platform.outbox limit 1');
        expect(false)->toBeTrue('expected permission denied for schema platform');
    } catch (QueryException $e) {
        expect(strtolower($e->getMessage()))->toContain('permission denied');
    }
})->group('product', 'wall');
