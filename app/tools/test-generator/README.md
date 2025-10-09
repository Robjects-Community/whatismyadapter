# Controller Test Generator

## Overview

This tool automatically generates PHPUnit test files for all 68 controllers in the WillowCMS application. It creates smoke tests that verify endpoints respond correctly for both authenticated and unauthenticated scenarios.

## Generated Files

- **68 test files** in `app/tests/TestCase/Controller/`
- **~774 test methods** covering all public controller actions
- Tests organized by namespace: Admin, Api, and Root

## Usage

### 1. Analyze Controllers

Scans all controllers and generates a metadata manifest:

```bash
cd /Volumes/1TB_DAVINCI/docker/willow
docker compose exec willowcms php tools/test-generator/analyze_controllers.php
```

This creates `controller_manifest.json` with metadata about all 68 controllers.

### 2. Generate Tests

Creates test files from templates:

```bash
docker compose exec willowcms php tools/test-generator/generate_tests.php
```

This generates:
- Root controller tests in `tests/TestCase/Controller/`
- Admin controller tests in `tests/TestCase/Controller/Admin/`
- API controller tests in `tests/TestCase/Controller/Api/`

### 3. Validate Tests

Check syntax of all generated tests:

```bash
app/tools/test-generator/validate_tests.sh
```

### 4. Run Tests

Run all controller tests:

```bash
app/tools/test-generator/run_all_tests.sh
```

Run specific namespace:

```bash
app/tools/test-generator/run_namespace_tests.sh admin
app/tools/test-generator/run_namespace_tests.sh api
app/tools/test-generator/run_namespace_tests.sh root
```

Run single controller:

```bash
app/tools/test-generator/run_single_test.sh Articles
```

## Test Structure

Each generated test includes:

### Root Controllers
- Unauthenticated access tests
- Authenticated access tests (if endpoint requires auth)

### Admin Controllers
- Admin authenticated access tests (`testMethodAsAdmin()`)
- Unauthenticated redirect tests (`testMethodRequiresAdmin()`)

### API Controllers
- JSON response format validation
- HTTP status code verification
- Content-Type header checks

## Test Patterns

### Smoke Tests

All tests are "smoke tests" - they verify endpoints respond without testing specific functionality:

```php
public function testIndexAsAdmin(): void
{
    $this->mockAdminUser();
    $this->get('/admin/articles');
    
    // Smoke test: verify admin can access
    $this->assertResponseOk();
}
```

### Authentication

Tests use the `AuthenticationTestTrait` for auth mocking:

- `mockAuthenticatedUser()` - Mock regular user
- `mockAdminUser()` - Mock admin user
- `mockUnauthenticatedRequest()` - Clear session

## Extending Tests

The generated tests provide basic coverage. To add specific tests:

1. Open the generated test file
2. Add new test methods
3. Use CakePHP's `IntegrationTestTrait` assertions
4. Add fixtures as needed

Example:

```php
public function testIndexDisplaysArticles(): void
{
    $this->mockAdminUser();
    $this->get('/admin/articles');
    
    $this->assertResponseOk();
    $this->assertResponseContains('Article Title');
}
```

## Files

- `analyze_controllers.php` - Controller analyzer
- `generate_tests.php` - Test generator
- `templates/` - Test templates
  - `controller_test_template.php` - Root controller template
  - `admin_controller_test_template.php` - Admin controller template
  - `api_controller_test_template.php` - API controller template
- `run_all_tests.sh` - Run all tests script
- `run_namespace_tests.sh` - Run namespace-specific tests
- `run_single_test.sh` - Run single controller test
- `validate_tests.sh` - Validate test syntax
- `controller_manifest.json` - Controller metadata (generated)
- `test_generation_report.json` - Generation report (generated)

## Reports

After generation, check `test_generation_report.json` for:
- Generation timestamp
- Controllers analyzed
- Tests generated
- Any errors encountered

## Maintenance

### Regenerating Tests

To regenerate tests after adding new controllers:

```bash
docker compose exec willowcms php tools/test-generator/analyze_controllers.php
docker compose exec willowcms php tools/test-generator/generate_tests.php
```

### Updating Templates

Edit templates in `templates/` directory and regenerate tests.

### Adding Fixtures

Generate fixtures for models:

```bash
docker compose exec willowcms bin/cake bake fixture ModelName
```

Then add to test's `$fixtures` array.

## Troubleshooting

### Tests Fail with Missing Fixtures

Generate the required fixture:

```bash
docker compose exec willowcms bin/cake bake fixture ModelName
```

### Authentication Errors

Ensure `AuthenticationTestTrait` is properly imported and bootstrap is configured.

### Slow Tests

Tests use SQLite in-memory database (configured in `tests/bootstrap.php`). Minimize fixture data to essential records.

## Documentation

- Full plan: `docs/CONTROLLER_TEST_GENERATION_PLAN.md`
- Quick start: `tools/test-generator/QUICK_START.md`
- Testing guide: `docs/TESTING.md`
