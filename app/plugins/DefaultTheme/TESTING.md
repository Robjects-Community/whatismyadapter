# DefaultTheme Plugin Testing Guide

**Last Updated:** 2025-10-07  
**CakePHP Version:** 5.x  
**PHPUnit Version:** 10.5.55

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Test Structure](#test-structure)
3. [Running Tests](#running-tests)
4. [Test Coverage](#test-coverage)
5. [Writing Tests](#writing-tests)
6. [MVC Component Testing](#mvc-component-testing)
7. [Template/View Testing](#templateview-testing)
8. [Integration with Main App](#integration-with-main-app)
9. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

The DefaultTheme plugin provides the public-facing frontend for WillowCMS. This testing guide ensures comprehensive coverage of all MVC components, templates, and routes in the default theme.

### What Gets Tested

✅ **Plugin Infrastructure**
- Plugin initialization
- Configuration loading
- Route registration
- Middleware setup

✅ **Controllers**
- AppController functionality
- Component loading
- Request handling

✅ **Components**
- FrontEndSiteComponent (menu, tags, featured content)
- Data preparation for views
- Caching behavior

✅ **Templates/Views**
- Template rendering
- Variable passing
- Element rendering
- Helper usage

✅ **Integration**
- Plugin integration with main app
- Theme switching
- Public route handling

---

## 📁 Test Structure

```
plugins/DefaultTheme/
├── phpunit.xml.dist          # PHPUnit configuration
├── tests/
│   ├── bootstrap.php         # Test bootstrap
│   ├── TestCase/
│   │   ├── DefaultThemePluginTest.php       # Main plugin tests
│   │   ├── Controller/
│   │   │   ├── AppControllerTest.php        # Controller tests
│   │   │   └── Component/
│   │   │       └── FrontEndSiteComponentTest.php
│   │   └── View/
│   │       ├── AppViewTest.php              # View tests
│   │       ├── HomeViewTest.php             # Home page views
│   │       ├── ArticlesViewTest.php         # Article views
│   │       ├── ProductsViewTest.php         # Product views
│   │       └── UsersViewTest.php            # User views
│   └── Fixture/                # Test fixtures (if needed)
└── TESTING.md                  # This file
```

---

## 🚀 Running Tests

### Option 1: Docker (Recommended)

```bash
# From the willow project root
docker compose exec willowcms bash

# Navigate to plugin
cd plugins/DefaultTheme

# Run all plugin tests
../../vendor/bin/phpunit

# Run with testdox output
../../vendor/bin/phpunit --testdox

# Run specific test
../../vendor/bin/phpunit --filter FrontEndSiteComponentTest
```

### Option 2: Direct Execution

```bash
# From DefaultTheme plugin directory
cd /path/to/willow/app/plugins/DefaultTheme

# Run tests
../../vendor/bin/phpunit --configuration phpunit.xml.dist

# With colors and detailed output
../../vendor/bin/phpunit --testdox --colors=always
```

### Option 3: Run from Main App

```bash
# From willow root, run DefaultTheme tests
cd /path/to/willow
docker compose exec willowcms php vendor/bin/phpunit plugins/DefaultTheme
```

---

## 📊 Test Coverage

### Generating Coverage Reports

```bash
# HTML coverage report
../../vendor/bin/phpunit --coverage-html coverage/

# Text coverage summary
../../vendor/bin/phpunit --coverage-text

# View HTML report
open coverage/index.html
```

### Coverage Goals

| Component | Target | Current |
|-----------|--------|---------|
| Plugin Class | 100% | TBD |
| Controllers | 80%+ | TBD |
| Components | 90%+ | TBD |
| Views/Templates | 70%+ | TBD |
| Overall | 80%+ | TBD |

---

## ✍️ Writing Tests

### Test Class Template

```php
<?php
declare(strict_types=1);

namespace DefaultTheme\Test\TestCase;

use Cake\TestSuite\TestCase;

/**
 * YourClass Test Case
 */
class YourClassTest extends TestCase
{
    /**
     * Test fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Articles',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Setup code here
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Cleanup code here
        parent::tearDown();
    }

    /**
     * Test method description
     *
     * @return void
     */
    public function testSomething(): void
    {
        // Arrange
        $expected = 'expected value';
        
        // Act
        $actual = $this->methodUnderTest();
        
        // Assert
        $this->assertEquals($expected, $actual);
    }
}
```

---

## 🎮 MVC Component Testing

### Controller Testing

```php
use Cake\TestSuite\IntegrationTestTrait;

class AppControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public function testHomePageLoads(): void
    {
        $this->get('/');
        $this->assertResponseOk();
        $this->assertResponseContains('WillowCMS');
    }
}
```

### Component Testing

```php
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;

class FrontEndSiteComponentTest extends TestCase
{
    protected FrontEndSiteComponent $component;

    protected function setUp(): void
    {
        parent::setUp();
        $controller = new Controller();
        $registry = new ComponentRegistry($controller);
        $this->component = new FrontEndSiteComponent($registry);
    }

    public function testComponentSetsViewVariables(): void
    {
        // Test component behavior
        $this->component->beforeRender($event);
        $this->assertNotEmpty($controller->viewBuilder()->getVars());
    }
}
```

### Model Testing (if plugin has models)

```php
use Cake\ORM\TableRegistry;

class ArticlesTableTest extends TestCase
{
    protected $Articles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->Articles = TableRegistry::getTableLocator()->get('Articles');
    }

    public function testFindPublished(): void
    {
        $articles = $this->Articles->find('published')->toArray();
        $this->assertNotEmpty($articles);
    }
}
```

---

## 👁️ Template/View Testing

### View Testing Example

```php
use Cake\View\View;

class HomeViewTest extends TestCase
{
    protected View $view;

    protected function setUp(): void
    {
        parent::setUp();
        $this->view = new View();
        $this->view->setTemplatePath('Home');
    }

    public function testHomeTemplateRendersWithData(): void
    {
        $this->view->set('featuredArticles', [
            ['id' => 1, 'title' => 'Test Article']
        ]);
        
        $output = $this->view->render('index');
        
        $this->assertStringContainsString('Test Article', $output);
    }
}
```

### Element Testing

```php
public function testPaginationElementRenders(): void
{
    $this->view->set('paginator', $mockPaginator);
    
    $output = $this->view->element('pagination');
    
    $this->assertStringContainsString('<nav', $output);
    $this->assertStringContainsString('pagination', $output);
}
```

---

## 🔗 Integration with Main App

### Testing Plugin Routes

```php
public function testArticlePageRoute(): void
{
    $this->get('/articles');
    $this->assertResponseOk();
    $this->assertTemplate('DefaultTheme.Articles/index');
}
```

### Testing Theme Variables

```php
public function testThemeVariablesAreSet(): void
{
    $this->get('/');
    
    $viewVars = $this->viewVariable('menuPages');
    $this->assertNotNull($viewVars);
    $this->assertIsArray($viewVars);
}
```

---

## 🐛 Troubleshooting

### Common Issues

#### 1. **Tests Can't Find Templates**
```php
// Make sure you're testing in the right theme context
$this->get('/');
$this->assertTemplate('DefaultTheme.Home/index');
// Not just: $this->assertTemplate('Home/index');
```

#### 2. **Missing Fixtures**
```php
// Ensure fixtures are loaded
protected array $fixtures = [
    'app.Users',
    'app.Articles',
    'app.Tags',
];
```

#### 3. **Component Not Loading**
```php
// Make sure controller has the component loaded
$this->controller->loadComponent('DefaultTheme.FrontEndSite');
```

#### 4. **View Variables Not Set**
```php
// Check that beforeRender was triggered
$event = new Event('Controller.beforeRender', $this->controller);
$this->component->beforeRender($event);
```

### Debug Mode

```bash
# Run with debug output
../../vendor/bin/phpunit --debug --verbose

# Run single test with full output
../../vendor/bin/phpunit --filter testMethodName --debug
```

---

## 📚 Additional Resources

- [CakePHP 5.x Testing Documentation](https://book.cakephp.org/5/en/development/testing.html)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WillowCMS Testing Guide](../../docs/TESTING.md)
- [AdminTheme Testing Guide](../AdminTheme/TESTING.md)

---

## 📝 Test Checklist

Use this checklist to ensure comprehensive coverage:

### Plugin Infrastructure
- [ ] Plugin initializes correctly
- [ ] Plugin name and path are correct
- [ ] Routes are registered
- [ ] Middleware is configured (if any)

### Controllers
- [ ] AppController extends base controller
- [ ] Components are loaded
- [ ] All public methods have tests

### Components
- [ ] FrontEndSiteComponent sets all view variables
- [ ] Component skips admin routes
- [ ] Component handles user auth actions correctly
- [ ] Caching works as expected

### Templates
- [ ] Home/index renders correctly
- [ ] Articles templates render with data
- [ ] Products templates render with data
- [ ] Users templates (login, register) render
- [ ] Quiz templates render correctly
- [ ] Elements render properly

### Integration
- [ ] Plugin works with main application
- [ ] Routes are accessible
- [ ] Theme switching works
- [ ] All MVC components interact correctly

---

**Maintained by:** WillowCMS Development Team  
**Questions?** See main project documentation or create an issue
