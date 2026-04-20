# Document Validator

---

A multi-tenant document validation platform built with PHP 8.2.
# Architecture
The project is split into three layers.

The `Domain layer` contains the core business logic - `Document` (value object), `ValidationResult` (value object), `DocumentValidator` (service), and the `ValidationRule` / `TenantRuleRepository` interfaces that define the system's contracts.

The `Application layer` contains `ValidateDocumentUseCase`, which orchestrates the validator and repository without knowing their concrete implementations.

The `Infrastructure layer` contains `InMemoryTenantRuleRepository` - a simple in-memory implementation of the repository contract, used for testing and demos.

Validation rules (`MaxDocumentSizeRule`, `RequiredMetadataFieldsRule`, `ProhibitedWordsRule`, `NonEmptyContentRule`) each implement the `ValidationRule` interface and are fully independent.

Adding a new rule means creating one new class - no existing code changes.

## Quick Start

### With Docker 

```bash
# Start the stack
docker compose up -d

# Install dependencies
docker compose exec php composer install

# Run the integration script
docker compose exec php php public/index.php

# Run tests
docker compose exec php vendor/bin/phpunit
```

## Running Tests

```bash
vendor/bin/phpunit --testdox
```
