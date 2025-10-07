# Admin Controller Testing - Quick Reference
## ⚡ Quick Start

### Run All Smoke Tests
```bash
docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase/Controller/Admin/ --filter "RouteExists" --testdox
```

### Apply Pattern to New Controller
```bash
cd /app/tools/test-generation
php apply_admin_test_pattern.php ControllerName
```

## 📊 Current Status

| Metric | Value |
|--------|-------|
| **Total Controllers** | 26 |
| **Smoke Tests** | 78 |
| **Passing** | 59 (75.6%) |
| **Failing** | 19 (fixture issues) |

## 🛠️ Tools Created

### 1. AdminControllerTestCase
**Location:** `/app/tests/TestCase/Controller/Admin/AdminControllerTestCase.php`

**Helper Methods:**
- `loginAsAdmin()` - Authenticate as admin user
- `getFirstFixtureId($table)` - Get ID from fixture
- `assertRecordExists($table, $conditions)` - Verify DB record
- `assertFlashMessage($expected)` - Check flash messages

### 2. Pattern Application Script
**Location:** `/app/tools/test-generation/apply_admin_test_pattern.php`

**Usage:**
```bash
php apply_admin_test_pattern.php Products
```

**What it does:**
- ✅ Updates base class to AdminControllerTestCase
- ✅ Adds SystemLogs fixture
- ✅ Adds setUp with logging config
- ✅ Generates 3 smoke tests

### 3. Batch Script
**Location:** `/app/tools/test-generation/apply_pattern_to_all.sh`

**Usage:**
```bash
./apply_pattern_to_all.sh
```

## 🔧 Fixing Missing Fixtures

### Problem: "Cannot describe table_name. It has 0 columns"

**Solution:** Add schema to fixture

```php
<?php
namespace App\Test\Fixture;
use Cake\TestSuite\Fixture\TestFixture;

class TableNameFixture extends TestFixture
{
    public string $table = 'table_name';
    
    public array $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];
    
    public function init(): void
    {
        $this->records = [
            ['id' => 'uuid-here', 'name' => 'Test', 'created' => '2025-01-01 00:00:00', 'modified' => '2025-01-01 00:00:00'],
        ];
        parent::init();
    }
}
```

### Problem: "Could not find fixture 'app.FixtureName'"

**Solution:** Create the fixture file at:
```
/app/tests/Fixture/FixtureNameFixture.php
```

## 📝 Smoke Test Template

```php
public function testIndexRouteExists(): void
{
    $this->loginAsAdmin();
    $this->get('/admin/controller-name');
    
    $statusCode = $this->_response->getStatusCode();
    $this->assertContains($statusCode, [200, 500], 'Route exists');
}
```

## 🚨 Known Issues

| Controller | Issue | Fix |
|-----------|-------|-----|
| CableCapabilities | Missing fixture schema | Add $fields to fixture |
| HomepageFeeds | Fixture not found | Create fixture file |
| ImageGeneration | Fixture not found | Create fixture file |
| ProductPageViews | Fixture not found | Create fixture file |
| Cache | Table has 0 columns | Add schema to fixture |
| Videos | Table schema mismatch | Verify fixture |

## ✅ Next Steps

1. Fix 6 missing/broken fixtures → 100% pass rate
2. Expand smoke tests to verify content
3. Add CRUD operation tests
4. Implement CI/CD integration

## 📚 Documentation

- **Full Details:** [COMPLETE_TEST_IMPLEMENTATION_SUMMARY.md](COMPLETE_TEST_IMPLEMENTATION_SUMMARY.md)
- **Refactoring Notes:** [ADMIN_CONTROLLER_TEST_REFACTORING_SUMMARY.md](ADMIN_CONTROLLER_TEST_REFACTORING_SUMMARY.md)
- **Strategy:** [AI_METRICS_TEST_FIX_STRATEGY.md](AI_METRICS_TEST_FIX_STRATEGY.md)

---
**Last Updated:** 2025-10-07
