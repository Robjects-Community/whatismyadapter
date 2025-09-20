# AdminTheme Plugin Testing

This document describes how to run tests for the AdminTheme plugin.

## Overview

The AdminTheme plugin includes a comprehensive PHPUnit test suite with the following features:

- **Modern PHPUnit configuration** (compatible with PHPUnit 10.x)
- **Strict testing standards** with detailed error reporting
- **Code coverage support** for tracking test coverage
- **Random execution order** to catch order-dependent test issues
- **CakePHP test suite integration** with fixture support

## Test Structure

```
tests/
├── bootstrap.php           # Test bootstrap configuration
├── schema.sql             # Test database schema
└── TestCase/              # Test cases directory
    ├── AdminThemePluginTest.php      # Main plugin tests
    ├── Controller/
    │   └── AppControllerTest.php     # Controller tests
    └── View/
        └── AppViewTest.php           # View tests
```

## Running Tests

### Option 1: Using Docker (Recommended)

Since the WillowCMS project uses Docker for development, the recommended approach is to run tests within the Docker environment where all dependencies (including Redis extension) are available:

```bash
# From the willow project root
docker compose exec willowcms bash

# Inside the container, navigate to the plugin directory
cd plugins/AdminTheme

# Run the tests
../../vendor/bin/phpunit --configuration phpunit.xml.dist
```

### Option 2: Using the Test Runner Script

A convenience script is provided to run tests:

```bash
# Make sure you're in the AdminTheme plugin directory
cd plugins/AdminTheme

# Run the tests (works both inside and outside Docker)
./run-tests.sh

# Or with additional PHPUnit options
./run-tests.sh --verbose --stop-on-failure
```

### Option 3: Direct PHPUnit Execution

```bash
# From the AdminTheme plugin directory
../../vendor/bin/phpunit --configuration phpunit.xml.dist

# With specific options
../../vendor/bin/phpunit --configuration phpunit.xml.dist --testdox --colors=always
```

## Test Configuration

The `phpunit.xml.dist` file includes:

### Key Features
- **XML Schema validation** for configuration correctness
- **Random execution order** to catch test dependencies
- **Strict error reporting** with detailed output
- **CakePHP fixture extension** for database testing
- **Code coverage configuration** for quality metrics

### Environment Variables
- `CAKE_ENV=test` - Sets CakePHP to test environment
- `FIXTURE_SCHEMA_METADATA=true` - Enables fixture schema metadata
- `SECURITY_SALT=__SALT__` - Test security salt

### PHP Settings
- Memory limit: Unlimited (`-1`)
- APC CLI enabled
- All errors and warnings displayed
- Error reporting: All levels (`-1`)

## Test Coverage

To generate code coverage reports:

```bash
# Generate HTML coverage report
../../vendor/bin/phpunit --configuration phpunit.xml.dist --coverage-html coverage/

# Generate text coverage summary
../../vendor/bin/phpunit --configuration phpunit.xml.dist --coverage-text
```

Coverage reports will be generated in the `coverage/` directory.

## Writing Tests

### Test Structure
All test classes should:
- Extend `Cake\TestSuite\TestCase`
- Follow CakePHP testing conventions
- Include proper PHPDoc comments
- Use meaningful test method names with `test` prefix

### Example Test Class
```php
<?php
declare(strict_types=1);

namespace AdminTheme\Test\TestCase;

use Cake\TestSuite\TestCase;

class ExampleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Setup test fixtures or mocks
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        parent::tearDown();
    }

    public function testExampleFunctionality(): void
    {
        // Arrange
        $expected = 'expected result';
        
        // Act
        $actual = $this->methodUnderTest();
        
        // Assert
        $this->assertEquals($expected, $actual);
    }
}
```

## Troubleshooting

### Common Issues

1. **Redis Extension Missing**
   - Solution: Run tests inside Docker container where Redis is available
   - Or install Redis PHP extension locally

2. **Autoloader Issues**
   - Ensure `composer install` has been run in the main willow directory
   - Check that `vendor/autoload.php` exists

3. **Database Connection Issues**
   - Verify test database configuration
   - Check that MySQL is running (in Docker environment)

4. **Memory Issues**
   - PHPUnit is configured with unlimited memory
   - If issues persist, check system resources

### Debug Mode

To run tests with additional debugging:

```bash
../../vendor/bin/phpunit --configuration phpunit.xml.dist --debug --verbose
```

## Continuous Integration

The test configuration is designed to work with CI environments:

- Uses XML schema validation
- Includes strict error checking
- Supports multiple output formats
- Compatible with modern PHPUnit versions

## Additional Resources

- [CakePHP Testing Documentation](https://book.cakephp.org/4/en/development/testing.html)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WillowCMS Development Guide](../../README.md)
