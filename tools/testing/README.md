# WillowCMS Testing Tools

**Comprehensive testing toolkit for achieving complete test coverage**

---

## 📦 What's Included

This directory contains all the tools you need to systematically test your WillowCMS application and achieve 80%+ code coverage.

### Tools

1. **`continuous-test.sh`** - Main testing script
2. **`progress.sh`** - Progress tracker and dashboard
3. **`generate-all-tests.sh`** - Bulk test generator

### Documentation

See `../../docs/` for complete guides:
- `TESTING_SETUP_SUMMARY.md` - Start here! ⭐
- `TESTING_QUICK_REFERENCE.md` - Daily cheat sheet ⭐
- `TESTING_COMPLETION_WORKFLOW.md` - Complete 8-week plan
- `CONTINUOUS_TESTING_WORKFLOW.md` - Tool reference

---

## 🚀 Quick Start

### 1. Check Progress
```bash
./tools/testing/progress.sh
```

### 2. Generate Tests
```bash
# Preview what will be created
./tools/testing/generate-all-tests.sh --models --dry-run

# Generate model tests
./tools/testing/generate-all-tests.sh --models
```

### 3. Start Testing
```bash
# Continuous testing with watch mode
./tools/testing/continuous-test.sh --model Users --watch
```

### 4. Track Coverage
```bash
./tools/testing/continuous-test.sh --model Users --coverage
open ../app/tmp/coverage/index.html
```

---

## 📖 Tool Documentation

### continuous-test.sh

Run PHPUnit tests with various modes and options.

**Basic Usage:**
```bash
./continuous-test.sh --model Users
./continuous-test.sh --controller Articles
./continuous-test.sh --component MyComponent
```

**Watch Mode (TDD):**
```bash
./continuous-test.sh --model Users --watch
```

**Coverage Reports:**
```bash
./continuous-test.sh --model Users --coverage
```

**Filter Tests:**
```bash
./continuous-test.sh --model Users --filter testValidation
```

**Batch Testing:**
```bash
./continuous-test.sh --type model --all
./continuous-test.sh --type controller --all
```

**Full Options:**
```bash
./continuous-test.sh --help
```

---

### progress.sh

Track testing progress across all components.

**Basic Usage:**
```bash
./progress.sh
```

**With Details:**
```bash
./progress.sh --detailed
```

**With Coverage:**
```bash
./progress.sh --coverage
```

**Output:**
```
===================================
WillowCMS Testing Progress
===================================

📊 Component Inventory:
   Models: 33
   Controllers: 68
   Middleware: 4
   Commands: 23

✅ Test Coverage:
   Models: 7/33 (21%)
   Controllers: 0/68 (0%)
   Middleware: 0/4 (0%)
   Commands: 0/23 (0%)

📈 Overall Progress:
   7/128 components tested (5%)

   [██░░░░░░░░░░░░░░░░░░] 5%
```

---

### generate-all-tests.sh

Bulk generate test stubs for all models and controllers.

**Preview (Dry Run):**
```bash
./generate-all-tests.sh --models --dry-run
./generate-all-tests.sh --controllers --dry-run
./generate-all-tests.sh --all --dry-run
```

**Generate:**
```bash
./generate-all-tests.sh --models
./generate-all-tests.sh --controllers
./generate-all-tests.sh --all
```

**Help:**
```bash
./generate-all-tests.sh --help
```

---

## 🎯 Testing Workflow

### Daily Routine

**Morning:**
```bash
# Check progress
./tools/testing/progress.sh --detailed

# Start Docker
./run_dev_env.sh

# Pick component and start watch mode
./tools/testing/continuous-test.sh --model Users --watch
```

**During Development:**
- Terminal 1: Watch mode running (auto-runs tests)
- Terminal 2: Edit tests

**Evening:**
```bash
# Generate coverage
./tools/testing/continuous-test.sh --model Users --coverage

# Commit work
git add app/tests/
git commit -m "test: Add UsersTable tests (92% coverage)"
git push
```

---

## 📊 Component Priority

### Week 1 - Critical Models (7 components)
1. UsersTable
2. RolesTable
3. ArticlesTable
4. PagesTable
5. CategoriesTable
6. SettingsTable
7. NavigationsTable

### Weeks 2-3 - Remaining Models (26 components)
All other Table classes

### Weeks 4-5 - Controllers (51 components at 75% target)
Priority order:
1. Authentication
2. CRUD operations
3. Admin
4. API
5. Utilities

---

## 🎓 Test Checklist

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
- [ ] Authenticated access tested
- [ ] Authorization rules
- [ ] CRUD actions work
- [ ] Form validation
- [ ] Redirects correct
- [ ] JSON responses (if API)
- [ ] Error handling
- [ ] Coverage > 75%

---

## 🔧 Configuration

### Test Database Setup
```bash
# Run migrations for test DB
docker compose exec willowcms php bin/cake migrations migrate --connection=test
```

### Watch Mode Requirements
```bash
# Install fswatch (macOS)
brew install fswatch
```

### Docker Environment
```bash
# Start environment
./run_dev_env.sh

# Check status
docker compose ps willowcms
```

---

## 🐛 Troubleshooting

### Container Not Running
```bash
./run_dev_env.sh
```

### Test Database Issues
```bash
docker compose exec willowcms php bin/cake migrations migrate --connection=test
```

### Watch Mode Not Working
```bash
brew install fswatch
```

### Tests Failing
```bash
# Verbose output
./continuous-test.sh --model Users --verbose

# Single method
./continuous-test.sh --model Users --filter testMethod
```

---

## 📁 File Locations

### Source Code
```
app/src/Model/Table/          # Models
app/src/Controller/           # Controllers
app/src/Middleware/           # Middleware
app/src/Command/              # Commands
```

### Tests
```
app/tests/TestCase/Model/Table/     # Model tests
app/tests/TestCase/Controller/      # Controller tests
app/tests/Fixture/                  # Test fixtures
app/tmp/coverage/                   # Coverage reports
```

---

## 📚 Documentation

### Start Here
1. **`docs/TESTING_SETUP_SUMMARY.md`** ⭐
   - Quick start guide
   - What's included
   - Next actions

2. **`docs/TESTING_QUICK_REFERENCE.md`** ⭐
   - Daily cheat sheet
   - All commands
   - Troubleshooting

### Complete Guides
3. **`docs/TESTING_COMPLETION_WORKFLOW.md`**
   - 8-week completion plan
   - MCP integration
   - GitHub Actions setup

4. **`docs/CONTINUOUS_TESTING_WORKFLOW.md`**
   - Tool deep dive
   - All options explained
   - Best practices

---

## 🎯 Success Metrics

### Daily Targets
- Models: 2-3 per day
- Controllers: 4-5 per day
- Time: 2-4 hours focused

### Weekly Milestones
- Week 1: 20% model coverage
- Week 2: 50% model coverage
- Week 3: 90% model coverage
- Week 4: 30% controller coverage
- Week 5: 75% controller coverage
- Week 6: Integration tests
- Week 7: 80%+ overall coverage
- Week 8: CI/CD integration

---

## 💡 Pro Tips

1. **Use watch mode** for TDD - instant feedback
2. **Generate in batches** - models first, then controllers
3. **Check progress often** - stay motivated
4. **Focus on coverage gaps** - use reports
5. **Test one method at a time** - faster feedback

---

## 🔗 External Resources

### CakePHP 5.x
- Testing: https://book.cakephp.org/5/en/development/testing.html
- Fixtures: https://book.cakephp.org/5/en/development/testing.html#fixtures

### PHPUnit
- Assertions: https://phpunit.de/manual/current/en/assertions.html
- Best Practices: https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html

---

## 🎉 Quick Start Today

```bash
# 1. Check current status
./tools/testing/progress.sh

# 2. Generate model tests
./tools/testing/generate-all-tests.sh --models

# 3. Start testing first model
./tools/testing/continuous-test.sh --model Users --watch

# 4. Track progress
./tools/testing/progress.sh --coverage
```

---

**Version:** 1.0  
**Last Updated:** 2025-10-07  
**Status:** Production Ready

**Happy Testing! 🚀**
