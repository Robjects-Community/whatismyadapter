# Merge Strategy and PHPUnit Testing Plan

**Date:** 2025-10-07  
**Current Branch:** portainer-stack  
**Target Branch:** main-clean  
**Status:** Merge conflicts require resolution

---

## 📋 Table of Contents

1. [Merge Strategy](#merge-strategy)
2. [Conflict Resolution Plan](#conflict-resolution-plan)
3. [PHPUnit Testing Plan](#phpunit-testing-plan)
4. [Implementation Roadmap](#implementation-roadmap)

---

## 🔀 Merge Strategy

### Current Situation

**Merge Attempt Result:**
```bash
git merge portainer-stack --no-edit
# Result: 13 conflicts detected
```

**Conflict Categories:**
1. **Configuration Files** (6 conflicts)
   - `.gitignore`
   - `docker-compose.yml`
   - `manage.sh`
   - `tool_modules/common.sh`
   - `stack.env.example`

2. **Docker Files** (3 conflicts)
   - `docker/redis/Dockerfile`
   - `docker/redis/redis.conf`
   - `infrastructure/docker/willowcms/Dockerfile`

3. **Application Code** (1 conflict)
   - `app/src/Console/Installer.php`

4. **Renamed/Deleted Files** (3 conflicts)
   - `deploy/docker-compose.test.yml`
   - `deploy/docker-compose.worker-limits.yml`
   - `tools/docker/docker-compose.override.yml.example`

5. **Tools** (1 conflict)
   - `tools/redis/bootguard.sh`

### Recommended Approach

#### Option 1: Cherry-Pick Strategy (RECOMMENDED)

**Pros:**
- Cleaner history
- More control over what gets merged
- Easier to test incrementally
- Can skip problematic changes

**Process:**
```bash
# Stay on main-clean
git checkout main-clean

# Cherry-pick specific commits from portainer-stack
git cherry-pick <commit-hash>  # Backup archival system
git cherry-pick <commit-hash>  # Documentation updates
# etc.
```

#### Option 2: Manual Conflict Resolution

**Pros:**
- Complete merge
- All history preserved
- Single merge commit

**Cons:**
- Time-consuming
- Risk of merge errors
- Harder to track what changed

**Process:**
```bash
git checkout main-clean
git merge portainer-stack

# For each conflict:
# 1. Review both versions
# 2. Choose the better implementation
# 3. Test the resolution
# 4. Mark as resolved

git add <resolved-file>
git commit
```

#### Option 3: Keep Branches Separate (PRACTICAL)

**Pros:**
- No merge conflicts
- Both branches work independently
- Can develop/test separately

**Cons:**
- Duplicate effort for bug fixes
- Need to maintain both branches

**Recommendation:**
Use portainer-stack for production and new development. Keep main-clean as a stable reference point.

---

## 🛠️ Conflict Resolution Plan

### Priority 1: Critical Application Code

**File:** `app/src/Console/Installer.php`

**Conflict:** Chmod handling improvement

**Resolution:**
```php
// Use portainer-stack version (better Docker compatibility)
$res = @chmod($path, $worldWritable);
if ($res) {
    $io->write('Permissions set on ' . $path);
} else {
    $io->write('<comment>Warning: Unable to set permissions on ' . 
        $path . ' (may be handled by container/host)</comment>');
}
```

**Action:** Accept portainer-stack version

---

### Priority 2: Build/Deployment Files

**Files:**
- `docker-compose.yml`
- `docker/redis/Dockerfile`
- `infrastructure/docker/willowcms/Dockerfile`

**Strategy:**
1. Compare both versions carefully
2. Keep portainer-stack versions (cleaner, production-ready)
3. Document any lost features from main-clean
4. Test thoroughly after merge

---

### Priority 3: Configuration Files

**Files:**
- `.gitignore`
- `stack.env.example`
- `manage.sh`
- `tool_modules/common.sh`

**Strategy:**
1. Merge both sets of changes
2. Keep portainer-stack structure
3. Add any missing main-clean features
4. Verify all scripts work

---

### Priority 4: Renamed/Deleted Files

**Files:**
- `deploy/docker-compose.test.yml`
- `deploy/docker-compose.worker-limits.yml`
- `tools/docker/docker-compose.override.yml.example`

**Strategy:**
1. Check if files are still needed
2. If yes, restore from main-clean
3. If no, accept deletion from portainer-stack
4. Document decision

---

## 🧪 PHPUnit Testing Plan

### Phase 1: Test Environment Setup (Week 1)

#### 1.1 Current Test Status Assessment

```bash
# Check current PHPUnit configuration
docker compose exec willowcms cat /var/www/html/phpunit.xml.dist

# List existing tests
find app/tests -name "*Test.php" -type f

# Run existing tests to establish baseline
docker compose exec willowcms php /var/www/html/vendor/bin/phpunit
```

#### 1.2 Test Infrastructure Setup

**Tasks:**
- [ ] Verify PHPUnit installation and version
- [ ] Check test database configuration
- [ ] Set up test fixtures and factories
- [ ] Configure code coverage tools
- [ ] Set up continuous integration (CI) pipeline

**Files to Create/Update:**
```
app/
├── phpunit.xml.dist              # PHPUnit configuration
├── tests/
│   ├── bootstrap.php             # Test bootstrap
│   ├── Fixture/                  # Test fixtures
│   │   ├── UsersFixture.php
│   │   ├── ArticlesFixture.php
│   │   └── SettingsFixture.php
│   └── TestCase/
│       ├── ApplicationTest.php   # Base test class
│       └── IntegrationTestCase.php
```

#### 1.3 Test Database Configuration

**Create:** `app/config/app_test.php`

```php
<?php
return [
    'Datasources' => [
        'default' => [
            'host' => env('TEST_DB_HOST', 'mysql'),
            'username' => env('TEST_DB_USER', 'root'),
            'password' => env('TEST_DB_PASS', 'rootpass'),
            'database' => env('TEST_DB_NAME', 'willow_test'),
        ],
        'test' => [
            'host' => env('TEST_DB_HOST', 'mysql'),
            'username' => env('TEST_DB_USER', 'root'),
            'password' => env('TEST_DB_PASS', 'rootpass'),
            'database' => env('TEST_DB_NAME', 'willow_test'),
        ],
    ],
];
```

---

### Phase 2: Unit Testing Implementation (Weeks 2-4)

#### 2.1 Model Layer Tests

**Priority:** HIGH - Foundation of application

**Test Coverage Goals:**
- ✅ All model methods
- ✅ Validation rules
- ✅ Associations
- ✅ Custom finders
- ✅ Behaviors

**Example Test Structure:**

```php
<?php
// app/tests/TestCase/Model/Table/UsersTableTest.php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\TestSuite\TestCase;

class UsersTableTest extends TestCase
{
    protected $Users;
    public $fixtures = ['app.Users', 'app.Roles'];

    public function setUp(): void
    {
        parent::setUp();
        $this->Users = $this->getTableLocator()->get('Users');
    }

    public function testValidation()
    {
        $user = $this->Users->newEntity([
            'email' => 'invalid-email',
            'password' => '123',  // Too short
        ]);
        
        $this->assertFalse($this->Users->save($user));
        $this->assertArrayHasKey('email', $user->getErrors());
        $this->assertArrayHasKey('password', $user->getErrors());
    }

    public function testFindActive()
    {
        $activeUsers = $this->Users->find('active')->toArray();
        $this->assertNotEmpty($activeUsers);
        
        foreach ($activeUsers as $user) {
            $this->assertTrue($user->is_active);
        }
    }
}
```

**Models to Test (Priority Order):**
1. UsersTable
2. ArticlesTable
3. SettingsTable
4. AipromptsTable
5. EmailTemplatesTable
6. NavigationsTable
7. PagesTable
8. FormsTable

---

#### 2.2 Controller Layer Tests

**Priority:** HIGH - User interaction testing

**Test Coverage Goals:**
- ✅ HTTP requests/responses
- ✅ Authentication/authorization
- ✅ CRUD operations
- ✅ Form submissions
- ✅ JSON responses
- ✅ Error handling

**Example Test Structure:**

```php
<?php
// app/tests/TestCase/Controller/ArticlesControllerTest.php
namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ArticlesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public $fixtures = [
        'app.Users',
        'app.Articles',
        'app.Categories'
    ];

    public function testIndexAsGuest()
    {
        $this->get('/articles');
        $this->assertResponseOk();
        $this->assertResponseContains('Articles');
    }

    public function testIndexAsAdmin()
    {
        $this->session(['Auth.User.id' => 1]);
        $this->get('/admin/articles');
        $this->assertResponseOk();
    }

    public function testAddArticleUnauthorized()
    {
        $this->post('/admin/articles/add', [
            'title' => 'Test Article',
            'body' => 'Test Body'
        ]);
        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function testAddArticleAuthorized()
    {
        $this->session(['Auth.User.id' => 1]);
        $this->post('/admin/articles/add', [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'body' => 'Test Body',
            'category_id' => 1,
            'is_published' => 1
        ]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('Article created successfully');
    }
}
```

**Controllers to Test (Priority Order):**
1. UsersController (Auth)
2. ArticlesController (CRUD)
3. PagesController (Dynamic)
4. FormsController (Submissions)
5. Admin/DashboardController
6. Api/* Controllers

---

#### 2.3 Component Tests

**Priority:** MEDIUM - Business logic components

**Components to Test:**
- AuthComponent
- FlashComponent
- RequestHandlerComponent
- Custom components

**Example Test:**

```php
<?php
namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\AuthComponent;
use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;

class AuthComponentTest extends TestCase
{
    public function testUserAuthentication()
    {
        $registry = new ComponentRegistry();
        $component = new AuthComponent($registry);
        
        $result = $component->identify([
            'email' => 'admin@test.com',
            'password' => 'password'
        ]);
        
        $this->assertNotEmpty($result);
        $this->assertEquals('admin@test.com', $result['email']);
    }
}
```

---

### Phase 3: Integration Testing (Weeks 5-6)

#### 3.1 Feature Tests

**Test complete user workflows:**

```php
<?php
namespace App\Test\TestCase\Feature;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ArticlePublishingWorkflowTest extends TestCase
{
    use IntegrationTestTrait;

    public $fixtures = ['app.Users', 'app.Articles', 'app.Categories'];

    public function testCompleteArticleWorkflow()
    {
        // 1. Login as admin
        $this->session(['Auth.User.id' => 1]);
        
        // 2. Create article
        $this->post('/admin/articles/add', [
            'title' => 'Integration Test Article',
            'slug' => 'integration-test',
            'body' => 'Test content',
            'category_id' => 1,
            'is_published' => 0  // Draft
        ]);
        $this->assertRedirect();
        
        // 3. Get article ID from session
        $articleId = $this->viewVariable('article')->id;
        
        // 4. Edit article
        $this->post("/admin/articles/edit/{$articleId}", [
            'title' => 'Updated Title',
            'is_published' => 1  // Publish
        ]);
        $this->assertRedirect();
        
        // 5. Verify public view
        $this->get('/articles/view/integration-test');
        $this->assertResponseOk();
        $this->assertResponseContains('Updated Title');
        
        // 6. Delete article
        $this->post("/admin/articles/delete/{$articleId}");
        $this->assertRedirect();
        
        // 7. Verify deletion
        $this->get('/articles/view/integration-test');
        $this->assertResponseCode(404);
    }
}
```

#### 3.2 API Tests

**Test RESTful API endpoints:**

```php
<?php
namespace App\Test\TestCase\Api;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ArticlesApiTest extends TestCase
{
    use IntegrationTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function testGetArticles()
    {
        $this->get('/api/articles.json');
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        
        $data = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('articles', $data);
        $this->assertIsArray($data['articles']);
    }

    public function testCreateArticleViaApi()
    {
        $this->post('/api/articles.json', json_encode([
            'title' => 'API Created Article',
            'body' => 'Content',
            'category_id' => 1
        ]));
        
        $this->assertResponseSuccess();
        $data = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('article', $data);
        $this->assertEquals('API Created Article', $data['article']['title']);
    }
}
```

---

### Phase 4: Test Automation & CI/CD (Week 7)

#### 4.1 Automated Test Runner Script

**Create:** `tools/testing/run-tests.sh`

```bash
#!/bin/bash
# Automated PHPUnit test runner

set -e

echo "==================================="
echo "WillowCMS Test Suite"
echo "==================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

# Check if containers are running
if ! docker compose ps | grep -q willowcms; then
    echo -e "${RED}Error: Docker containers not running${NC}"
    echo "Run: ./run_dev_env.sh"
    exit 1
fi

# Create test database if needed
echo "Setting up test database..."
docker compose exec -T willowcms php /var/www/html/bin/cake migrations migrate --connection=test

# Run tests
echo ""
echo "Running PHPUnit tests..."
echo "-----------------------------------"

if docker compose exec -T willowcms php /var/www/html/vendor/bin/phpunit "$@"; then
    echo ""
    echo -e "${GREEN}✓ All tests passed!${NC}"
    exit 0
else
    echo ""
    echo -e "${RED}✗ Some tests failed${NC}"
    exit 1
fi
```

#### 4.2 GitHub Actions Workflow

**Create:** `.github/workflows/tests.yml`

```yaml
name: PHPUnit Tests

on:
  push:
    branches: [ main-clean, portainer-stack, develop ]
  pull_request:
    branches: [ main-clean, portainer-stack ]

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
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, intl, pdo_mysql, redis
          coverage: xdebug
      
      - name: Install Composer dependencies
        run: |
          cd app
          composer install --prefer-dist --no-interaction
      
      - name: Run migrations
        run: |
          cd app
          php bin/cake migrations migrate --connection=test
      
      - name: Run PHPUnit tests
        run: |
          cd app
          vendor/bin/phpunit --coverage-text --coverage-clover=coverage.xml
      
      - name: Upload coverage
        uses: codecov/codecov-action@v3
        with:
          files: ./app/coverage.xml
```

#### 4.3 Pre-commit Hook

**Create:** `.git/hooks/pre-commit`

```bash
#!/bin/bash
# Run tests before commit

echo "Running pre-commit tests..."

# Run quick unit tests only
docker compose exec -T willowcms php /var/www/html/vendor/bin/phpunit \
    --testsuite Unit \
    --stop-on-failure

if [ $? -ne 0 ]; then
    echo "Tests failed. Commit aborted."
    exit 1
fi

echo "All tests passed. Proceeding with commit."
exit 0
```

---

### Phase 5: Code Coverage & Quality (Week 8)

#### 5.1 Code Coverage Goals

**Targets:**
- Overall: 80%+ coverage
- Models: 90%+ coverage
- Controllers: 75%+ coverage
- Components: 85%+ coverage

**Generate Coverage Report:**

```bash
# Generate HTML coverage report
docker compose exec willowcms php /var/www/html/vendor/bin/phpunit \
    --coverage-html app/tmp/coverage

# View report
open app/tmp/coverage/index.html
```

#### 5.2 Quality Metrics

**Install PHPStan:**

```bash
cd app
composer require --dev phpstan/phpstan
```

**Create:** `app/phpstan.neon`

```neon
parameters:
    level: 6
    paths:
        - src
        - tests
    excludePaths:
        - src/Console/Installer.php
    checkGenericClassInNonGenericObjectType: false
```

**Run Static Analysis:**

```bash
docker compose exec willowcms vendor/bin/phpstan analyze
```

---

## 📅 Implementation Roadmap

### Week 1: Foundation
- [ ] Resolve merge conflicts
- [ ] Set up test environment
- [ ] Configure test database
- [ ] Create base test classes
- [ ] Document testing standards

### Week 2-3: Model Tests
- [ ] UsersTable tests
- [ ] ArticlesTable tests
- [ ] SettingsTable tests
- [ ] Other model tests
- [ ] Achieve 90% model coverage

### Week 4-5: Controller Tests
- [ ] Authentication tests
- [ ] CRUD operation tests
- [ ] Admin controller tests
- [ ] API endpoint tests
- [ ] Achieve 75% controller coverage

### Week 6: Integration Tests
- [ ] Complete workflow tests
- [ ] API integration tests
- [ ] Form submission tests
- [ ] File upload tests
- [ ] Email sending tests

### Week 7: Automation
- [ ] CI/CD pipeline setup
- [ ] Automated test runner
- [ ] Pre-commit hooks
- [ ] Documentation

### Week 8: Quality & Polish
- [ ] Code coverage analysis
- [ ] Static analysis with PHPStan
- [ ] Performance testing
- [ ] Final documentation
- [ ] Team training

---

## 🎯 Success Criteria

### Testing
- ✅ 80%+ overall code coverage
- ✅ All critical paths tested
- ✅ CI/CD pipeline passing
- ✅ Zero known critical bugs

### Merge
- ✅ All conflicts resolved
- ✅ Both branches tested
- ✅ Documentation updated
- ✅ Team approved

### Production Ready
- ✅ All tests passing
- ✅ Performance benchmarks met
- ✅ Security audit passed
- ✅ Deployment tested

---

## 📚 Resources

### CakePHP Testing Docs
- [Testing Guide](https://book.cakephp.org/5/en/development/testing.html)
- [Test Fixtures](https://book.cakephp.org/5/en/development/testing.html#fixtures)
- [Integration Testing](https://book.cakephp.org/5/en/development/testing.html#integration-testing)

### PHPUnit Docs
- [PHPUnit Manual](https://phpunit.de/manual/current/en/index.html)
- [Assertions](https://phpunit.de/manual/current/en/assertions.html)
- [Code Coverage](https://phpunit.de/manual/current/en/code-coverage-analysis.html)

### Best Practices
- [PHP Testing Best Practices](https://phpbestpractices.org/#testing)
- [Test-Driven Development](https://martinfowler.com/bliki/TestDrivenDevelopment.html)

---

**Document Version:** 1.0  
**Last Updated:** 2025-10-07  
**Status:** Ready for Implementation
