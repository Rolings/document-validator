<?php

declare(strict_types=1);

namespace App\Domain\Validation\Rules;

use App\Domain\Document\Document;
use App\Domain\Validation\Contracts\ValidationRule;
use App\Domain\Validation\ValidationResult;

final class ProhibitedWordsRule implements ValidationRule
{

    public function __construct(
        private readonly array $prohibitedWords,
        private readonly bool $caseSensitive = false,
    ) {}

    public function validate(Document $document): ValidationResult
    {
        $content = $this->caseSensitive
            ? $document->content
            : strtolower($document->content);

        $foundWords = array_filter(
            $this->prohibitedWords,
            fn(string $word) => str_contains(
                $content,
                $this->caseSensitive ? $word : strtolower($word)
            )
        );

        if (!empty($foundWords)) {
            return ValidationResult::fail(
                sprintf(
                    'Document contains prohibited words: %s.',
                    implode(', ', $foundWords)
                )
            );
        }

        return ValidationResult::pass();
    }

    public function getName(): string
    {
        return 'prohibited_words';
    }
}
