<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Domain\Document\Document;
use App\Domain\Validation\Rules\RequiredMetadataFieldsRule;
use PHPUnit\Framework\TestCase;

final class RequiredMetadataFieldsRuleTest extends TestCase
{
    private function makeDocument(array $metadata): Document
    {
        return new Document('doc-1', 'tenant-1', 'some content', $metadata);
    }

    public function test_passes_when_all_required_fields_present(): void
    {
        $rule = new RequiredMetadataFieldsRule(['author', 'version']);
        $result = $rule->validate($this->makeDocument(['author' => 'Alice', 'version' => '1.0']));

        $this->assertTrue($result->isValid);
    }

    public function test_fails_when_a_required_field_is_missing(): void
    {
        $rule = new RequiredMetadataFieldsRule(['author', 'version']);
        $result = $rule->validate($this->makeDocument(['author' => 'Alice']));

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('version', $result->getErrors()[0]);
    }

    public function test_fails_when_all_fields_are_missing(): void
    {
        $rule = new RequiredMetadataFieldsRule(['author', 'version']);
        $result = $rule->validate($this->makeDocument([]));

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('author', $result->getErrors()[0]);
        $this->assertStringContainsString('version', $result->getErrors()[0]);
    }

    public function test_passes_when_no_required_fields_configured(): void
    {
        $rule = new RequiredMetadataFieldsRule([]);
        $result = $rule->validate($this->makeDocument([]));

        $this->assertTrue($result->isValid);
    }

    public function test_passes_with_extra_metadata_fields(): void
    {
        $rule = new RequiredMetadataFieldsRule(['author']);
        $result = $rule->validate($this->makeDocument([
            'author' => 'Alice',
            'extra'  => 'value',
            'more'   => 'data',
        ]));

        $this->assertTrue($result->isValid);
    }

    public function test_has_correct_name(): void
    {
        $rule = new RequiredMetadataFieldsRule(['author']);
        $this->assertSame('required_metadata_fields', $rule->getName());
    }
}
