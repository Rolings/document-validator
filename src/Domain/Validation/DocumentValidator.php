<?php

declare(strict_types=1);

namespace App\Domain\Validation;

use App\Domain\Document\Document;
use App\Domain\Validation\Contracts\ValidationRule;

final class DocumentValidator
{

    public function validate(Document $document, array $rules): ValidationResult
    {
        if (empty($rules)) {
            return ValidationResult::pass();
        }

        $results = array_map(
            fn(ValidationRule $rule) => $rule->validate($document),
            $rules
        );

        return ValidationResult::merge(...$results);
    }
}
