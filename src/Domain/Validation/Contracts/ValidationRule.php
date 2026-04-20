<?php

declare(strict_types=1);

namespace App\Domain\Validation\Contracts;

use App\Domain\Document\Document;
use App\Domain\Validation\ValidationResult;

interface ValidationRule
{
    public function validate(Document $document): ValidationResult;

    public function getName(): string;
}
