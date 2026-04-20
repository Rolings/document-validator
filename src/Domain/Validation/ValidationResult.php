<?php

declare(strict_types=1);

namespace App\Domain\Validation;

final class ValidationResult
{

    private array $errors;

    private function __construct(bool $isValid, string ...$errors)
    {
        $this->isValid = $isValid;
        $this->errors = $errors;
    }

    public readonly bool $isValid;

    public static function pass(): self
    {
        return new self(true);
    }

    public static function fail(string ...$errors): self
    {
        return new self(false, ...$errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function merge(self ...$results): self
    {
        $allErrors = array_merge(...array_map(
            fn(self $r) => $r->getErrors(),
            $results
        ));

        return empty($allErrors) ? self::pass() : self::fail(...$allErrors);
    }
}
