# PHPUnit Testing Rule

## Description
Rather than running the full command `docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase`, run shorter PHPUnit tests by filtering MVC components one by one to save on long commands and avoid checking all tests if there are many failing.

## Preferred Commands

### Filter by Component Type
```bash
# Test Controllers only
docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase --filter Controller

# Test Models only  
docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase --filter Model

# Test specific component
docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase --filter ProductsController
```

### Filter by Test Group
```bash
# Run unit tests
docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase --group unit

# Run integration tests
docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase --group integration
```

### Quick Test Commands
```bash
# Test single file
docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase/Controller/ProductsControllerTest.php

# Stop on first failure
docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase --stop-on-failure
```

## Benefits
- Faster feedback during development
- Easier to isolate failing tests
- More efficient debugging workflow
- Reduced resource usage

## Apply This Rule
- Use filters when running tests during development
- Start with component-specific tests before running full suite
- Use `--stop-on-failure` flag for quick iteration
- Only run full test suite before commits or deployment