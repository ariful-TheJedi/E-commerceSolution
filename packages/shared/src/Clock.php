<?php

namespace Shared;

use DateTimeImmutable;

interface Clock
{
    public function now(): DateTimeImmutable;
}
