<?php

declare(strict_types=1);

namespace App\Domain\Validation\Rules;

use App\Domain\Document\Document;
use App\Domain\Validation\Contracts\ValidationRule;
use App\Domain\Validation\ValidationResult;

final class MaxDocumentSizeRule implements ValidationRule
{
    public function __construct(
        private readonly int $maxBytes,
    ) {}

    public function validate(Document $document): ValidationResult
    {
        $size = strlen($document->content);

        if ($size > $this->maxBytes) {
            return ValidationResult::fail(
                sprintf(
                    'Document content exceeds maximum size: %d bytes (limit: %d bytes).',
                    $size,
                    $this->maxBytes
                )
            );
        }

        return ValidationResult::pass();
    }

    public function getName(): string
    {
        return 'max_document_size';
    }
}
