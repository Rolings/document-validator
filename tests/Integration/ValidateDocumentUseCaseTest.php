<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Application\ValidateDocumentUseCase;
use App\Domain\Document\Document;
use App\Domain\Validation\DocumentValidator;
use App\Domain\Validation\Rules\MaxDocumentSizeRule;
use App\Domain\Validation\Rules\NonEmptyContentRule;
use App\Domain\Validation\Rules\ProhibitedWordsRule;
use App\Domain\Validation\Rules\RequiredMetadataFieldsRule;
use App\Infrastructure\InMemoryTenantRuleRepository;
use PHPUnit\Framework\TestCase;

final class ValidateDocumentUseCaseTest extends TestCase
{
    private InMemoryTenantRuleRepository $repository;
    private ValidateDocumentUseCase $useCase;

    protected function setUp(): void
    {
        $this->repository = new InMemoryTenantRuleRepository();

        $this->useCase = new ValidateDocumentUseCase(
            validator: new DocumentValidator(),
            ruleRepository: $this->repository,
        );
    }

    public function test_valid_document_passes_all_tenant_rules(): void
    {
        $this->repository->setRulesForTenant('tenant-a', [
            new NonEmptyContentRule(),
            new MaxDocumentSizeRule(maxBytes: 500),
            new RequiredMetadataFieldsRule(['author', 'version']),
            new ProhibitedWordsRule(['confidential']),
        ]);

        $document = new Document(
            id: 'doc-1',
            tenantId: 'tenant-a',
            content: 'A clean, valid document.',
            metadata: ['author' => 'Alice', 'version' => '1.0'],
        );

        $result = $this->useCase->execute($document);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->getErrors());
    }

    public function test_document_fails_when_content_too_large(): void
    {
        $this->repository->setRulesForTenant('tenant-a', [
            new MaxDocumentSizeRule(maxBytes: 10),
        ]);

        $document = new Document(
            id: 'doc-2',
            tenantId: 'tenant-a',
            content: 'This content is definitely more than 10 bytes.',
        );

        $result = $this->useCase->execute($document);

        $this->assertFalse($result->isValid);
    }

    public function test_document_fails_when_metadata_field_missing(): void
    {
        $this->repository->setRulesForTenant('tenant-a', [
            new RequiredMetadataFieldsRule(['author', 'version']),
        ]);

        $document = new Document(
            id: 'doc-3',
            tenantId: 'tenant-a',
            content: 'Valid content.',
            metadata: ['author' => 'Bob'],
        );

        $result = $this->useCase->execute($document);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('version', $result->getErrors()[0]);
    }

    public function test_document_fails_with_multiple_violations(): void
    {
        $this->repository->setRulesForTenant('tenant-a', [
            new MaxDocumentSizeRule(maxBytes: 5),
            new RequiredMetadataFieldsRule(['author']),
            new ProhibitedWordsRule(['confidential']),
        ]);

        $document = new Document(
            id: 'doc-4',
            tenantId: 'tenant-a',
            content: 'This is confidential and too long.',
            metadata: [],
        );

        $result = $this->useCase->execute($document);

        $this->assertFalse($result->isValid);
        $this->assertGreaterThanOrEqual(3, count($result->getErrors()));
    }

    public function test_document_is_valid_when_tenant_has_no_rules(): void
    {
        $document = new Document(
            id: 'doc-5',
            tenantId: 'tenant-x',
            content: '',
            metadata: [],
        );

        $result = $this->useCase->execute($document);

        $this->assertTrue($result->isValid);
    }

    public function test_each_tenant_applies_only_its_own_rules(): void
    {
        $this->repository->setRulesForTenant('tenant-strict', [
            new ProhibitedWordsRule(['forbidden']),
        ]);
        $this->repository->setRulesForTenant('tenant-relaxed', [
            new NonEmptyContentRule(),
        ]);

        $doc = new Document('doc-6', 'tenant-strict', 'This contains forbidden words.');
        $docRelaxed = new Document('doc-7', 'tenant-relaxed', 'This contains forbidden words.');

        $this->assertFalse($this->useCase->execute($doc)->isValid);
        $this->assertTrue($this->useCase->execute($docRelaxed)->isValid);
    }

    public function test_repository_can_add_rules_incrementally(): void
    {
        $this->repository->addRuleForTenant('tenant-b', new NonEmptyContentRule());
        $this->repository->addRuleForTenant('tenant-b', new MaxDocumentSizeRule(maxBytes: 100));

        $document = new Document('doc-8', 'tenant-b', 'Short content.');

        $result = $this->useCase->execute($document);

        $this->assertTrue($result->isValid);
    }
}
