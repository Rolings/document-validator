<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Validation\ValidationResult;
use PHPUnit\Framework\TestCase;

final class ValidationResultTest extends TestCase
{
    public function test_pass_creates_valid_result_with_no_errors(): void
    {
        $result = ValidationResult::pass();

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->getErrors());
    }

    public function test_fail_creates_invalid_result_with_errors(): void
    {
        $result = ValidationResult::fail('Error one', 'Error two');

        $this->assertFalse($result->isValid);
        $this->assertCount(2, $result->getErrors());
        $this->assertContains('Error one', $result->getErrors());
        $this->assertContains('Error two', $result->getErrors());
    }

    public function test_merge_of_all_passing_results_is_valid(): void
    {
        $merged = ValidationResult::merge(
            ValidationResult::pass(),
            ValidationResult::pass(),
        );

        $this->assertTrue($merged->isValid);
        $this->assertEmpty($merged->getErrors());
    }

    public function test_merge_with_one_failing_result_is_invalid(): void
    {
        $merged = ValidationResult::merge(
            ValidationResult::pass(),
            ValidationResult::fail('Something went wrong'),
            ValidationResult::pass(),
        );

        $this->assertFalse($merged->isValid);
        $this->assertCount(1, $merged->getErrors());
    }

    public function test_merge_collects_all_errors_from_multiple_failures(): void
    {
        $merged = ValidationResult::merge(
            ValidationResult::fail('Error A'),
            ValidationResult::fail('Error B', 'Error C'),
        );

        $this->assertFalse($merged->isValid);
        $this->assertCount(3, $merged->getErrors());
        $this->assertContains('Error A', $merged->getErrors());
        $this->assertContains('Error B', $merged->getErrors());
        $this->assertContains('Error C', $merged->getErrors());
    }

    public function test_merge_of_empty_results_is_valid(): void
    {
        $merged = ValidationResult::merge();

        $this->assertTrue($merged->isValid);
    }
}
