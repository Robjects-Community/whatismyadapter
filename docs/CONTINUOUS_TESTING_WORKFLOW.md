# Continuous Testing Workflow for CakePHP 5.x

**Purpose:** Automated, continuous PHPUnit testing for MVC components in CakePHP 5.x applications.

**Location:** `./tools/testing/continuous-test.sh`

---

## Features

### 🎯 Component-Specific Testing
- **Models** - Test Table classes, entities, behaviors
- **Controllers** - Test controller actions, routing, responses
- **Components** - Test controller components
- **Helpers** - Test view helpers
- **Commands** - Test CLI commands

### 🔄 Testing Modes
- **Single Run** - Run tests once and exit
- **Watch Mode** - Auto-reload tests when files change
- **Continuous Mode** - Run tests N times or infinitely
- **Coverage Mode** - Generate HTML coverage reports

### 📊 Advanced Features
- Filter specific test methods
- Stop on failure or continue
- Verbose output for debugging
- Integration with Docker Compose
- Color-coded output
- Performance timing

---

## Quick Start

### Test a Single Model
```bash
./tools/testing/continuous-test.sh --model Users
```

### Test with Watch Mode (Auto-reload)
```bash
./tools/testing/continuous-test.sh --model Users --watch
```

### Test with Coverage Report
```bash
./tools/testing/continuous-test.sh --controller Articles --coverage
```

### Test All Models
```bash
./tools/testing/continuous-test.sh --type model --all
```

---

## Usage Guide

### Command Structure

```bash
./tools/testing/continuous-test.sh [COMPONENT] [MODE] [OPTIONS]
```

### Component Options

| Option | Description | Example |
|--------|-------------|---------|
| `--model NAME` | Test a specific model | `--model Users` |
| `--controller NAME` | Test a specific controller | `--controller Articles` |
| `--component NAME` | Test a controller component | `--component Auth` |
| `--behavior NAME` | Test a model behavior | `--behavior Timestamp` |
| `--helper NAME` | Test a view helper | `--helper Form` |
| `--command NAME` | Test a CLI command | `--command CreateUser` |

### Type Options

| Option | Description | Example |
|--------|-------------|---------|
| `--type TYPE` | Test all components of a type | `--type model` |
| `--all` | Test all of specified type | `--type model --all` |

### Mode Options

| Option | Description | Default |
|--------|-------------|---------|
| `-w, --watch` | Enable watch mode | `false` |
| `-c, --coverage` | Generate coverage | `false` |
| `-v, --verbose` | Verbose output | `false` |
| `--no-stop` | Continue after failures | Stop on failure |
| `--filter PATTERN` | Filter test methods | None |
| `--iterations N` | Run N times (0=infinite) | 1 |

---

## Examples

### 1. Basic Model Testing

```bash
# Test UsersTable once
./tools/testing/continuous-test.sh --model Users
```

**Output:**
```
╔═══════════════════════════════════════════════════════════════╗
║     CakePHP 5.x Continuous Testing Workflow                 ║
╚═══════════════════════════════════════════════════════════════╝

ℹ  Testing: model 'Users'

➜ Configuration
  Component: model Users
  Watch mode: false
  Coverage: false
  Stop on failure: true

➜ Running tests (iteration: 1)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
ℹ  Test file: tests/TestCase/Model/Table/UsersTableTest.php
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

PHPUnit 10.x...
.........                                                           9 / 9 (100%)
Time: 00:01.234, Memory: 12.00 MB
OK (9 tests, 23 assertions)

✓ Tests passed in 1s
✓ Testing workflow completed
ℹ  Total iterations run: 1
```

### 2. Watch Mode (TDD Workflow)

```bash
# Watch mode - tests re-run on file changes
./tools/testing/continuous-test.sh --model Users --watch
```

**Workflow:**
1. Script runs tests initially
2. Monitors `src/`, `tests/`, `config/` directories
3. Detects file changes
4. Automatically re-runs tests
5. Continues until Ctrl+C

**Perfect for:**
- Test-Driven Development (TDD)
- Refactoring
- Bug fixing
- Feature development

### 3. Coverage Reports

```bash
# Generate HTML coverage report
./tools/testing/continuous-test.sh --model Users --coverage
```

**Output Location:** `app/tmp/coverage/index.html`

**View Report:**
```bash
open app/tmp/coverage/index.html
```

### 4. Filter Specific Tests

```bash
# Test only validation methods
./tools/testing/continuous-test.sh --model Users --filter testValidation
```

### 5. Test All Controllers

```bash
# Test every controller once
./tools/testing/continuous-test.sh --type controller --all
```

### 6. Continuous Testing Loop

```bash
# Run tests 10 times
./tools/testing/continuous-test.sh --model Users --iterations 10

# Run tests infinitely (stress testing)
./tools/testing/continuous-test.sh --model Users --iterations 0 --no-stop
```

### 7. Verbose Debugging

```bash
# Detailed output for debugging failures
./tools/testing/continuous-test.sh --model Users --verbose
```

---

## CakePHP 5.x Test Structure

### Expected Directory Layout

```
app/
├── src/
│   ├── Controller/
│   │   ├── UsersController.php
│   │   └── ArticlesController.php
│   ├── Model/
│   │   ├── Table/
│   │   │   ├── UsersTable.php
│   │   │   └── ArticlesTable.php
│   │   └── Entity/
│   │       ├── User.php
│   │       └── Article.php
│   └── View/
│       └── Helper/
│           └── CustomHelper.php
└── tests/
    ├── TestCase/
    │   ├── Controller/
    │   │   ├── UsersControllerTest.php
    │   │   └── ArticlesControllerTest.php
    │   ├── Model/
    │   │   └── Table/
    │   │       ├── UsersTableTest.php
    │   │       └── ArticlesTableTest.php
    │   └── View/
    │       └── Helper/
    │           └── CustomHelperTest.php
    └── Fixture/
        ├── UsersFixture.php
        └── ArticlesFixture.php
```

### Test File Naming Convention

| Component Type | Source File | Test File |
|----------------|-------------|-----------|
| Model | `UsersTable.php` | `UsersTableTest.php` |
| Controller | `UsersController.php` | `UsersControllerTest.php` |
| Component | `AuthComponent.php` | `AuthComponentTest.php` |
| Behavior | `TimestampBehavior.php` | `TimestampBehaviorTest.php` |
| Helper | `FormHelper.php` | `FormHelperTest.php` |
| Command | `CreateUserCommand.php` | `CreateUserCommandTest.php` |

---

## Integration with Development Workflow

### 1. TDD Cycle (Watch Mode)

```bash
# Terminal 1: Run watch mode
./tools/testing/continuous-test.sh --model Users --watch

# Terminal 2: Edit code
vim app/src/Model/Table/UsersTable.php

# Tests auto-run when you save!
```

### 2. Pre-Commit Testing

```bash
# Test all changed components before commit
./tools/testing/continuous-test.sh --type model --all
./tools/testing/continuous-test.sh --type controller --all
```

### 3. Coverage-Driven Development

```bash
# Generate coverage, identify gaps
./tools/testing/continuous-test.sh --model Users --coverage

# Open coverage report
open app/tmp/coverage/index.html

# Write tests for uncovered code
# Re-run with coverage to verify
```

### 4. Debugging Failures

```bash
# Verbose output with specific filter
./tools/testing/continuous-test.sh \
    --model Users \
    --filter testEmailValidation \
    --verbose
```

---

## Watch Mode Details

### File Monitoring

Watch mode monitors these directories:
- `app/src/` - Application source code
- `app/tests/` - Test files
- `app/config/` - Configuration files

### Supported File Types
- `*.php` - PHP source files
- `*.yml` - YAML configuration
- `*.yaml` - YAML configuration

### Requirements

**macOS (Recommended):**
```bash
brew install fswatch
```

**Fallback (All Platforms):**
- Polling mode (checks every 2 seconds)
- Automatic fallback if fswatch not available
- Slower but works everywhere

### Usage Tips

**Best Practices:**
- Use watch mode during active development
- Keep tests fast (< 5 seconds total)
- Focus on one component at a time
- Use `--filter` to narrow scope

**Performance:**
- Watch mode adds minimal overhead
- Fast detection with fswatch (< 100ms)
- Debounce prevents multiple triggers

---

## Coverage Reports

### Generating Coverage

```bash
./tools/testing/continuous-test.sh --model Users --coverage
```

### Viewing Reports

**Browser:**
```bash
open app/tmp/coverage/index.html
```

**Terminal:**
```bash
./tools/testing/continuous-test.sh --model Users --coverage --verbose
```

### Coverage Metrics

Reports include:
- **Line Coverage** - % of lines executed
- **Function Coverage** - % of functions called
- **Class Coverage** - % of classes instantiated
- **Complexity** - Cyclomatic complexity

### Coverage Goals

| Component Type | Target Coverage |
|----------------|-----------------|
| Models | 90%+ |
| Controllers | 75%+ |
| Components | 85%+ |
| Helpers | 80%+ |
| Commands | 85%+ |

---

## Troubleshooting

### Docker Not Running

**Error:**
```
ERROR: Docker service 'willowcms' is not running
```

**Solution:**
```bash
./run_dev_env.sh
```

### Test File Not Found

**Error:**
```
⚠  Test file not found: tests/TestCase/Model/Table/UsersTableTest.php
```

**Solution:**
1. Check component name spelling
2. Verify test file exists
3. Create test file if missing:
   ```bash
   # Use CakePHP bake to generate test
   docker compose exec willowcms bin/cake bake test table Users
   ```

### fswatch Not Installed

**Warning:**
```
⚠  fswatch not found. Install with: brew install fswatch
ℹ  Falling back to polling mode (slower)
```

**Solution (Optional):**
```bash
brew install fswatch
```

**Note:** Polling mode works fine, just slower

### Coverage Reports Empty

**Issue:** Coverage report shows 0% coverage

**Solution:**
1. Ensure Xdebug is installed in Docker
2. Check phpunit.xml configuration
3. Verify test actually runs

---

## Advanced Usage

### Custom PHPUnit Options

Edit the script's `build_phpunit_command()` function to add custom options.

### Integration with CI/CD

```yaml
# GitHub Actions example
- name: Run Tests
  run: |
    ./tools/testing/continuous-test.sh --type model --all
    ./tools/testing/continuous-test.sh --type controller --all
```

### Parallel Testing

```bash
# Terminal 1
./tools/testing/continuous-test.sh --type model --all

# Terminal 2
./tools/testing/continuous-test.sh --type controller --all
```

---

## Best Practices

### 1. Start Small
```bash
# Test one component first
./tools/testing/continuous-test.sh --model Users
```

### 2. Use Watch Mode for Development
```bash
# Active TDD
./tools/testing/continuous-test.sh --model Users --watch
```

### 3. Generate Coverage Regularly
```bash
# Check coverage weekly
./tools/testing/continuous-test.sh --model Users --coverage
```

### 4. Filter During Debugging
```bash
# Focus on failing test
./tools/testing/continuous-test.sh --model Users --filter testValidation
```

### 5. Test Before Committing
```bash
# Verify all tests pass
./tools/testing/continuous-test.sh --type model --all
./tools/testing/continuous-test.sh --type controller --all
```

---

## Related Documentation

- [PHPUnit Testing Plan](MERGE_AND_TESTING_PLAN.md) - Complete testing strategy
- [CakePHP 5.x Testing Guide](https://book.cakephp.org/5/en/development/testing.html)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

---

**Version:** 1.0  
**Last Updated:** 2025-10-07  
**Status:** Production Ready
