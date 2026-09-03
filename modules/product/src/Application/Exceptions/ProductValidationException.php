<?php

namespace Modules\Product\Application\Exceptions;

use RuntimeException;

/** Application validation failure; transport layers decide its HTTP representation. */
final class ProductValidationException extends RuntimeException
{
    /** @param array<string, list<string>> $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('The product request is not valid.');
    }

    /** @param array<string, string> $errors */
    public static function withMessages(array $errors): self
    {
        return new self(array_map(static fn (string $message): array => [$message], $errors));
    }

    /** @return array<string, list<string>> */
    public function errors(): array
    {
        return $this->errors;
    }
}
