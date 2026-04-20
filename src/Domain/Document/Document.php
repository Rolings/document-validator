<?php

declare(strict_types=1);

namespace App\Domain\Document;

final class Document
{

    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public readonly string $content,
        public readonly array $metadata = [],
    ) {}
}
