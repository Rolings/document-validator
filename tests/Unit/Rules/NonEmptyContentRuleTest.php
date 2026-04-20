<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Domain\Document\Document;
use App\Domain\Validation\Rules\NonEmptyContentRule;
use PHPUnit\Framework\TestCase;

final class NonEmptyContentRuleTest extends TestCase
{
    private function makeDocument(string $content): Document
    {
        return new Document('doc-1', 'tenant-1', $content);
    }

    public function test_passes_when_content_is_present(): void
    {
        $rule = new NonEmptyContentRule();
        $result = $rule->validate($this->makeDocument('Hello world'));

        $this->assertTrue($result->isValid);
    }

    public function test_fails_when_content_is_empty_string(): void
    {
        $rule = new NonEmptyContentRule();
        $result = $rule->validate($this->makeDocument(''));

        $this->assertFalse($result->isValid);
        $this->assertNotEmpty($result->getErrors());
    }

    public function test_fails_when_content_is_only_whitespace(): void
    {
        $rule = new NonEmptyContentRule();
        $result = $rule->validate($this->makeDocument('   '));

        $this->assertFalse($result->isValid);
    }

    public function test_fails_when_content_is_only_newlines(): void
    {
        $rule = new NonEmptyContentRule();
        $result = $rule->validate($this->makeDocument("\n\n\t\n"));

        $this->assertFalse($result->isValid);
    }

    public function test_has_correct_name(): void
    {
        $rule = new NonEmptyContentRule();
        $this->assertSame('non_empty_content', $rule->getName());
    }
}
