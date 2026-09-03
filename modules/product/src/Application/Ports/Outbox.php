<?php

namespace Modules\Product\Application\Ports;

/**
 * Write a published event in the same database transaction as the state change.
 *
 * The real adapter writes platform.outbox. Tests use an in-memory fake.
 * This module does not own that table; it must not add a foreign key to it.
 */
interface Outbox
{
    public function record(object $event): void;
}
