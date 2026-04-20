<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Document\Document;
use App\Domain\Validation\Contracts\ValidationRule;
use App\Domain\Validation\DocumentValidator;
use App\Domain\Validation\ValidationResult;
use PHPUnit\Framework\TestCase;

final class DocumentValidatorTest extends TestCase
{
    private DocumentValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new DocumentValidator();
    }

    private function makeDocument(): Document
    {
        return new Document('doc-1', 'tenant-1', 'some content');
    }

    private function passingRule(): ValidationRule
    {
        return new class implements ValidationRule {
            public function validate(Document $document): ValidationResult
            {
                return ValidationResult::pass();
            }
            public function getName(): string { return 'passing_rule'; }
        };
    }

    private function failingRule(string $error): ValidationRule
    {
        return new class($error) implements ValidationRule {
            public function __construct(private readonly string $error) {}
            public function validate(Document $document): ValidationResult
            {
                return ValidationResult::fail($this->error);
            }
            public function getName(): string { return 'failing_rule'; }
        };
    }

    public function test_returns_valid_when_no_rules_configured(): void
    {
        $result = $this->validator->validate($this->makeDocument(), []);

        $this->assertTrue($result->isValid);
    }

    public function test_returns_valid_when_all_rules_pass(): void
    {
        $result = $this->validator->validate($this->makeDocument(), [
            $this->passingRule(),
            $this->passingRule(),
        ]);

        $this->assertTrue($result->isValid);
    }

    public function test_returns_invalid_when_any_rule_fails(): void
    {
        $result = $this->validator->validate($this->makeDocument(), [
            $this->passingRule(),
            $this->failingRule('Rule failed'),
        ]);

        $this->assertFalse($result->isValid);
        $this->assertContains('Rule failed', $result->getErrors());
    }

    public function test_collects_errors_from_all_failing_rules(): void
    {
        $result = $this->validator->validate($this->makeDocument(), [
            $this->failingRule('First error'),
            $this->failingRule('Second error'),
        ]);

        $this->assertFalse($result->isValid);
        $this->assertCount(2, $result->getErrors());
        $this->assertContains('First error', $result->getErrors());
        $this->assertContains('Second error', $result->getErrors());
    }
}
