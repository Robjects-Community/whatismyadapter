# Controller Test Generation - Quick Start

## Prerequisites
- Docker environment running
- WillowCMS application accessible via `docker compose`
- PHP 8.3+ and PHPUnit 10.5+ installed in container

## Step-by-Step Execution

### 1. Create Directory Structure
```bash
cd /Volumes/1TB_DAVINCI/docker/willow
mkdir -p tools/test-generator/templates
```

### 2. Implement Core Files

You need to create the following files based on the detailed plan in `docs/CONTROLLER_TEST_GENERATION_PLAN.md`:

#### Required Files:
1. ✅ **AuthenticationTestTrait** - `app/tests/TestCase/Controller/AuthenticationTestTrait.php`
2. ✅ **Controller Analyzer** - `tools/test-generator/analyze_controllers.php`
3. ✅ **Test Templates** - `tools/test-generator/templates/*.php` (3 templates)
4. ✅ **Test Generator** - `tools/test-generator/generate_tests.php`
5. ✅ **Execution Scripts** - `tools/test-generator/*.sh` (4 shell scripts)

### 3. Generate Essential Fixtures

```bash
cd /Volumes/1TB_DAVINCI/docker/willow

# Generate core fixtures
docker compose exec willowcms bin/cake bake fixture Users
docker compose exec willowcms bin/cake bake fixture Articles  
docker compose exec willowcms bin/cake bake fixture Products
docker compose exec willowcms bin/cake bake fixture Settings
docker compose exec willowcms bin/cake bake fixture Tags
```

**Important**: Edit `app/tests/Fixture/UsersFixture.php` to add test user records with proper roles.

### 4. Run Test Generation

```bash
cd /Volumes/1TB_DAVINCI/docker/willow/tools/test-generator

# Step 1: Analyze all 68 controllers
php analyze_controllers.php

# Step 2: Generate test files
php generate_tests.php

# Step 3: Validate syntax
chmod +x *.sh
./validate_tests.sh

# Step 4: Run smoke tests
./run_all_tests.sh
```

## Expected Results

### Generated Files
```
app/tests/TestCase/Controller/
├── Admin/
│   ├── AiMetricsControllerTest.php
│   ├── AipromptsControllerTest.php
│   ├── ArticlesControllerTest.php
│   └── ... (39 admin controller tests)
├── Api/
│   ├── AiFormSuggestionsControllerTest.php
│   ├── ProductsControllerTest.php
│   ├── QuizControllerTest.php
│   └── ReliabilityControllerTest.php
├── HomeControllerTest.php
├── ArticlesControllerTest.php
└── ... (25 root controller tests)
```

### Test Statistics
- **68 test files** created
- **~272 test methods** generated
- **Coverage**: Smoke tests for all public controller actions
- **Test Types**: 
  - Unauthenticated access tests
  - Authenticated user tests
  - Admin authorization tests
  - API JSON response tests

## Verification Commands

### Check Generated Files
```bash
# Count test files
find app/tests/TestCase/Controller -name "*Test.php" | wc -l

# List all test files
find app/tests/TestCase/Controller -name "*Test.php" | sort
```

### Run Tests by Category
```bash
# Admin tests only
./tools/test-generator/run_namespace_tests.sh admin

# API tests only
./tools/test-generator/run_namespace_tests.sh api

# Root controller tests
./tools/test-generator/run_namespace_tests.sh root

# Single controller
./tools/test-generator/run_single_test.sh Home
```

### Check Test Status
```bash
# View generation report
cat tools/test-generator/test_generation_report.json

# Run specific test method
docker compose exec willowcms php vendor/bin/phpunit \
    --filter testIndexAsAdmin \
    app/tests/TestCase/Controller/Admin/ArticlesControllerTest.php
```

## Troubleshooting

### Issue: "Controller manifest not found"
**Solution**: Run `php analyze_controllers.php` first before generating tests.

### Issue: "Permission denied" on shell scripts  
**Solution**: `chmod +x tools/test-generator/*.sh`

### Issue: Tests fail with authentication errors
**Solution**: 
1. Ensure `AuthenticationTestTrait.php` is created
2. Verify fixture data includes proper user records
3. Check that test bootstrap configures authentication correctly

### Issue: "Undefined fixture" errors
**Solution**: Generate missing fixtures:
```bash
docker compose exec willowcms bin/cake bake fixture <ModelName>
```

### Issue: Slow test execution
**Solution**:
- Tests use SQLite in-memory DB (already configured in `tests/bootstrap.php`)
- Minimize fixture records to essential data only
- Run specific test suites instead of full suite

## Next Steps After Generation

### 1. Review Generated Tests
Spot-check a few test files to ensure proper structure:
```bash
# View a sample test
cat app/tests/TestCase/Controller/HomeControllerTest.php
```

### 2. Run Initial Test Suite
```bash
cd /Volumes/1TB_DAVINCI/docker/willow
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/ --testdox
```

### 3. Address Any Failures
- Check `tools/test-generator/test_generation_report.json` for errors
- Fix any authentication or fixture issues
- Update templates if needed and regenerate

### 4. Integrate with CI/CD
Add to your GitHub Actions or CI pipeline:
```yaml
- name: Run Controller Tests
  run: docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/
```

### 5. Extend Tests
Based on your needs, add more specific tests:
- Form submission tests
- Validation error handling
- Edge cases and error conditions
- Performance benchmarks

## Quick Reference

### File Locations
- **Plan Document**: `docs/CONTROLLER_TEST_GENERATION_PLAN.md`
- **Test Generator**: `tools/test-generator/`
- **Generated Tests**: `app/tests/TestCase/Controller/`
- **Fixtures**: `app/tests/Fixture/`
- **Test Config**: `app/tests/bootstrap.php`, `app/phpunit.xml.dist`

### Key Commands
```bash
# Full test generation workflow
cd /Volumes/1TB_DAVINCI/docker/willow/tools/test-generator
php analyze_controllers.php && php generate_tests.php

# Run all tests
docker compose exec willowcms php vendor/bin/phpunit

# Coverage report
docker compose exec willowcms php vendor/bin/phpunit \
    --coverage-html webroot/coverage \
    tests/TestCase/Controller/
```

## Support

For detailed information, see:
- **Comprehensive Plan**: `docs/CONTROLLER_TEST_GENERATION_PLAN.md`
- **Testing Guide**: `docs/TESTING.md` (created after generation)
- **Test Refactoring Notes**: `docs/TEST_REFACTORING.md`

---

**Status**: Ready for implementation ✅  
**Estimated Time**: 2-3 hours for full implementation  
**Complexity**: Moderate (requires PHP, shell scripting, and CakePHP knowledge)
