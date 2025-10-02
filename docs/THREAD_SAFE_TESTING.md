# Thread-Safe Testing for WillowCMS

This guide outlines how to safely run tests across multiple Warp threads without interference, ensuring proper MVC component isolation.

## 🔧 Thread-Safe Testing Rule

When multiple Warp threads are working on WillowCMS simultaneously, each thread must use isolated testing environments to prevent interference between test runs, especially when focusing on specific MVC components.

## 🚀 Implementation

### 1. Thread Isolation Infrastructure

The testing framework provides complete isolation across:
- Database tables
- Cache storage
- Temporary files
- Log files

### 2. Thread-Safe Testing Script

```bash
#!/bin/bash
# File: tools/testing/run_tests.sh

# Parse arguments
COMPONENT=""
FILTER=""
THREAD_ID=""

while [[ "$#" -gt 0 ]]; do
    case $1 in
        --component=*) COMPONENT="${1#*=}" ;;
        --filter=*) FILTER="${1#*=}" ;;
        --thread=*) THREAD_ID="${1#*=}" ;;
        *) echo "Unknown parameter: $1"; exit 1 ;;
    esac
    shift
done

# Auto-detect thread ID if not provided
if [ -z "$THREAD_ID" ]; then
    # Use last 4 digits of process ID for uniqueness
    THREAD_ID=$(echo $$ | tail -c 4)
fi

# Set environment variables for isolation
export THREAD_ID=$THREAD_ID
export CAKEPHP_TEST_DATABASE_URL="mysql://root:password@mysql/willowcms_test_${THREAD_ID}"
export CACHE_PREFIX="willow_test_${THREAD_ID}_"
export TEST_TMP_DIR="/tmp/willow_test_${THREAD_ID}"
export TEST_LOGS_DIR="app/tests/logs/${THREAD_ID}"

# Create necessary directories
mkdir -p "$TEST_TMP_DIR"
mkdir -p "$TEST_LOGS_DIR"

echo "🧪 Running tests with thread ID: $THREAD_ID"
echo "📊 Test database: willowcms_test_${THREAD_ID}"

# Create isolated test database if needed
docker compose exec -T mysql mysql -u root -ppassword -e "CREATE DATABASE IF NOT EXISTS willowcms_test_${THREAD_ID};"

# Build the test command
TEST_CMD="docker compose exec -T"

# Add environment variables
TEST_CMD+=" -e THREAD_ID=${THREAD_ID}"
TEST_CMD+=" -e CAKEPHP_TEST_DATABASE_URL=${CAKEPHP_TEST_DATABASE_URL}"
TEST_CMD+=" -e CACHE_PREFIX=${CACHE_PREFIX}"
TEST_CMD+=" -e TEST_TMP_DIR=${TEST_TMP_DIR}"
TEST_CMD+=" -e TEST_LOGS_DIR=${TEST_LOGS_DIR}"

# Add the service and PHPUnit command
TEST_CMD+=" willowcms php vendor/bin/phpunit"

# Add component filter if specified
if [ -n "$COMPONENT" ]; then
    echo "🔍 Filtering by component: $COMPONENT"
    TEST_CMD+=" tests/TestCase/$COMPONENT"
fi

# Add specific filter if specified
if [ -n "$FILTER" ]; then
    echo "🔍 Filtering by test: $FILTER"
    TEST_CMD+=" --filter=$FILTER"
fi

# Execute the test command
echo "🚀 Executing: $TEST_CMD"
eval $TEST_CMD

# Return the exit status
exit $?
```

### 3. CakePHP Test Configuration

Add the following to `app/tests/bootstrap.php`:

```php
// Thread isolation configuration
$threadId = getenv('THREAD_ID') ?: 'default';

// Configure thread-specific cache prefix
Cache::setConfig('default', [
    'className' => 'File',
    'path' => CACHE,
    'prefix' => 'cake_test_' . $threadId . '_',
]);

// Configure thread-specific session settings
Configure::write('Session.defaults', 'php');
Configure::write('Session.cookie', 'cakephp_test_' . $threadId);

// Configure thread-specific log path
Log::setConfig('default', [
    'className' => 'File',
    'path' => getenv('TEST_LOGS_DIR') ?: LOGS,
    'file' => 'test_' . $threadId,
    'scopes' => false,
]);

// Configure thread-specific temporary directory
Configure::write('App.tmp', getenv('TEST_TMP_DIR') ?: TMP);
```

### 4. Thread Cleanup Script

```bash
#!/bin/bash
# File: tools/testing/cleanup_thread.sh

# Thread ID to clean up
THREAD_ID=$1

if [ -z "$THREAD_ID" ]; then
    echo "Error: Thread ID must be provided"
    echo "Usage: $0 <thread_id>"
    exit 1
fi

echo "🧹 Cleaning up thread ID: $THREAD_ID"

# Drop the test database
docker compose exec -T mysql mysql -u root -ppassword -e "DROP DATABASE IF EXISTS willowcms_test_${THREAD_ID};"

# Clean up temporary files
rm -rf "/tmp/willow_test_${THREAD_ID}"
rm -rf "app/tests/logs/${THREAD_ID}"

# Clear Redis cache with thread-specific prefix
docker compose exec -T redis redis-cli --scan --pattern "willow_test_${THREAD_ID}_*" | xargs -r docker compose exec -T redis redis-cli del

echo "✅ Cleanup complete for thread ID: $THREAD_ID"
```

## 🛠️ Usage Examples

### Testing Specific MVC Components

```bash
# Test only Controllers in thread 1234
./tools/testing/run_tests.sh --component=Controller --thread=1234

# Test only Models in thread 5678
./tools/testing/run_tests.sh --component=Model --thread=5678

# Test a specific controller in thread 9012
./tools/testing/run_tests.sh --filter=ArticlesController --thread=9012
```

### Sequential MVC Component Testing

```bash
# Test each component in isolation
./tools/testing/run_tests.sh --component=Model
./tools/testing/run_tests.sh --component=Controller
./tools/testing/run_tests.sh --component=Service
```

### Parallel Testing in Multiple Terminals

Terminal 1:
```bash
./tools/testing/run_tests.sh --component=Model --thread=1001
```

Terminal 2:
```bash
./tools/testing/run_tests.sh --component=Controller --thread=1002
```

Terminal 3:
```bash
./tools/testing/run_tests.sh --component=View --thread=1003
```

## 🧪 Integration with PHPUnit Groups

Mark tests with thread-aware group annotations:

```php
/**
 * @group model
 * @group thread-safe
 */
public function testArticleValidation(): void
{
    // Test code here
}
```

Then run specific test groups:

```bash
./tools/testing/run_tests.sh --filter=@model
```

## 🏆 Benefits

1. **Parallel Development**: Multiple developers can test simultaneously
2. **Thread Isolation**: Each thread has its own database, cache, and temp files
3. **Fast Feedback**: Test specific components without running the full suite
4. **Resource Efficiency**: Only test what you're working on
5. **CakePHP Compatibility**: Works with standard CakePHP testing framework

## 🧠 Best Practices

1. **Always specify a thread ID** when running multiple tests simultaneously
2. **Clean up after testing** using the cleanup script
3. **Use component-specific testing** for faster feedback
4. **Group related tests** for more efficient test runs
5. **Leverage CI/CD integration** for automated testing

## 🚀 Next Steps

- Integrate this approach into CI/CD pipelines
- Create test coverage reports per thread
- Implement parallelized test runners for faster test execution