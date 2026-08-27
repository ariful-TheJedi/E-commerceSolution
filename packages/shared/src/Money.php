<?php

namespace Shared;

final readonly class Money
{
    public function __construct(
        public int $minorUnits,
        public string $currency,
    ) {
        if (strlen($this->currency) !== 3) {
            throw new \InvalidArgumentException('Currency must be a 3-letter code.');
        }
    }
}
