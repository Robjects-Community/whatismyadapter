# Testing & Code Coverage Guide

This guide covers everything you need to know about testing Willow CMS, generating coverage reports, and maintaining code quality.

## Table of Contents

- [Quick Start](#quick-start)
- [Local Testing](#local-testing)
  - [Running Tests](#running-tests)
  - [Generating Coverage Reports](#generating-coverage-reports)
  - [Viewing Coverage Reports](#viewing-coverage-reports)
- [CI/CD Testing](#cicd-testing)
- [Code Quality Tools](#code-quality-tools)
- [Writing Tests](#writing-tests)
- [Understanding Coverage Metrics](#understanding-coverage-metrics)
- [Troubleshooting](#troubleshooting)
- [Best Practices](#best-practices)

---

## Quick Start

```bash
# Start the development environment
docker compose up -d

# Run all tests
docker compose exec willowcms php vendor/bin/phpunit

# Run tests with coverage
docker compose exec willowcms php vendor/bin/phpunit --coverage-html /var/www/html/webroot/coverage

# View coverage report
open http://localhost:8080/coverage/
```

---

## Local Testing

### Running Tests

Willow CMS has 292+ comprehensive tests covering controllers, models, services, and more.

#### Run All Tests

```bash
# Standard test run
docker compose exec willowcms php vendor/bin/phpunit

# With detailed output (testdox format)
docker compose exec willowcms php vendor/bin/phpunit --testdox

# With colors
docker compose exec willowcms php vendor/bin/phpunit --colors=always
```

#### Run Specific Tests

```bash
# Run a specific test file
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/ArticlesControllerTest.php

# Run a specific test method
docker compose exec willowcms php vendor/bin/phpunit --filter testAdd

# Run tests for a specific component
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Model/
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Service/
```

#### Using Aliases (Recommended)

If you've sourced the `dev_aliases.txt` file:

```bash
# Source aliases (do this once per terminal session)
source dev_aliases.txt

# Run tests
phpunit-test

# Run with MVC filter
phpunit-mvc
```

### Generating Coverage Reports

Code coverage shows which parts of your code are tested and which are not.

#### HTML Coverage (Most Detailed)

```bash
# Generate HTML coverage report
docker compose exec willowcms php vendor/bin/phpunit --coverage-html /var/www/html/webroot/coverage

# Using alias
phpunit-coverage
```

The report will be generated at `/app/webroot/coverage/` inside the container, accessible via:
**http://localhost:8080/coverage/**

#### Text Coverage (Quick Overview)

```bash
# Generate text coverage report in terminal
docker compose exec willowcms php vendor/bin/phpunit --coverage-text

# Using alias
phpunit-coverage-text
```

#### Clover XML Coverage

```bash
# Generate Clover XML format (useful for CI/CD integrations)
docker compose exec willowcms php vendor/bin/phpunit --coverage-clover /var/www/html/webroot/coverage.xml

# Using alias
phpunit-coverage-clover
```

### Viewing Coverage Reports

#### Browser Access

```bash
# Open coverage report in browser
open http://localhost:8080/coverage/

# Using alias
coverage-open
```

#### Cleaning Up Coverage Reports

```bash
# Remove coverage reports to free space
docker compose exec willowcms rm -rf /var/www/html/webroot/coverage

# Using alias
coverage-clean
```

---

## CI/CD Testing

GitHub Actions automatically runs tests on every push to `main` and on all pull requests.

### Workflow Features

The CI/CD pipeline (`.github/workflows/tests.yml`) includes:

- ✅ **Automated PHPUnit testing** (all 292+ tests)
- 📊 **Code coverage generation** (HTML reports)
- 🔍 **PHPStan static analysis** (level 5)
- 🎨 **PHP CodeSniffer** (CakePHP coding standards)
- 🐳 **Docker-based testing environment**
- 📦 **Coverage artifact uploads** (7-day retention)
- 📝 **Test summary in PR comments**

### Triggering CI/CD

The workflow automatically runs when:
- Pushing commits to the `main` branch
- Opening or updating a pull request
- Manual trigger via GitHub Actions web interface

### Viewing CI/CD Results

1. Go to your repository on GitHub
2. Click the **Actions** tab
3. Select the most recent workflow run
4. Review:
   - Test results
   - Code quality checks
   - Coverage reports (download as artifact)

### Downloading Coverage Reports

1. Navigate to a completed workflow run
2. Scroll to the **Artifacts** section
3. Click **coverage-report** to download
4. Extract the ZIP file
5. Open `index.html` in your browser

---

## Code Quality Tools

### PHPStan (Static Analysis)

PHPStan analyzes code without running it, catching potential bugs early.

```bash
# Run PHPStan analysis
docker compose exec willowcms composer stan

# Or directly
docker compose exec willowcms vendor/bin/phpstan analyze
```

**Current level:** 5 (comprehensive type checking)

### PHP CodeSniffer (Coding Standards)

Ensures code follows CakePHP coding standards.

```bash
# Check code standards
docker compose exec willowcms composer cs-check

# Auto-fix violations
docker compose exec willowcms composer cs-fix

# Using aliases
phpcs_sniff  # Check
phpcs_fix    # Fix
```

### All Quality Checks at Once

```bash
# Run tests, PHPStan, and PHPCS
docker compose exec willowcms composer check
```

---

## Writing Tests

### Test Organization

```
tests/
├── bootstrap.php           # Test configuration
├── Fixture/               # Test data fixtures
│   ├── ArticlesFixture.php
│   ├── UsersFixture.php
│   └── ...
├── schema/                # SQLite test schema
└── TestCase/              # Actual test classes
    ├── Controller/        # Controller tests
    │   ├── Admin/        # Admin controller tests
    │   └── ...
    ├── Model/            # Model tests
    │   ├── Behavior/     # Behavior tests
    │   ├── Entity/       # Entity tests
    │   └── Table/        # Table tests
    ├── Service/          # Service class tests
    ├── Command/          # CLI command tests
    └── Middleware/       # Middleware tests
```

### Writing a Basic Test

```php
<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppControllerTestCase;

class ArticlesControllerTest extends AppControllerTestCase
{
    public function testIndex(): void
    {
        $this->get('/articles');
        
        $this->assertResponseOk();
        $this->assertResponseContains('Articles');
    }
    
    public function testAdd(): void
    {
        $data = [
            'title' => 'Test Article',
            'body' => 'Test content',
            'published' => true
        ];
        
        $this->post('/admin/articles/add', $data);
        
        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);
        
        // Verify article was created
        $articles = $this->getTableLocator()->get('Articles');
        $article = $articles->find()->where(['title' => 'Test Article'])->first();
        $this->assertNotNull($article);
    }
}
```

### Test Fixtures

Fixtures provide consistent test data:

```php
<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesFixture extends TestFixture
{
    public $records = [
        [
            'id' => 1,
            'title' => 'First Article',
            'body' => 'First article body text',
            'published' => 1,
            'created' => '2024-01-01 12:00:00',
            'modified' => '2024-01-01 12:00:00',
        ],
        // ... more records
    ];
}
```

### Using Fixtures in Tests

```php
class ArticlesControllerTest extends AppControllerTestCase
{
    protected array $fixtures = [
        'app.Articles',
        'app.Tags',
        'app.Users',
        'app.ArticlesTags',
    ];
    
    public function testViewPublishedArticle(): void
    {
        // Fixture data is automatically loaded
        $this->get('/articles/view/first-article');
        $this->assertResponseOk();
    }
}
```

---

## Understanding Coverage Metrics

### Coverage Types

#### Line Coverage
Percentage of code lines executed during tests.

**Example:**
```php
if ($user->isAdmin()) {  // Line 1 - Covered if executed
    return true;          // Line 2 - Covered only if condition true
}
return false;             // Line 3 - Covered if executed
```

#### Branch Coverage
Percentage of decision branches taken (if/else, switch cases).

**Example:**
```php
if ($value > 10) {       // Branch point
    // Branch 1 (true)
} else {
    // Branch 2 (false)
}
// 100% branch coverage requires testing both paths
```

#### Path Coverage
All possible execution paths through code.

### Coverage Goals

| Component | Target | Minimum |
|-----------|--------|---------|
| Overall | 85%+ | 80% |
| Controllers | 90%+ | 85% |
| Models | 95%+ | 90% |
| Services | 95%+ | 90% |
| Critical Paths | 100% | 95% |

### Reading Coverage Reports

#### Dashboard View
- **Green** (75-100%): Good coverage
- **Yellow** (50-75%): Moderate coverage  
- **Red** (0-50%): Poor coverage

#### File View
- **Green lines**: Executed by tests
- **Red lines**: Not executed
- **Gray lines**: Not executable (comments, declarations)

---

## Troubleshooting

### Tests Failing Locally

#### Database Issues

```bash
# Reset test database
docker compose exec willowcms bin/cake migrations migrate

# Clear cache
docker compose exec willowcms bin/cake cache clear_all
docker compose exec willowcms rm -rf tmp/cache/*
```

#### Permission Issues

```bash
# Fix file permissions
docker compose exec willowcms chown -R nobody:nobody /var/www/html
docker compose exec willowcms chmod -R 755 /var/www/html
```

### Coverage Not Generating

#### Check Xdebug Configuration

```bash
# Verify Xdebug is loaded
docker compose exec willowcms php -m | grep xdebug

# Check Xdebug mode
docker compose exec willowcms php -i | grep "xdebug.mode"
```

Expected output: `xdebug.mode => coverage`

#### Fix Xdebug Configuration

Edit `docker/willowcms/config/php/php.ini`:

```ini
[Xdebug]
zend_extension=xdebug.so
xdebug.mode=coverage
zend_assertions=1
```

Then restart containers:

```bash
docker compose restart willowcms
```

### Tests Pass Locally but Fail in CI

1. **Environment differences**: Check `.env.example` matches CI expectations
2. **Service dependencies**: Ensure MySQL and Redis are properly configured
3. **Database migrations**: Verify migrations are up-to-date
4. **Timing issues**: Add appropriate waits for service readiness

### Memory Issues

```bash
# Increase PHP memory limit in php.ini
memory_limit = 512M

# Or run tests with more memory
docker compose exec willowcms php -d memory_limit=512M vendor/bin/phpunit
```

### Slow Tests

```bash
# Run tests in parallel (requires paratest)
docker compose exec willowcms vendor/bin/paratest

# Or run specific test suites
docker compose exec willowcms php vendor/bin/phpunit --testsuite unit
```

---

## Best Practices

### General Testing Guidelines

1. **Write tests first (TDD)** when developing new features
2. **Test behavior, not implementation** - focus on what code does, not how
3. **Keep tests simple and focused** - one test per behavior
4. **Use descriptive test names** - `testUserCannotAccessAdminWithoutLogin`
5. **Maintain fixtures** - keep test data minimal and realistic
6. **Avoid test interdependencies** - each test should run independently
7. **Clean up after tests** - use `setUp()` and `tearDown()` properly

### Coverage Best Practices

1. **Don't chase 100% coverage** - aim for meaningful coverage
2. **Focus on critical paths first** - authentication, payment, data integrity
3. **Test edge cases** - null values, empty arrays, boundary conditions
4. **Test error handling** - verify exceptions and error messages
5. **Review uncovered lines** - understand why they're not covered

### Code Quality Best Practices

1. **Fix PHPStan issues** before committing
2. **Auto-fix PHPCS violations** with `composer cs-fix`
3. **Run quality checks locally** before pushing
4. **Add type hints** to improve static analysis
5. **Document complex logic** with PHPDoc blocks

### CI/CD Best Practices

1. **Always run tests locally** before pushing
2. **Check CI results** after pushing
3. **Don't ignore failing tests** - fix them or mark them as skipped with justification
4. **Review coverage changes** - ensure new code is tested
5. **Update documentation** when changing test setup

### Performance Testing

```bash
# Identify slow tests
docker compose exec willowcms php vendor/bin/phpunit --testdox --verbose

# Profile tests for performance issues
docker compose exec willowcms php -d xdebug.mode=profile vendor/bin/phpunit
```

---

## Helper Scripts

### Local CI Test Simulation

Run the full CI test suite locally:

```bash
./tools/ci/local-ci-test.sh
```

### Coverage Report Generation

Generate coverage without running full CI:

```bash
./tools/ci/coverage-report.sh
```

### Code Quality Checks

Run PHPStan and PHPCS:

```bash
./tools/ci/code-quality.sh
```

---

## Resources

### Documentation
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [CakePHP Testing Guide](https://book.cakephp.org/5/en/development/testing.html)
- [Xdebug Documentation](https://xdebug.org/docs/)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)

### CakePHP-Specific Resources
- [CakePHP Test Fixtures](https://book.cakephp.org/5/en/development/testing.html#fixtures)
- [CakePHP Integration Testing](https://book.cakephp.org/5/en/development/testing.html#integration-testing)
- [CakePHP Testing Best Practices](https://book.cakephp.org/5/en/development/testing.html#testing-best-practices)

### GitHub Actions
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Workflow Syntax](https://docs.github.com/en/actions/reference/workflow-syntax-for-github-actions)

---

## Contributing

When contributing to Willow CMS:

1. ✅ **Write tests for new features**
2. ✅ **Ensure existing tests pass**
3. ✅ **Maintain or improve coverage**
4. ✅ **Follow coding standards** (PHPCS)
5. ✅ **Pass static analysis** (PHPStan level 5)
6. ✅ **Document complex test scenarios**

---

## Support

If you encounter issues with testing:

1. Check this documentation
2. Review the [troubleshooting section](#troubleshooting)
3. Check GitHub Actions logs for CI failures
4. Review existing tests for examples
5. Ask in project discussions or issues

---

**Happy Testing! 🧪**

*Last updated: 2025-10-07*
