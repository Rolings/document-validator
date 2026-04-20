<?php

declare(strict_types=1);

namespace App\Domain\Validation\Rules;

use App\Domain\Document\Document;
use App\Domain\Validation\Contracts\ValidationRule;
use App\Domain\Validation\ValidationResult;

final class RequiredMetadataFieldsRule implements ValidationRule
{
    public function __construct(
        private readonly array $requiredFields,
    )
    {
    }

    public function validate(Document $document): ValidationResult
    {
        $missingFields = array_filter(
            $this->requiredFields,
            fn(string $field) => !array_key_exists($field, $document->metadata)
        );

        if (!empty($missingFields)) {
            return ValidationResult::fail(
                sprintf(
                    'Document is missing required metadata fields: %s.',
                    implode(', ', $missingFields)
                )
            );
        }

        return ValidationResult::pass();
    }

    public function getName(): string
    {
        return 'required_metadata_fields';
    }
}
