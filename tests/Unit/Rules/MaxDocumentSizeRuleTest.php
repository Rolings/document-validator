<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Domain\Document\Document;
use App\Domain\Validation\Rules\MaxDocumentSizeRule;
use PHPUnit\Framework\TestCase;

final class MaxDocumentSizeRuleTest extends TestCase
{
    private function makeDocument(string $content): Document
    {
        return new Document('doc-1', 'tenant-1', $content);
    }

    public function test_passes_when_content_is_within_limit(): void
    {
        $rule = new MaxDocumentSizeRule(maxBytes: 100);
        $result = $rule->validate($this->makeDocument('short content'));

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->getErrors());
    }

    public function test_fails_when_content_exceeds_limit(): void
    {
        $rule = new MaxDocumentSizeRule(maxBytes: 10);
        $result = $rule->validate($this->makeDocument('this is longer than ten bytes'));

        $this->assertFalse($result->isValid);
        $this->assertNotEmpty($result->getErrors());
    }

    public function test_passes_when_content_is_exactly_at_limit(): void
    {
        $content = str_repeat('a', 100);
        $rule = new MaxDocumentSizeRule(maxBytes: 100);
        $result = $rule->validate($this->makeDocument($content));

        $this->assertTrue($result->isValid);
    }

    public function test_error_message_contains_actual_and_max_size(): void
    {
        $rule = new MaxDocumentSizeRule(maxBytes: 5);
        $content = 'abcdefghij';
        $result = $rule->validate($this->makeDocument($content));

        $this->assertStringContainsString('10', $result->getErrors()[0]);
        $this->assertStringContainsString('5', $result->getErrors()[0]);
    }

    public function test_has_correct_name(): void
    {
        $rule = new MaxDocumentSizeRule(maxBytes: 100);
        $this->assertSame('max_document_size', $rule->getName());
    }
}
