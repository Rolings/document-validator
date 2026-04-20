<?php

declare(strict_types=1);

define('VALIDATOR_START', microtime(true));

$autoloaders = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../autoload.php',
];


foreach ($autoloaders as $loader) {
    if (file_exists($loader)) {
        require_once $loader;
        break;
    }
}

use App\Application\ValidateDocumentUseCase;
use App\Domain\Document\Document;
use App\Domain\Validation\DocumentValidator;
use App\Domain\Validation\Rules\MaxDocumentSizeRule;
use App\Domain\Validation\Rules\NonEmptyContentRule;
use App\Domain\Validation\Rules\ProhibitedWordsRule;
use App\Domain\Validation\Rules\RequiredMetadataFieldsRule;
use App\Infrastructure\InMemoryTenantRuleRepository;
use App\Domain\Validation\ValidationResult;

$repository = new InMemoryTenantRuleRepository();

$repository->setRulesForTenant('tenant-a', [
    new NonEmptyContentRule(),
    new MaxDocumentSizeRule(maxBytes: 500),
    new RequiredMetadataFieldsRule(['author', 'version']),
    new ProhibitedWordsRule(['confidential', 'secret']),
]);

$repository->setRulesForTenant('tenant-b', [
    new NonEmptyContentRule(),
    new RequiredMetadataFieldsRule(['author']),
]);

$useCase = new ValidateDocumentUseCase(
    validator: new DocumentValidator(),
    ruleRepository: $repository,
);

function printResult(string $label, ValidationResult $result): void
{
    echo PHP_EOL . "=== {$label} ===" . PHP_EOL;
    if ($result->isValid) {
        echo "✅  VALID" . PHP_EOL;
    } else {
        echo "❌  INVALID" . PHP_EOL;
        foreach ($result->getErrors() as $error) {
            echo "    • {$error}" . PHP_EOL;
        }
    }

    echo PHP_EOL;
}


$validDoc = new Document(
    id: 'doc-001',
    tenantId: 'tenant-a',
    content: 'This is a perfectly fine document.',
    metadata: ['author' => 'Alice', 'version' => '1.0'],
);

printResult('Tenant A — valid document', $useCase->execute($validDoc));


$largeDoc = new Document(
    id: 'doc-002',
    tenantId: 'tenant-a',
    content: str_repeat('x', 600),
    metadata: ['author' => 'Bob', 'version' => '2.0'],
);

printResult('Tenant A — document exceeds size limit', $useCase->execute($largeDoc));


$badDoc = new Document(
    id: 'doc-003',
    tenantId: 'tenant-a',
    content: 'This document contains confidential information.',
    metadata: ['author' => 'Eve'],
);

printResult('Tenant A — multiple rule violations', $useCase->execute($badDoc));


$relaxedDoc = new Document(
    id: 'doc-004',
    tenantId: 'tenant-b',
    content: 'This document contains confidential information.',
    metadata: ['author' => 'Eve'],
);

printResult('Tenant B — same content, relaxed rules', $useCase->execute($relaxedDoc));

$emptyDoc = new Document(
    id: 'doc-005',
    tenantId: 'tenant-b',
    content: '   ',
    metadata: ['author' => 'Nobody'],
);

printResult('Tenant B — empty content', $useCase->execute($emptyDoc));

echo PHP_EOL;
