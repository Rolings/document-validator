<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Validation\Contracts\TenantRuleRepository;
use App\Domain\Validation\Contracts\ValidationRule;

final class InMemoryTenantRuleRepository implements TenantRuleRepository
{
    private array $tenantRules = [];

    public function setRulesForTenant(string $tenantId, array $rules): void
    {
        $this->tenantRules[$tenantId] = $rules;
    }

    public function addRuleForTenant(string $tenantId, ValidationRule $rule): void
    {
        $this->tenantRules[$tenantId][] = $rule;
    }

    public function getRulesForTenant(string $tenantId): array
    {
        return $this->tenantRules[$tenantId] ?? [];
    }
}
