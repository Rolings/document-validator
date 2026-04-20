<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Domain\Document\Document;
use App\Domain\Validation\Rules\ProhibitedWordsRule;
use PHPUnit\Framework\TestCase;

final class ProhibitedWordsRuleTest extends TestCase
{
    private function makeDocument(string $content): Document
    {
        return new Document('doc-1', 'tenant-1', $content);
    }

    public function test_passes_when_no_prohibited_words_found(): void
    {
        $rule = new ProhibitedWordsRule(['confidential', 'secret']);
        $result = $rule->validate($this->makeDocument('This is a normal document.'));

        $this->assertTrue($result->isValid);
    }

    public function test_fails_when_prohibited_word_found(): void
    {
        $rule = new ProhibitedWordsRule(['confidential', 'secret']);
        $result = $rule->validate($this->makeDocument('This is a confidential document.'));

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('confidential', $result->getErrors()[0]);
    }

    public function test_is_case_insensitive_by_default(): void
    {
        $rule = new ProhibitedWordsRule(['secret']);
        $result = $rule->validate($this->makeDocument('This is a SECRET document.'));

        $this->assertFalse($result->isValid);
    }

    public function test_is_case_sensitive_when_configured(): void
    {
        $rule = new ProhibitedWordsRule(['secret'], caseSensitive: true);
        $result = $rule->validate($this->makeDocument('This is a SECRET document.'));

        $this->assertTrue($result->isValid);
    }

    public function test_fails_when_multiple_prohibited_words_found(): void
    {
        $rule = new ProhibitedWordsRule(['confidential', 'secret']);
        $result = $rule->validate($this->makeDocument('This is confidential and secret.'));

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('confidential', $result->getErrors()[0]);
        $this->assertStringContainsString('secret', $result->getErrors()[0]);
    }

    public function test_passes_when_no_prohibited_words_configured(): void
    {
        $rule = new ProhibitedWordsRule([]);
        $result = $rule->validate($this->makeDocument('Any content here.'));

        $this->assertTrue($result->isValid);
    }

    public function test_has_correct_name(): void
    {
        $rule = new ProhibitedWordsRule(['word']);
        $this->assertSame('prohibited_words', $rule->getName());
    }
}
