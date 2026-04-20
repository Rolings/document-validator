# Document Validator

---

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
