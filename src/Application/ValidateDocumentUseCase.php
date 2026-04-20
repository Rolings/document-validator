<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Document\Document;
use App\Domain\Validation\DocumentValidator;
use App\Domain\Validation\ValidationResult;
use App\Domain\Validation\Contracts\TenantRuleRepository;

final class ValidateDocumentUseCase
{
    public function __construct(
        private readonly DocumentValidator     $validator,
        private readonly ?TenantRuleRepository $ruleRepository = null,
        private readonly array                 $additionalRules = [],
    )
    {
    }

    public function execute(Document $document): ValidationResult
    {
        $rules = [
            ...($this->ruleRepository?->getRulesForTenant($document->tenantId) ?? []),
            ...$this->additionalRules,
        ];

        return $this->validator->validate($document, $rules);
    }

    public function withRepository(TenantRuleRepository $repository): self
    {
        return new self($this->validator, $repository);
    }
}
