<?php

declare(strict_types=1);

namespace App\Domain\Validation\Rules;

use App\Domain\Document\Document;
use App\Domain\Validation\Contracts\ValidationRule;
use App\Domain\Validation\ValidationResult;

final class NonEmptyContentRule implements ValidationRule
{
    public function validate(Document $document): ValidationResult
    {
        if (trim($document->content) === '') {
            return ValidationResult::fail('Document content must not be empty.');
        }

        return ValidationResult::pass();
    }

    public function getName(): string
    {
        return 'non_empty_content';
    }
}
