<?php

declare(strict_types=1);

namespace App\Domain\Validation\Contracts;

interface TenantRuleRepository
{

    public function getRulesForTenant(string $tenantId): array;
}
