# Middleware Testing Documentation

## Overview

This document describes the middleware testing infrastructure for WillowCMS, including unit tests, integration tests, and execution workflows.

## Architecture

### Test Structure

```
tests/
├── TestCase/
│   ├── Middleware/
│   │   ├── IpBlockerMiddlewareTest.php      (11 tests, 10 passing)
│   │   ├── ApiCsrfMiddlewareTest.php         (13 tests)
│   │   ├── LogIntegrityMiddlewareTest.php    (14 tests)
│   │   └── RateLimitMiddlewareTest.php       (existing)
│   └── Service/
│       └── Api/
│           └── RateLimitServiceTest.php
├── Integration/
│   ├── MiddlewareStackTest.php    (planned)
│   ├── RouteMiddlewareTest.php    (planned)
│   ├── RedisMiddlewareTest.php    (planned)
│   └── DatabaseMiddlewareTest.php (planned)
└── Fixture/
    ├── AiMetricsFixture.php
    ├── BlockedIpsFixture.php (planned)
    └── SettingsFixture.php   (planned)
```

## Unit Tests

### IpBlockerMiddleware Tests

**File:** `tests/TestCase/Middleware/IpBlockerMiddlewareTest.php`

**Coverage:**
- ✅ Blocks requests from blocked IPs
- ✅ Allows requests from non-blocked IPs
- ✅ Detects and blocks suspicious requests
- ✅ Sets clientIp attribute on requests
- ✅ Returns JSON responses for API requests
- ✅ Sets security headers in blocked responses
- ⚠️  BlockOnNoIp configuration (skipped - requires integration test)
- ✅ Blocks requests when IP cannot be determined
- ✅ Tracks suspicious activity with query parameters
- ✅ Passes requests to next handler when checks pass
- ✅ Handles IPv6 addresses

**Known Limitations:**
- One test is skipped because `SettingsManager::read()` returns default values in test environment
- This behavior should be tested via integration tests with actual Settings table records

### ApiCsrfMiddleware Tests

**File:** `tests/TestCase/Middleware/ApiCsrfMiddlewareTest.php`

**Coverage:**
- CSRF protection skipped for `/api/*` routes
- CSRF protection skipped for routes with Api prefix
- CSRF protection applied to non-API routes
- GET requests allowed for non-API routes
- All HTTP methods work for API routes (GET, POST, PUT, DELETE, PATCH)
- Nested API routes handled correctly
- API route detection logic verified
- Non-API routes not mistaken for API routes
- Language-prefixed routes handled
- Root route handled
- Query parameters don't affect route detection
- Proper integration with request handler chain

### LogIntegrityMiddleware Tests

**File:** `tests/TestCase/Middleware/LogIntegrityMiddlewareTest.php`

**Coverage:**
- Middleware disabled when configuration set to false
- Verification runs when interval has passed (3600s)
- Verification skipped within interval
- Middleware always passes requests to handler
- First run with empty cache handled correctly
- OK status handled properly
- Various integrity statuses handled (OK, INFO, WARNING, CRITICAL)
- Cache key management verified
- Verification exceptions handled gracefully
- Request flow not blocked by verification
- Verification interval constant tested (boundary conditions)
- Default configuration works correctly
- Multiple consecutive requests within interval handled efficiently

## Running Tests

### Using the Test Script

A comprehensive test execution script is available:

```bash
# Location
./tools/testing/test-middleware.sh

# Run all middleware tests
./tools/testing/test-middleware.sh --all

# Run specific test
./tools/testing/test-middleware.sh --filter IpBlockerMiddlewareTest

# Run with text coverage
./tools/testing/test-middleware.sh --all --coverage

# Run with HTML coverage report
./tools/testing/test-middleware.sh --all --html-coverage

# Run specific test method
./tools/testing/test-middleware.sh --filter testBlocksRequestsFromBlockedIps

# Interactive mode (no arguments)
./tools/testing/test-middleware.sh
```

### Direct PHPUnit Commands

```bash
# Run all middleware tests
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Middleware/

# Run specific middleware test file
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Middleware/IpBlockerMiddlewareTest.php

# Run with testdox output (human-readable)
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Middleware/ --testdox

# Run specific test method
docker compose exec willowcms php vendor/bin/phpunit --filter testBlocksRequestsFromBlockedIps

# Run with verbose output
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Middleware/ --verbose

# Generate text coverage
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Middleware/ --coverage-text

# Generate HTML coverage
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Middleware/ --coverage-html webroot/coverage/middleware
```

## Test Environment

### Docker Services Required

- **willowcms** - PHP 8.3.26, PHPUnit 10.5.55
- **mysql** - MySQL 8.0 (for integration tests)
- **redis** - Redis 7.2-alpine (for integration tests)

### Environment Variables

Tests run in `CAKE_ENV=test` mode, which:
- Uses in-memory SQLite for most tests
- Disables security middleware by default
- Returns default values from SettingsManager
- Uses Array cache engine for fast testing

### Configuration

**PHPUnit Configuration:** `app/phpunit.xml.dist`

```xml
<testsuites>
    <testsuite name="app">
        <directory>tests/TestCase/</directory>
    </testsuite>
    <testsuite name="middleware">
        <directory>tests/TestCase/Middleware</directory>
    </testsuite>
    <testsuite name="integration">
        <directory>tests/Integration</directory>
    </testsuite>
</testsuites>
```

## Coverage Reports

### Generating Coverage

```bash
# Text coverage (terminal output)
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Middleware/ --coverage-text

# HTML coverage (browser-viewable)
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Middleware/ --coverage-html webroot/coverage/middleware

# View HTML coverage
open app/webroot/coverage/middleware/index.html
```

### Coverage Goals

- **Target:** >80% code coverage for middleware classes
- **Current:** Comprehensive unit test coverage for all public methods
- **Missing:** Integration tests for cross-middleware interaction

## Troubleshooting

### Common Issues

#### 1. Bootstrap Schema Errors

**Symptom:**
```
Error in bootstrap script: TypeError:
Cake\Database\Schema\TableSchema::addIndex(): Argument #1 ($name) must be of type string
```

**Solution:**
Check `tests/schema/*.php` files for incorrect `addIndex` calls. Should be:
```php
// Correct
$table->addIndex('column_name', ['type' => 'index']);

// Incorrect  
$table->addIndex(['column_name'], ['type' => 'index']);
```

#### 2. SettingsManager Returns Defaults

**Symptom:**
Tests that rely on specific Settings values don't work as expected.

**Cause:**
`SettingsManager::read()` returns default values in test environment (line 101-103 of SettingsManager.php).

**Solution:**
- For unit tests: Use the default value behavior or skip tests that require specific settings
- For integration tests: Create Settings fixtures with required values

#### 3. Middleware Not Bypassed in Tests

**Symptom:**
Security middleware blocks test requests.

**Cause:**
Security middleware is enabled in `Application.php` lines 156-160.

**Solution:**
The middleware checks for test environment and disables itself:
```php
if (env('CAKE_ENV') !== 'test' || Configure::read('TestSecurity.enabled', false)) {
    $middlewareQueue
        ->add(new IpBlockerMiddleware())
        ->add(new RateLimitMiddleware());
}
```

#### 4. Redis Connection Issues

**Symptom:**
```
Connection refused to redis:6379
```

**Solution:**
```bash
# Check Redis is running
docker compose ps redis

# Restart Redis if needed
docker compose restart redis

# Check Redis connectivity from willowcms container
docker compose exec willowcms redis-cli -h redis ping
```

#### 5. MySQL Connection Issues

**Symptom:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Solution:**
```bash
# Check MySQL is running and healthy
docker compose ps mysql

# Wait for healthy status
docker compose exec mysql mysqladmin ping -h localhost -u root -p

# Restart if needed
docker compose restart mysql
```

## Integration Tests (Planned)

### MiddlewareStackTest

Tests complete middleware execution order and interaction:
- Middleware ordering matches Application.php
- IpBlockerMiddleware sets clientIp attribute
- RateLimitMiddleware uses clientIp from IpBlockerMiddleware
- Error propagation through middleware stack
- Different route types handled correctly

### RouteMiddlewareTest

Tests route-specific middleware behavior:
- `/users/login` - rate limiting applied
- `/admin/*` - authentication + rate limiting
- `/api/*` - no CSRF protection
- Language-prefixed routes (`/en/*`, `/es/*`)
- Public routes (home, sitemap.xml, robots.txt)

### RedisMiddlewareTest

Tests Redis integration:
- RateLimitMiddleware with actual Redis
- Rate limit counter increments correctly
- Key expiration and TTL verification
- Cache clearing between tests
- Connection failure handling

### DatabaseMiddlewareTest

Tests database integration:
- IpBlockerMiddleware with actual BlockedIps records
- Suspicious activity tracking inserts
- Settings table integration
- Transaction and rollback behavior

## Best Practices

### Writing Middleware Tests

1. **Mock External Dependencies**
   ```php
   $ipService = $this->createMock(IpSecurityService::class);
   $ipService->method('getClientIp')->willReturn('192.168.1.100');
   ```

2. **Use Anonymous Classes for Handlers**
   ```php
   $handler = new class implements RequestHandlerInterface {
       public function handle(ServerRequestInterface $request): ResponseInterface {
           return new Response();
       }
   };
   ```

3. **Clear Cache in setUp/tearDown**
   ```php
   protected function setUp(): void {
       parent::setUp();
       Cache::clear('default');
   }
   ```

4. **Test Both Success and Failure Paths**
   ```php
   public function testAllowsValidRequests(): void { /* ... */ }
   public function testBlocksInvalidRequests(): void { /* ... */ }
   ```

5. **Document Skipped Tests**
   ```php
   $this->markTestSkipped('Reason for skipping with alternative testing approach');
   ```

### Running Tests in CI/CD

```yaml
# .github/workflows/tests.yml example
- name: Run Middleware Tests
  run: |
    docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase/Middleware/ --coverage-text
```

## Test Execution Summary

### Unit Tests Status

| Test Class | Tests | Passing | Skipped | Notes |
|---|---|---|---|---|
| IpBlockerMiddlewareTest | 11 | 10 | 1 | One test skipped (SettingsManager limitation) |
| ApiCsrfMiddlewareTest | 13 | 13 | 0 | All passing |
| LogIntegrityMiddlewareTest | 14 | 14 | 0 | All passing |
| RateLimitMiddlewareTest | 10 | 10 | 0 | Existing tests passing |
| **Total** | **48** | **47** | **1** | **97.9% passing** |

### Coverage Metrics

Run the following to generate detailed coverage:

```bash
./tools/testing/test-middleware.sh --all --html-coverage
open app/webroot/coverage/middleware/index.html
```

## Additional Resources

- [CakePHP Testing Guide](https://book.cakephp.org/5/en/development/testing.html)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WillowCMS Test Refactoring Notebook](../docs/notebooks/TEST_REFACTORING.md)
- [WillowCMS Test Execution Summary](../docs/notebooks/TEST_EXECUTION_SUMMARY.md)

## Contributing

When adding new middleware:

1. Create corresponding test file in `tests/TestCase/Middleware/`
2. Follow existing test patterns (mocking, anonymous handlers, cache clearing)
3. Aim for >80% code coverage
4. Document any known limitations
5. Add integration tests for cross-middleware behavior
6. Update this documentation

## Maintenance

### Regular Tasks

- Run full test suite before merging: `./tools/testing/test-middleware.sh --all`
- Generate coverage reports monthly: `--html-coverage`
- Review and update skipped tests quarterly
- Keep fixtures synchronized with schema changes

### Updating Tests

When modifying middleware:

1. Update corresponding unit tests
2. Verify all tests still pass
3. Update coverage if API changes
4. Document behavioral changes in this file

---

**Last Updated:** 2025-10-07  
**Test Framework:** PHPUnit 10.5.55  
**PHP Version:** 8.3.26  
**CakePHP Version:** 5.x
