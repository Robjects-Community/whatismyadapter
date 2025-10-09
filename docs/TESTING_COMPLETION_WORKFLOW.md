# WillowCMS Testing Completion Workflow

**Purpose:** Systematic workflow to achieve complete test coverage for WillowCMS application.

**Status:** Starting from zero tests - Building comprehensive test suite

---

## 📊 Current Status

### Application Components

```
✅ Models (Tables): 36 files
✅ Controllers: 68 files
✅ Middleware: 5+ files
✅ Commands: Multiple CLI commands
✅ Tests: 0 files (Need to create all tests!)
```

### Coverage Goals

| Component Type | Count | Target Coverage | Priority |
|----------------|-------|-----------------|----------|
| Models | 36 | 90%+ | HIGH |
| Controllers | 68 | 75%+ | HIGH |
| Middleware | 5+ | 85%+ | MEDIUM |
| Commands | ~10 | 85%+ | LOW |
| Helpers/Components | ~5 | 80%+ | LOW |

---

## 🎯 Testing Strategy

### Phase 1: Foundation (Week 1) ✓ CURRENT
**Goal:** Set up testing infrastructure and create first tests

### Phase 2: Core Models (Weeks 2-3)
**Goal:** 90% coverage on critical models

### Phase 3: Controllers (Weeks 4-5)
**Goal:** 75% coverage on all controllers

### Phase 4: Integration (Week 6)
**Goal:** End-to-end workflows tested

### Phase 5: Polish (Weeks 7-8)
**Goal:** 80%+ overall coverage, CI/CD integrated

---

## 🛠️ MCP Servers for Testing

### 1. **GitHub MCP** (Primary)
**Purpose:** Track testing progress, create issues, manage PRs

**Key Functions:**
```bash
# Create testing milestone
gh api repos/OWNER/REPO/milestones -f title="Complete Test Suite" -f description="Achieve 80% test coverage"

# Create issues for each component
gh issue create --title "Test UsersTable" --label "testing,model" --body "Create comprehensive tests"

# Track progress
gh issue list --label testing --state open

# Link PRs to issues
gh pr create --title "Add UsersTable tests" --body "Closes #123"
```

**Workflow Integration:**
- Create issue for each model/controller
- Link test files to issues
- Track completion with labels
- Auto-close issues with PR merges

### 2. **Sequential Thinking MCP**
**Purpose:** Break down complex testing scenarios into steps

**Use Cases:**
- Planning test cases for complex models
- Designing integration test workflows
- Debugging failing tests
- Optimizing test performance

**Example:**
```javascript
// Use for complex test planning
sequentialthinking({
  thought: "Need to test UsersTable validation",
  totalThoughts: 10
})
// Returns step-by-step test case breakdown
```

### 3. **Knowledge Graph MCP**
**Purpose:** Map relationships between components and tests

**Use Cases:**
- Track which tests cover which components
- Identify untested code paths
- Map dependencies between tests
- Document test coverage relationships

**Example:**
```javascript
// Create entity for each model
create_entities([
  {
    name: "UsersTable",
    entityType: "Model",
    observations: [
      "Has validation rules for email",
      "Requires password hashing",
      "Has findActive custom finder"
    ]
  }
])

// Link to test file
create_relations([
  {
    from: "UsersTable",
    to: "UsersTableTest",
    relationType: "tested_by"
  }
])
```

### 4. **Fetch MCP**
**Purpose:** Access CakePHP and PHPUnit documentation

**Use Cases:**
- Look up CakePHP 5.x testing patterns
- Reference PHPUnit assertion methods
- Check testing best practices
- Find example test code

**Example:**
```bash
# Fetch CakePHP testing docs
fetch("https://book.cakephp.org/5/en/development/testing.html")

# Get PHPUnit assertions reference
fetch("https://phpunit.de/manual/current/en/assertions.html")
```

### 5. **Docker MCP**
**Purpose:** Manage test environment containers

**Use Cases:**
- Start test containers
- Reset test database
- View test logs
- Debug container issues

### 6. **Context7 Library MCP**
**Purpose:** Access up-to-date library documentation

**Use Cases:**
```bash
# Get CakePHP 5.x docs
resolve-library-id --libraryName "cakephp"
get-library-docs --context7CompatibleLibraryID "/cakephp/cakephp/5.x"

# Get PHPUnit docs
resolve-library-id --libraryName "phpunit"
get-library-docs --context7CompatibleLibraryID "/sebastianbergmann/phpunit"
```

---

## 📋 Step-by-Step Workflow

### Step 1: Initial Setup (Day 1)

#### 1.1 Create Testing Milestone (GitHub MCP)

```bash
# Using GitHub CLI or MCP
gh api repos/OWNER/REPO/milestones \
  -f title="Complete Test Suite" \
  -f description="Achieve 80%+ test coverage for WillowCMS" \
  -f state="open"
```

#### 1.2 Inventory Components

```bash
# List all models
find app/src/Model/Table -name "*Table.php" > testing-inventory/models.txt

# List all controllers
find app/src/Controller -name "*Controller.php" > testing-inventory/controllers.txt

# Create GitHub issues for each
while read model; do
  name=$(basename "$model" .php)
  gh issue create \
    --title "Test $name" \
    --label "testing,model" \
    --milestone "Complete Test Suite" \
    --body "Create comprehensive tests for $name"
done < testing-inventory/models.txt
```

#### 1.3 Set Up Test Database

```bash
# Create test config
cp app/config/app.php app/config/app_test.php

# Edit for test database
# Change DB name to willow_test

# Run test migrations
docker compose exec willowcms php bin/cake migrations migrate --connection=test
```

---

### Step 2: Test Generation Strategy

#### 2.1 Use CakePHP Bake (Quick Start)

```bash
# Generate test for a model
docker compose exec willowcms bin/cake bake test table Users

# Generate test for a controller
docker compose exec willowcms bin/cake bake test controller Users

# Generates:
# - app/tests/TestCase/Model/Table/UsersTableTest.php
# - app/tests/Fixture/UsersFixture.php
```

#### 2.2 Test Template Structure

Create base test template using Sequential Thinking MCP to plan test cases:

```php
<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\TestSuite\TestCase;

class UsersTableTest extends TestCase
{
    protected $Users;
    
    public $fixtures = [
        'app.Users',
        'app.Roles'
    ];
    
    public function setUp(): void
    {
        parent::setUp();
        $this->Users = $this->getTableLocator()->get('Users');
    }
    
    public function tearDown(): void
    {
        unset($this->Users);
        parent::tearDown();
    }
    
    // VALIDATION TESTS
    public function testValidationSuccess() { }
    public function testValidationFailure() { }
    
    // CRUD TESTS
    public function testSaveNew() { }
    public function testSaveUpdate() { }
    public function testDelete() { }
    
    // FINDER TESTS
    public function testFindActive() { }
    
    // BEHAVIOR TESTS
    public function testTimestampBehavior() { }
}
```

---

### Step 3: Continuous Testing Workflow

#### 3.1 Daily Workflow

**Morning:**
```bash
# 1. Check assigned issues
gh issue list --assignee @me --label testing

# 2. Pick a component (start with HIGH priority)
./tools/testing/continuous-test.sh --model Users --watch

# 3. Open component in editor (Terminal 2)
code app/src/Model/Table/UsersTable.php
```

**Development:**
```bash
# Terminal 1: Tests running in watch mode
./tools/testing/continuous-test.sh --model Users --watch

# Terminal 2: Write tests
vim app/tests/TestCase/Model/Table/UsersTableTest.php

# Tests auto-run on save!
# Fix until all pass
```

**Evening:**
```bash
# 1. Generate coverage report
./tools/testing/continuous-test.sh --model Users --coverage

# 2. Check coverage
open app/tmp/coverage/index.html

# 3. Commit and push
git add app/tests/TestCase/Model/Table/UsersTableTest.php
git commit -m "test: Add comprehensive tests for UsersTable

- Test validation rules
- Test CRUD operations
- Test custom finders
- Test behaviors
- Coverage: 92%

Closes #123"

git push

# 4. PR auto-closes issue
gh pr create --title "Add UsersTable tests" --body "Closes #123"
```

---

### Step 4: Progressive Testing Plan

#### Week 1: Foundation (5 days)

**Monday: Critical Models (20%)**
```bash
Priority 1: Authentication
- [ ] UsersTable (most critical)
- [ ] RolesTable

Commands:
./tools/testing/continuous-test.sh --model Users --watch
./tools/testing/continuous-test.sh --model Roles --watch
```

**Tuesday: Content Models (20%)**
```bash
Priority 2: Core Content
- [ ] ArticlesTable
- [ ] PagesTable
- [ ] CategoriesTable

Commands:
./tools/testing/continuous-test.sh --model Articles --watch
./tools/testing/continuous-test.sh --model Pages --watch
./tools/testing/continuous-test.sh --model Categories --watch
```

**Wednesday: Supporting Models (20%)**
```bash
Priority 3: Supporting
- [ ] SettingsTable
- [ ] NavigationsTable
- [ ] MenusTable

Commands:
./tools/testing/continuous-test.sh --model Settings --watch
./tools/testing/continuous-test.sh --model Navigations --watch
./tools/testing/continuous-test.sh --model Menus --watch
```

**Thursday: Remaining Models (20%)**
```bash
Priority 4: Rest of models
- [ ] All remaining Table classes

Commands:
./tools/testing/continuous-test.sh --type model --all
```

**Friday: Review & Coverage (20%)**
```bash
- [ ] Run all model tests
- [ ] Generate coverage reports
- [ ] Fix failing tests
- [ ] Document progress

Commands:
./tools/testing/continuous-test.sh --type model --all --coverage
open app/tmp/coverage/index.html
```

#### Week 2-3: Controllers (10 days)

**Daily Pattern:**
```bash
# Morning: 3-4 controllers per day
./tools/testing/continuous-test.sh --controller Users --watch
./tools/testing/continuous-test.sh --controller Articles --watch
./tools/testing/continuous-test.sh --controller Pages --watch

# Evening: Integration tests
./tools/testing/continuous-test.sh --type controller --all
```

**Priority Order:**
1. Authentication controllers (Users, Login)
2. CRUD controllers (Articles, Pages)
3. Admin controllers
4. API controllers
5. Utility controllers

---

### Step 5: Knowledge Tracking

#### Use Knowledge Graph MCP to Track Progress

```javascript
// Track each component
create_entities([
  {
    name: "UsersTable",
    entityType: "Model",
    observations: [
      "Test coverage: 92%",
      "Tests passing: 15/15",
      "Last tested: 2025-10-07",
      "Critical: Yes"
    ]
  },
  {
    name: "UsersTableTest",
    entityType: "Test",
    observations: [
      "Tests: 15 total",
      "Covers: validation, CRUD, finders",
      "Status: Complete"
    ]
  }
])

// Link relationships
create_relations([
  {
    from: "UsersTable",
    to: "UsersTableTest",
    relationType: "tested_by"
  },
  {
    from: "UsersTableTest",
    to: "UsersController",
    relationType: "required_by"
  }
])

// Query progress
search_nodes({ query: "test coverage" })
```

---

### Step 6: Automated Progress Tracking

#### Create Progress Dashboard Script

**File:** `tools/testing/progress.sh`

```bash
#!/bin/bash

echo "==================================="
echo "WillowCMS Testing Progress"
echo "==================================="
echo ""

# Count total components
TOTAL_MODELS=$(find app/src/Model/Table -name "*Table.php" | wc -l)
TOTAL_CONTROLLERS=$(find app/src/Controller -name "*Controller.php" | wc -l)

# Count test files
TEST_MODELS=$(find app/tests/TestCase/Model/Table -name "*Test.php" 2>/dev/null | wc -l)
TEST_CONTROLLERS=$(find app/tests/TestCase/Controller -name "*Test.php" 2>/dev/null | wc -l)

# Calculate percentages
MODEL_PERCENT=$((TEST_MODELS * 100 / TOTAL_MODELS))
CONTROLLER_PERCENT=$((TEST_CONTROLLERS * 100 / TOTAL_CONTROLLERS))

echo "Models: $TEST_MODELS/$TOTAL_MODELS ($MODEL_PERCENT%)"
echo "Controllers: $TEST_CONTROLLERS/$TOTAL_CONTROLLERS ($CONTROLLER_PERCENT%)"
echo ""

# Run tests and get coverage
if [ "$1" = "--coverage" ]; then
    docker compose exec willowcms vendor/bin/phpunit --coverage-text
fi

# Check GitHub issues
echo ""
echo "Open testing issues:"
gh issue list --label testing --state open | wc -l
```

**Usage:**
```bash
./tools/testing/progress.sh
./tools/testing/progress.sh --coverage
```

---

## 🔄 Integration with CI/CD

### GitHub Actions Workflow

**File:** `.github/workflows/tests.yml`

```yaml
name: PHPUnit Tests

on:
  push:
    branches: [ portainer-stack, main-clean ]
  pull_request:
    branches: [ portainer-stack, main-clean ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: rootpass
          MYSQL_DATABASE: willow_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, intl, pdo_mysql
          coverage: xdebug
      
      - name: Install dependencies
        run: |
          cd app
          composer install
      
      - name: Run migrations
        run: |
          cd app
          php bin/cake migrations migrate --connection=test
      
      - name: Run tests
        run: |
          cd app
          vendor/bin/phpunit --coverage-clover=coverage.xml
      
      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./app/coverage.xml
      
      - name: Comment PR with coverage
        if: github.event_name == 'pull_request'
        uses: codecov/codecov-action@v3
```

---

## 📈 Success Metrics

### Daily Targets

- **Models:** 2-3 per day = ~15 days for 36 models
- **Controllers:** 4-5 per day = ~15 days for 68 controllers
- **Coverage Increase:** +5-10% per week

### Weekly Milestones

- **Week 1:** 20% model coverage
- **Week 2:** 50% model coverage
- **Week 3:** 90% model coverage
- **Week 4:** 30% controller coverage
- **Week 5:** 75% controller coverage
- **Week 6:** Integration tests complete
- **Week 7:** 80%+ overall coverage
- **Week 8:** CI/CD integrated, documentation complete

---

## 🎓 Learning Resources (Via Fetch MCP)

### Essential Docs

```bash
# CakePHP 5.x Testing
fetch("https://book.cakephp.org/5/en/development/testing.html")

# PHPUnit Assertions
fetch("https://phpunit.de/manual/current/en/assertions.html")

# CakePHP Test Fixtures
fetch("https://book.cakephp.org/5/en/development/testing.html#fixtures")

# Integration Testing
fetch("https://book.cakephp.org/5/en/development/testing.html#integration-testing")
```

---

## 🚀 Quick Start Commands

### Today's Workflow

```bash
# 1. Check progress
./tools/testing/progress.sh

# 2. See available models
find app/src/Model/Table -name "*Table.php"

# 3. Generate test for first model
docker compose exec willowcms bin/cake bake test table Users

# 4. Start continuous testing
./tools/testing/continuous-test.sh --model Users --watch

# 5. Edit test in another terminal
code app/tests/TestCase/Model/Table/UsersTableTest.php

# 6. Check coverage when done
./tools/testing/continuous-test.sh --model Users --coverage

# 7. Commit and push
git add app/tests/
git commit -m "test: Add UsersTable tests"
git push
```

---

## 📝 Test Checklist Template

For each component, ensure:

### Models
- [ ] Validation rules tested
- [ ] Required fields validated
- [ ] Optional fields handled
- [ ] Custom validation rules
- [ ] CRUD operations work
- [ ] Create new records
- [ ] Update existing records
- [ ] Delete records
- [ ] Custom finders tested
- [ ] Associations tested
- [ ] Behaviors tested
- [ ] Edge cases handled

### Controllers
- [ ] Guest access tested
- [ ] Authenticated access tested
- [ ] Authorization rules
- [ ] CRUD actions work
- [ ] Form validation
- [ ] Flash messages
- [ ] Redirects correct
- [ ] JSON responses (API)
- [ ] Error handling
- [ ] Edge cases handled

---

## 🔍 Troubleshooting

### Common Issues

**Issue:** Test database not created
```bash
# Solution
docker compose exec willowcms php bin/cake migrations migrate --connection=test
```

**Issue:** Fixtures not loading
```bash
# Check fixture files exist
ls app/tests/Fixture/

# Generate if missing
docker compose exec willowcms bin/cake bake fixture Users
```

**Issue:** Tests fail in watch mode
```bash
# Run once to see full error
./tools/testing/continuous-test.sh --model Users --verbose
```

---

## 📊 Tracking Dashboard

Use this template to track weekly progress:

```markdown
# Week 1 Progress

## Models Tested (Goal: 7/36 = 20%)
- [x] UsersTable (92% coverage)
- [x] RolesTable (88% coverage)  
- [x] ArticlesTable (94% coverage)
- [x] PagesTable (91% coverage)
- [x] CategoriesTable (85% coverage)
- [x] SettingsTable (90% coverage)
- [x] NavigationsTable (87% coverage)

## Issues Closed
- #123: Test UsersTable ✓
- #124: Test RolesTable ✓
...

## Overall Progress
- Models: 7/36 (19%)
- Controllers: 0/68 (0%)
- Overall Coverage: 12%
```

---

**Version:** 1.0  
**Last Updated:** 2025-10-07  
**Status:** Ready to Start
