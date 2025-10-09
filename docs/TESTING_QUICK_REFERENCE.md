# WillowCMS Testing Quick Reference Card

**Quick access guide for daily testing workflow**

---

## 🚀 Getting Started (First Time)

```bash
# 1. Check your progress
./tools/testing/progress.sh

# 2. Preview test generation (dry run)
./tools/testing/generate-all-tests.sh --models --dry-run

# 3. Generate all model tests
./tools/testing/generate-all-tests.sh --models

# 4. Check progress again
./tools/testing/progress.sh
```

---

## 📅 Daily Workflow

### Morning Routine
```bash
# 1. Check overall progress
./tools/testing/progress.sh

# 2. See what needs testing
./tools/testing/progress.sh --detailed

# 3. Start Docker environment
./run_dev_env.sh

# 4. Pick a component and start continuous testing
./tools/testing/continuous-test.sh --model Users --watch
```

### During Development (Two Terminals)

**Terminal 1: Watch Mode (auto-runs tests on save)**
```bash
./tools/testing/continuous-test.sh --model Users --watch
```

**Terminal 2: Edit Tests**
```bash
# Open test file
code app/tests/TestCase/Model/Table/UsersTableTest.php

# Or use vim
vim app/tests/TestCase/Model/Table/UsersTableTest.php
```

### End of Day
```bash
# 1. Run with coverage
./tools/testing/continuous-test.sh --model Users --coverage

# 2. View coverage report
open app/tmp/coverage/index.html

# 3. Commit your work
git add app/tests/
git commit -m "test: Add UsersTable tests (92% coverage)"
git push
```

---

## 🔧 Common Commands

### Progress Tracking
```bash
# Quick status
./tools/testing/progress.sh

# With details (lists untested components)
./tools/testing/progress.sh --detailed

# With full test coverage
./tools/testing/progress.sh --coverage
```

### Test Generation
```bash
# Generate all model tests
./tools/testing/generate-all-tests.sh --models

# Generate all controller tests
./tools/testing/generate-all-tests.sh --controllers

# Generate everything
./tools/testing/generate-all-tests.sh --all

# Dry run (preview only)
./tools/testing/generate-all-tests.sh --all --dry-run

# Generate single test manually
docker compose exec willowcms bin/cake bake test table Users
```

### Running Tests

#### Single Component Tests
```bash
# Model
./tools/testing/continuous-test.sh --model Users

# Controller
./tools/testing/continuous-test.sh --controller Users

# With watch mode (TDD)
./tools/testing/continuous-test.sh --model Users --watch

# With coverage
./tools/testing/continuous-test.sh --model Users --coverage
```

#### Filtered Tests
```bash
# Run specific test method
./tools/testing/continuous-test.sh --model Users --filter testValidation

# Run multiple iterations
./tools/testing/continuous-test.sh --model Users --iterations 5

# Verbose output
./tools/testing/continuous-test.sh --model Users --verbose
```

#### Batch Tests
```bash
# All models
./tools/testing/continuous-test.sh --type model --all

# All controllers
./tools/testing/continuous-test.sh --type controller --all

# Everything with coverage
./tools/testing/continuous-test.sh --type model --all --coverage
```

#### Direct PHPUnit
```bash
# All tests
docker compose exec willowcms vendor/bin/phpunit

# Specific test file
docker compose exec willowcms vendor/bin/phpunit tests/TestCase/Model/Table/UsersTableTest.php

# With coverage
docker compose exec willowcms vendor/bin/phpunit --coverage-html app/tmp/coverage
```

---

## 📁 File Locations

### Source Files
```
app/src/Model/Table/          # Model files
app/src/Controller/           # Controller files
app/src/Middleware/           # Middleware files
app/src/Command/              # Command files
```

### Test Files
```
app/tests/TestCase/Model/Table/     # Model tests
app/tests/TestCase/Controller/      # Controller tests
app/tests/TestCase/Middleware/      # Middleware tests
app/tests/TestCase/Command/         # Command tests
app/tests/Fixture/                  # Test fixtures
```

### Testing Tools
```
./tools/testing/continuous-test.sh    # Main testing script
./tools/testing/progress.sh           # Progress tracker
./tools/testing/generate-all-tests.sh # Test generator
```

### Documentation
```
./docs/TESTING_COMPLETION_WORKFLOW.md     # Complete workflow guide
./docs/CONTINUOUS_TESTING_WORKFLOW.md     # Continuous testing docs
./docs/TESTING_QUICK_REFERENCE.md         # This file
```

---

## 🎯 Test Priority Order

### Week 1: Critical Models (20%)
1. `UsersTable`
2. `RolesTable`
3. `ArticlesTable`
4. `PagesTable`
5. `CategoriesTable`
6. `SettingsTable`
7. `NavigationsTable`

### Week 2-3: Remaining Models (70%)
- All other Table classes

### Week 4-5: Controllers (75%)
Priority order:
1. Authentication (Users, Auth)
2. CRUD (Articles, Pages)
3. Admin controllers
4. API controllers
5. Utility controllers

---

## 🧪 Test Checklist

### For Each Model
- [ ] Validation rules tested
- [ ] CRUD operations work
- [ ] Custom finders tested
- [ ] Associations tested
- [ ] Behaviors tested
- [ ] Edge cases handled
- [ ] Coverage > 90%

### For Each Controller
- [ ] Guest access tested
- [ ] Auth access tested
- [ ] Authorization rules
- [ ] CRUD actions work
- [ ] Form validation
- [ ] Redirects correct
- [ ] JSON responses (if API)
- [ ] Error handling
- [ ] Coverage > 75%

---

## 🐛 Troubleshooting

### Container Not Running
```bash
# Start environment
./run_dev_env.sh

# Check status
docker compose ps
```

### Test Database Issues
```bash
# Create/reset test database
docker compose exec willowcms php bin/cake migrations migrate --connection=test

# Check database exists
docker compose exec mysql mysql -u root -prootpass -e "SHOW DATABASES;"
```

### Fixtures Not Loading
```bash
# List fixtures
ls app/tests/Fixture/

# Generate missing fixture
docker compose exec willowcms bin/cake bake fixture Users
```

### Tests Failing
```bash
# Run with verbose output
./tools/testing/continuous-test.sh --model Users --verbose

# Run single test method
./tools/testing/continuous-test.sh --model Users --filter testSpecificMethod

# Check test file syntax
docker compose exec willowcms php -l app/tests/TestCase/Model/Table/UsersTableTest.php
```

### Watch Mode Not Working
```bash
# Install fswatch (macOS)
brew install fswatch

# Restart watch mode
./tools/testing/continuous-test.sh --model Users --watch
```

---

## 🎨 Output Color Codes

When reading test output:

- **Green**: Tests passing ✓
- **Red**: Tests failing ✗
- **Yellow**: Warnings or skipped tests
- **Blue**: Information messages

Coverage colors in progress script:

- **Green**: ≥ 80% coverage (Good!)
- **Yellow**: 50-79% coverage (Getting there)
- **Red**: < 50% coverage (Needs work)

---

## 💡 Pro Tips

### 1. Use Watch Mode for TDD
```bash
# Tests auto-run on file save
./tools/testing/continuous-test.sh --model Users --watch
```

### 2. Generate Tests in Batches
```bash
# Do models first, then controllers
./tools/testing/generate-all-tests.sh --models
# ... write tests ...
./tools/testing/generate-all-tests.sh --controllers
```

### 3. Check Progress Often
```bash
# Quick alias in ~/.zshrc
alias wt='cd /Volumes/1TB_DAVINCI/docker/willow && ./tools/testing/progress.sh'
```

### 4. Focus on Coverage Gaps
```bash
# Generate coverage, then focus on red areas
./tools/testing/continuous-test.sh --model Users --coverage
open app/tmp/coverage/index.html
```

### 5. Test One Method at a Time
```bash
# Faster feedback loop
./tools/testing/continuous-test.sh --model Users --filter testValidation --watch
```

### 6. Use Sequential Thinking MCP
When planning complex test scenarios, use the MCP tool to break down into steps.

### 7. Track with Knowledge Graph MCP
Document test coverage relationships in the knowledge graph for easy tracking.

---

## 📊 Goals & Targets

### Daily Goals
- **Models:** 2-3 tests per day
- **Controllers:** 4-5 tests per day
- **Time:** ~2-4 hours focused testing

### Weekly Milestones
- **Week 1:** 20% model coverage (7 models)
- **Week 2:** 50% model coverage (16 models)
- **Week 3:** 90% model coverage (30 models)
- **Week 4:** 30% controller coverage (20 controllers)
- **Week 5:** 75% controller coverage (51 controllers)
- **Week 6:** Integration tests
- **Week 7:** 80%+ overall coverage
- **Week 8:** CI/CD integration

### Final Target
- **80%+ overall code coverage**
- **All critical paths tested**
- **CI/CD pipeline running**
- **Documentation complete**

---

## 🔗 Useful Links

### CakePHP 5.x Documentation
- Testing: https://book.cakephp.org/5/en/development/testing.html
- Fixtures: https://book.cakephp.org/5/en/development/testing.html#fixtures
- Integration: https://book.cakephp.org/5/en/development/testing.html#integration-testing

### PHPUnit Documentation
- Assertions: https://phpunit.de/manual/current/en/assertions.html
- Best Practices: https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html

### Access via Fetch MCP
```bash
# Example using MCP
fetch("https://book.cakephp.org/5/en/development/testing.html")
```

---

**Version:** 1.0  
**Last Updated:** 2025-10-07  
**Status:** Ready to Use

---

## 🚦 Quick Start Today

```bash
# 1. Check status
./tools/testing/progress.sh

# 2. Generate first batch of tests
./tools/testing/generate-all-tests.sh --models --dry-run  # Preview
./tools/testing/generate-all-tests.sh --models            # Generate

# 3. Start testing your first model
./tools/testing/continuous-test.sh --model Users --watch

# 4. Edit test in another terminal
code app/tests/TestCase/Model/Table/UsersTableTest.php

# 5. Get coverage when done
./tools/testing/continuous-test.sh --model Users --coverage

# 6. Commit
git add app/tests/
git commit -m "test: Add UsersTable tests"
```

**That's it! You're now ready to achieve complete test coverage! 🎉**
