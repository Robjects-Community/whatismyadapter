#!/bin/bash
# Automated Test Generation Script for WillowCMS
# Generates all missing test files based on coverage analysis

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

echo -e "${BLUE}🏭 WillowCMS Test Generation Factory${NC}"
echo -e "${BLUE}===================================${NC}"
echo ""

# Change to app directory
cd app

# Check if environment is running
if ! docker compose ps willowcms >/dev/null 2>&1; then
    echo -e "${RED}❌ Docker environment not running!${NC}"
    echo -e "${YELLOW}💡 Run: ./run_dev_env.sh${NC}"
    exit 1
fi

# Phase 1: Generate Critical Controller Tests
echo -e "${PURPLE}🎯 Phase 1: Generating Critical Controller Tests${NC}"
echo -e "${BLUE}=================================================${NC}"

CRITICAL_CONTROLLERS=(
    "Home"
    "Pages" 
    "Articles"
    "Auth"
    "Admin/Articles"
    "Admin/Pages" 
    "Admin/Users"
    "Admin/Settings"
    "Api/Products"
)

for controller in "${CRITICAL_CONTROLLERS[@]}"; do
    controller_path=$(echo "$controller" | sed 's/\//_/g')
    test_file="tests/TestCase/Controller/${controller}ControllerTest.php"
    
    if [ ! -f "$test_file" ]; then
        echo -e "${YELLOW}🔨 Generating ${controller}Controller test...${NC}"
        
        # Create directory if needed
        mkdir -p "$(dirname "$test_file")"
        
        # Generate basic controller test
        cat > "$test_file" << EOF
<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller$(echo "$controller" | sed 's/\//\\/g');

use App\Test\TestCase\WillowControllerTestCase;

/**
 * ${controller}Controller Test Case
 *
 * @group controller
 * @group thread-safe
 */
class $(basename "$controller")ControllerTest extends WillowControllerTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    protected \$fixtures = [
        'app.Users',
        'app.Articles',
        'app.Pages',
        'app.Settings',
    ];

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        \$this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        \$this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method - requires authentication
     *
     * @return void
     */
    public function testAdd(): void
    {
        \$this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method - requires authentication
     *
     * @return void
     */
    public function testEdit(): void
    {
        \$this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method - requires authentication
     *
     * @return void
     */
    public function testDelete(): void
    {
        \$this->markTestIncomplete('Not implemented yet.');
    }
}
EOF
        echo -e "${GREEN}✓ Created $test_file${NC}"
    else
        echo -e "${GREEN}✓ $test_file already exists${NC}"
    fi
done

# Phase 2: Generate Critical Model Tests
echo ""
echo -e "${PURPLE}🎯 Phase 2: Generating Critical Model Tests${NC}"
echo -e "${BLUE}============================================${NC}"

CRITICAL_TABLES=(
    "Articles"
    "Pages"
    "Users" 
    "Tags"
    "Settings"
    "Comments"
)

for table in "${CRITICAL_TABLES[@]}"; do
    test_file="tests/TestCase/Model/Table/${table}TableTest.php"
    
    if [ ! -f "$test_file" ]; then
        echo -e "${YELLOW}🔨 Generating ${table}Table test...${NC}"
        
        mkdir -p "$(dirname "$test_file")"
        
        cat > "$test_file" << EOF
<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Test\TestCase\WillowTestCase;
use Cake\TestSuite\TestCase;

/**
 * ${table}Table Test Case
 *
 * @group model
 * @group table
 * @group thread-safe
 */
class ${table}TableTest extends WillowTestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\\${table}Table
     */
    protected \$${table};

    /**
     * Fixtures
     *
     * @var array
     */
    protected \$fixtures = [
        'app.${table}',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        \$config = \$this->getTableLocator()->exists('${table}') ? [] : ['className' => '${table}Table'];
        \$this->$(echo ${table} | awk '{print tolower($0)}') = \$this->getTableLocator()->get('${table}', \$config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset(\$this->$(echo ${table} | awk '{print tolower($0)}'));
        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        \$this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        \$this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test entity creation
     *
     * @return void
     */
    public function testCreateEntity(): void
    {
        \$this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test entity saving
     *
     * @return void
     */
    public function testSaveEntity(): void
    {
        \$this->markTestIncomplete('Not implemented yet.');
    }
}
EOF
        echo -e "${GREEN}✓ Created $test_file${NC}"
    else
        echo -e "${GREEN}✓ $test_file already exists${NC}"
    fi
done

# Phase 3: Generate Service Tests
echo ""
echo -e "${PURPLE}🎯 Phase 3: Generating Critical Service Tests${NC}"
echo -e "${BLUE}============================================${NC}"

CRITICAL_SERVICES=(
    "Settings/SettingsService"
    "WebpageExtractor"
    "IpSecurityService"
    "LogChecksumService"
    "CacheService"
    "Ai/TagDetectionService"
    "Api/Anthropic/AnthropicApiService"
)

for service in "${CRITICAL_SERVICES[@]}"; do
    service_path="src/Service/${service}.php"
    test_path="tests/TestCase/Service/${service}Test.php"
    
    if [ -f "$service_path" ] && [ ! -f "$test_path" ]; then
        echo -e "${YELLOW}🔨 Generating $(basename "$service") test...${NC}"
        
        mkdir -p "$(dirname "$test_path")"
        
        service_name=$(basename "$service")
        service_namespace=$(dirname "$service" | sed 's/\//\\/g')
        
        cat > "$test_path" << EOF
<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\\${service_namespace};

use App\Test\TestCase\WillowTestCase;

/**
 * ${service_name} Test Case
 *
 * @group service
 * @group thread-safe
 */
class ${service_name}Test extends WillowTestCase
{
    /**
     * Test subject
     *
     * @var \App\Service\\${service_namespace}\\${service_name}
     */
    protected \$service;

    /**
     * Fixtures
     *
     * @var array
     */
    protected \$fixtures = [
        'app.Settings',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Service instantiation will depend on the specific service
        \$this->markTestIncomplete('Service setup not implemented yet.');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset(\$this->service);
        parent::tearDown();
    }

    /**
     * Test basic functionality
     *
     * @return void
     */
    public function testBasicFunctionality(): void
    {
        \$this->markTestIncomplete('Not implemented yet.');
    }
}
EOF
        echo -e "${GREEN}✓ Created $test_path${NC}"
    else
        if [ ! -f "$service_path" ]; then
            echo -e "${YELLOW}⚠️  Service file $service_path does not exist${NC}"
        else
            echo -e "${GREEN}✓ $test_path already exists${NC}"
        fi
    fi
done

# Phase 4: Generate Route Tests
echo ""
echo -e "${PURPLE}🎯 Phase 4: Generating Route Tests${NC}"
echo -e "${BLUE}==================================${NC}"

route_test_file="tests/TestCase/RoutingTest.php"

if [ ! -f "$route_test_file" ]; then
    echo -e "${YELLOW}🔨 Generating routing test...${NC}"
    
    cat > "$route_test_file" << EOF
<?php
declare(strict_types=1);

namespace App\Test\TestCase;

use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

/**
 * Routing Test Case
 *
 * Tests all application routes to ensure they are properly configured
 *
 * @group routing
 * @group integration
 * @group thread-safe
 */
class RoutingTest extends WillowControllerTestCase
{
    /**
     * Test core routes
     *
     * @return void
     */
    public function testCoreRoutes(): void
    {
        // Test home route
        \$this->get('/');
        \$this->assertResponseCode([200, 302]); // May redirect to localized version
        
        // Test robots.txt
        \$this->get('/robots.txt');
        \$this->assertResponseOk();
        \$this->assertContentType('text/plain');
        
        // Test sitemap.xml
        \$this->get('/sitemap.xml');
        \$this->assertResponseOk();
        \$this->assertContentType('application/xml');
    }

    /**
     * Test localized routes
     *
     * @return void
     */
    public function testLocalizedRoutes(): void
    {
        // Test English routes
        \$this->get('/en');
        \$this->assertResponseCode([200, 302]);
        
        \$this->get('/en/articles');
        \$this->assertResponseCode([200, 404]); // 404 if no articles
    }

    /**
     * Test admin routes require authentication
     *
     * @return void
     */
    public function testAdminRoutesRequireAuth(): void
    {
        \$this->assertRequiresAuth('GET', '/admin');
        \$this->assertRequiresAuth('GET', '/admin/articles');
        \$this->assertRequiresAuth('GET', '/admin/pages');
        \$this->assertRequiresAuth('GET', '/admin/users');
    }

    /**
     * Test API routes
     *
     * @return void
     */
    public function testApiRoutes(): void
    {
        // API routes should return 401/403 without proper authentication
        \$this->get('/api/products');
        \$this->assertResponseCode([401, 403, 405]); // 405 if method not allowed
    }

    /**
     * Test parameter validation in routes
     *
     * @return void
     */
    public function testRouteParameterValidation(): void
    {
        // Test invalid IDs
        \$this->get('/articles/view/invalid-id');
        \$this->assertResponseCode([404, 500]);
        
        // Test valid ID format (if any articles exist)
        \$this->get('/articles/view/1');
        \$this->assertResponseCode([200, 404]); // 404 if article doesn't exist
    }
}
EOF
    echo -e "${GREEN}✓ Created $route_test_file${NC}"
else
    echo -e "${GREEN}✓ $route_test_file already exists${NC}"
fi

# Generate test runner script
echo ""
echo -e "${PURPLE}🎯 Phase 5: Generating Test Execution Scripts${NC}"
echo -e "${BLUE}=============================================${NC}"

test_runner_file="../tools/testing/run_component_tests.sh"

if [ ! -f "$test_runner_file" ]; then
    echo -e "${YELLOW}🔨 Generating component test runner...${NC}"
    
    cat > "$test_runner_file" << 'EOF'
#!/bin/bash
# Component-specific test runner for WillowCMS
# Runs tests by MVC component with thread safety

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

COMPONENT=$1
THREAD_ID=${2:-$(echo $$ | tail -c 4)}

if [ -z "$COMPONENT" ]; then
    echo -e "${RED}Usage: $0 <component> [thread_id]${NC}"
    echo -e "${YELLOW}Components: Controller, Model, Service, Integration, All${NC}"
    exit 1
fi

echo -e "${BLUE}🧪 Running $COMPONENT tests (Thread: $THREAD_ID)${NC}"

case $COMPONENT in
    "Controller")
        ../tools/testing/run_tests.sh --component=Controller --thread=$THREAD_ID --coverage
        ;;
    "Model") 
        ../tools/testing/run_tests.sh --component=Model --thread=$THREAD_ID --coverage
        ;;
    "Service")
        ../tools/testing/run_tests.sh --component=Service --thread=$THREAD_ID --coverage
        ;;
    "Integration")
        ../tools/testing/run_tests.sh --component=Integration --thread=$THREAD_ID --coverage
        ;;
    "All")
        echo -e "${BLUE}Running all components sequentially...${NC}"
        ../tools/testing/run_tests.sh --component=Controller --thread=${THREAD_ID} --stop-on-failure
        ../tools/testing/run_tests.sh --component=Model --thread=${THREAD_ID} --stop-on-failure  
        ../tools/testing/run_tests.sh --component=Service --thread=${THREAD_ID} --stop-on-failure
        ../tools/testing/run_tests.sh --component=Integration --thread=${THREAD_ID} --stop-on-failure
        ;;
    *)
        echo -e "${RED}Unknown component: $COMPONENT${NC}"
        exit 1
        ;;
esac

echo -e "${GREEN}✅ $COMPONENT tests completed${NC}"
EOF

    chmod +x "$test_runner_file"
    echo -e "${GREEN}✓ Created $test_runner_file${NC}"
else
    echo -e "${GREEN}✓ $test_runner_file already exists${NC}"
fi

# Summary
echo ""
echo -e "${PURPLE}📊 Test Generation Summary${NC}"
echo -e "${BLUE}===========================${NC}"

TOTAL_GENERATED=0

for category in "Controller" "Model/Table" "Service"; do
    count=$(find "tests/TestCase/$category" -name "*Test.php" -newer . 2>/dev/null | wc -l || echo 0)
    echo -e "${GREEN}✓ $category tests: $count${NC}"
    TOTAL_GENERATED=$((TOTAL_GENERATED + count))
done

echo -e "${BLUE}📈 Total test files: $TOTAL_GENERATED${NC}"
echo ""
echo -e "${GREEN}🎉 Test generation complete!${NC}"
echo ""
echo -e "${YELLOW}💡 Next steps:${NC}"
echo -e "${BLUE}1. Review generated test files and implement test logic${NC}"
echo -e "${BLUE}2. Run tests: ./tools/testing/run_component_tests.sh Controller${NC}"
echo -e "${BLUE}3. Add missing fixtures and update test data${NC}"
echo -e "${BLUE}4. Gradually replace markTestIncomplete() with real tests${NC}"
echo ""
echo -e "${GREEN}🛠️  Example test command:${NC}"
echo -e "${BLUE}   ./tools/testing/run_tests.sh --component=Controller --thread=\$(date +%s) --coverage${NC}"