# AiMetricsControllerTest Fix Strategy

## Current Status (2025-10-07)

**Test Results: 9 Failures, 7 Passing (43.75% pass rate)**

- ✅ All authentication redirect tests pass
- ❌ All authenticated admin actions return HTTP 500 errors

## Root Cause Analysis

### Primary Issue: Database Logging Table Missing
**Error in logs:** `"Cannot describe system_logs. It has 0 columns."`

The test database is missing or has a malformed `system_logs` table. When errors occur during controller execution, CakePHP attempts to log to this table but fails, masking the original error.

### Secondary Issues Identified

1. **Template Files Created** ✅
   - Dashboard, index, view, add, edit templates have been created
   - Located in: `/app/plugins/AdminTheme/templates/Admin/AiMetrics/`

2. **Authorization Policy Created** ✅
   - `AiMetricPolicy.php` created with proper admin-only access rules
   - Located in: `/app/src/Policy/AiMetricPolicy.php`

3. **Fixtures Present** ✅
   - `AiMetricsFixture.php` - 4 test records
   - `SettingsFixture.php` - Multiple setting records
   - `UsersFixture.php` - Admin and regular users

4. **Controller Methods Exist** ✅
   - All CRUD methods present
   - Custom methods: dashboard(), realtimeData()
   - Table helper methods: getCostsByDateRange(), getTaskTypeSummary(), getRecentErrors()

## Priority 2: Update Test Expectations

### Approach A: Fix Database Logging (RECOMMENDED)

**Create SystemLogsFixture**
```php
// tests/Fixture/SystemLogsFixture.php
<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class SystemLogsFixture extends TestFixture
{
    public string $table = 'system_logs';

    public array $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'level' => ['type' => 'string', 'length' => 20, 'null' => false],
        'message' => ['type' => 'text', 'null' => false],
        'context' => ['type' => 'text', 'null' => true],
        'created' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
        '_indexes' => [
            'level_idx' => ['type' => 'index', 'columns' => ['level']],
            'created_idx' => ['type' => 'index', 'columns' => ['created']],
        ],
    ];

    public function init(): void
    {
        $this->records = [];
        parent::init();
    }
}
```

**Add fixture to test:**
```php
protected array $fixtures = [
    'app.Users',
    'app.AiMetrics',
    'app.Settings',
    'app.SystemLogs', // Add this
];
```

### Approach B: Disable Database Logging in Tests

**Modify test setUp():**
```php
protected function setUp(): void
{
    parent::setUp();
    
    // Disable database logging in tests
    \Cake\Core\Configure::write('debug', true);
    \Cake\Log\Log::drop('database');
    
    // Configure file logging only for tests
    \Cake\Log\Log::setConfig('test', [
        'className' => 'Cake\Log\Engine\FileLog',
        'path' => LOGS,
        'file' => 'test',
        'levels' => ['error', 'critical', 'alert', 'emergency'],
    ]);
}
```

### Approach C: Accept 500 Errors Temporarily

**Update test expectations to temporarily accept failures:**
```php
public function testDashboardAsAdmin(): void
{
    $this->loginAsAdmin();
    $this->get('/admin/ai-metrics/dashboard');
    
    // Temporary: Accept 500 until database logging fixed
    if ($this->_response->getStatusCode() === 500) {
        $this->markTestSkipped('Skipping due to database logging issue');
        return;
    }
    
    $this->assertResponseOk();
    $this->assertResponseContains('AI Metrics Dashboard');
}
```

## Priority 3: Fix Fixture Data Issues

### Check Database Schema Match

Run this query to compare fixture to actual table:
```sql
DESCRIBE ai_metrics;
DESCRIBE settings;
DESCRIBE system_logs;
```

### Ensure Settings Fixture Has Required Keys

The `RateLimitService` needs these settings:
- `AI.enableMetrics` (boolean, default: true)
- `AI.hourlyLimit` (integer, default: 100)
- `AI.dailyCostLimit` (decimal, default: 50.00)

**Add to SettingsFixture if missing:**
```php
[
    'id' => 'ai-enable-metrics-001',
    'category' => 'AI',
    'key_name' => 'enableMetrics',
    'value' => '1',
    'value_type' => 'bool',
    'description' => 'Enable AI metrics tracking',
    'created' => '2025-01-01 00:00:00',
    'modified' => '2025-01-01 00:00:00',
],
[
    'id' => 'ai-hourly-limit-001',
    'category' => 'AI',
    'key_name' => 'hourlyLimit',
    'value' => '100',
    'value_type' => 'numeric',
    'description' => 'Hourly API call limit',
    'created' => '2025-01-01 00:00:00',
    'modified' => '2025-01-01 00:00:00',
],
[
    'id' => 'ai-daily-cost-limit-001',
    'category' => 'AI',
    'key_name' => 'dailyCostLimit',
    'value' => '50.00',
    'value_type' => 'numeric',
    'description' => 'Daily cost limit in USD',
    'created' => '2025-01-01 00:00:00',
    'modified' => '2025-01-01 00:00:00',
],
```

## Recommended Implementation Order

1. **FIRST**: Create `SystemLogsFixture` and add to test fixtures (Approach A)
2. **SECOND**: Run tests again to see if this resolves 500 errors
3. **THIRD**: If still failing, add debug output to capture actual error messages
4. **FOURTH**: Verify Settings fixture has required AI configuration keys
5. **FIFTH**: If needed, disable database logging in test setUp (Approach B)

## Expected Outcome

After implementing fixes:
- **Target**: 14-16 passing tests (87-100% pass rate)
- **Realistic**: 12-14 passing tests (75-87% pass rate)
- **Minimum**: 10-12 passing tests (62-75% pass rate)

## Files to Modify

1. `/app/tests/Fixture/SystemLogsFixture.php` - CREATE NEW
2. `/app/tests/Fixture/SettingsFixture.php` - ADD AI settings
3. `/app/tests/TestCase/Controller/Admin/AiMetricsControllerTest.php` - UPDATE fixtures array
4. `/app/tests/TestCase/Controller/Admin/AdminControllerTestCase.php` - OPTIONAL: Add logging setup

## Testing Command

```bash
# Run all AI Metrics tests
docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase/Controller/Admin/AiMetricsControllerTest.php --testdox

# Run specific test
docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase/Controller/Admin/AiMetricsControllerTest.php --filter testDashboardAsAdmin

# With verbose output
docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase/Controller/Admin/AiMetricsControllerTest.php --testdox --verbose
```

## Success Criteria

✅ All authentication redirect tests pass (currently passing)  
✅ At least 80% of authenticated admin action tests pass  
✅ No HTTP 500 errors in test output  
✅ Database logging errors eliminated from logs  
✅ Test output shows meaningful assertions, not generic errors  

## Next Steps After Tests Pass

1. Apply same pattern to other Admin controller tests
2. Update TEST_FIXING_SUMMARY.md with final results
3. Document any patterns discovered for future test development
4. Consider creating test helper for common setup (database logging, fixtures, etc.)
