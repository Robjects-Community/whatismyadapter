# WillowCMS Test Fixtures Guide

## Overview

Test fixtures provide sample data for PHPUnit tests. WillowCMS has **34 comprehensive fixtures** covering all major models in the application.

## Available Fixtures

### Core Fixtures

#### UsersFixture
- **Location**: `tests/Fixture/UsersFixture.php`
- **Records**: 3 test users
- **Usage**: `'app.Users'`

**Test Users:**
1. **Admin User** (ID: `90d91e66-5d90-412b-aeaa-4d51fa110794`)
   - Email: `admin@example.com`
   - Username: `admin`
   - Role: `admin`
   - Active: Yes

2. **Regular User** (ID: `91d91e66-5d90-412b-aeaa-4d51fa110795`)
   - Email: `user@example.com`
   - Username: `user`
   - Role: `user`
   - Active: Yes

3. **Inactive User** (ID: `92d91e66-5d90-412b-aeaa-4d51fa110796`)
   - Email: `inactive@example.com`
   - Username: `inactive`
   - Role: `user`
   - Active: No

#### ArticlesFixture
- **Location**: `tests/Fixture/ArticlesFixture.php`
- **Records**: 1 sample article
- **Usage**: `'app.Articles'`
- **Features**: Full article with metadata, SEO fields, social media descriptions

#### ProductsFixture
- **Location**: `tests/Fixture/ProductsFixture.php`
- **Records**: 1 sample product
- **Usage**: `'app.Products'`
- **Features**: Complete product data with specifications, certifications, compatibility

#### SettingsFixture
- **Location**: `tests/Fixture/SettingsFixture.php`
- **Records**: 1 sample setting
- **Usage**: `'app.Settings'`
- **Features**: Configuration key-value pairs with types and descriptions

#### TagsFixture
- **Location**: `tests/Fixture/TagsFixture.php`
- **Records**: 1 sample tag
- **Usage**: `'app.Tags'`
- **Features**: Tag with metadata, tree structure support

### AI & Metrics Fixtures

#### AiMetricsFixture
- **Location**: `tests/Fixture/AiMetricsFixture.php`
- **Usage**: `'app.AiMetrics'`
- **Features**: AI service usage tracking, cost metrics

#### AipromptsFixture
- **Location**: `tests/Fixture/AipromptsFixture.php`
- **Usage**: `'app.Aiprompts'`
- **Features**: AI prompt templates and configurations

### Content Fixtures

#### ArticlesTagsFixture
- **Usage**: `'app.ArticlesTags'`
- **Purpose**: Article-tag relationships

#### ArticlesTranslationsFixture
- **Usage**: `'app.ArticlesTranslations'`
- **Purpose**: Multilingual article content

#### CommentsFixture
- **Usage**: `'app.Comments'`
- **Purpose**: User comments on articles

#### PagesFixture
- **Usage**: `'app.Pages'` (if exists)
- **Purpose**: Static page content

### Media & Image Fixtures

#### ImagesFixture
- **Usage**: `'app.Images'`
- **Purpose**: Image metadata

#### ImageGalleriesFixture
- **Usage**: `'app.ImageGalleries'`
- **Purpose**: Image gallery definitions

#### ImageGalleriesImagesFixture
- **Usage**: `'app.ImageGalleriesImages'`
- **Purpose**: Gallery-image relationships

#### ModelsImagesFixture
- **Usage**: `'app.ModelsImages'`
- **Purpose**: Polymorphic image attachments

### Product-Related Fixtures

#### ProductsTagsFixture
- **Usage**: `'app.ProductsTags'`
- **Purpose**: Product-tag relationships

#### ProductFormFieldsFixture
- **Usage**: `'app.ProductFormFields'`
- **Purpose**: Dynamic product form fields

#### ProductsReliabilityFixture
- **Usage**: `'app.ProductsReliability'`
- **Purpose**: Product reliability scores

#### ProductsReliabilityFieldsFixture
- **Usage**: `'app.ProductsReliabilityFields'`
- **Purpose**: Reliability field definitions

#### ProductsReliabilityLogsFixture
- **Usage**: `'app.ProductsReliabilityLogs'`
- **Purpose**: Reliability audit logs

#### CableCapabilitiesFixture
- **Usage**: `'app.CableCapabilities'`
- **Purpose**: Cable/adapter capabilities

#### PortTypesFixture
- **Usage**: `'app.PortTypes'`
- **Purpose**: Port type definitions

#### DeviceCompatibilityFixture
- **Usage**: `'app.DeviceCompatibility'`
- **Purpose**: Device compatibility data

### System Fixtures

#### SystemLogsFixture
- **Usage**: `'app.SystemLogs'`
- **Purpose**: System activity logs

#### PageViewsFixture
- **Usage**: `'app.PageViews'`
- **Purpose**: Page view analytics

#### BlockedIpsFixture
- **Usage**: `'app.BlockedIps'`
- **Purpose**: IP blocking data

#### CookieConsentsFixture
- **Usage**: `'app.CookieConsents'`
- **Purpose**: Cookie consent tracking

### Other Fixtures

#### EmailTemplatesFixture
- **Usage**: `'app.EmailTemplates'`
- **Purpose**: Email template definitions

#### InternationalisationsFixture
- **Usage**: `'app.Internationalisations'`
- **Purpose**: i18n translations

#### QueueConfigurationsFixture
- **Usage**: `'app.QueueConfigurations'`
- **Purpose**: Queue job configurations

#### QuizSubmissionsFixture
- **Usage**: `'app.QuizSubmissions'`
- **Purpose**: User quiz responses

#### SlugsFixture
- **Usage**: `'app.Slugs'`
- **Purpose**: URL slug management

#### TagsTranslationsFixture
- **Usage**: `'app.TagsTranslations'`
- **Purpose**: Multilingual tag content

#### UserAccountConfirmationsFixture
- **Usage**: `'app.UserAccountConfirmations'`
- **Purpose**: Account verification tokens

---

## Using Fixtures in Tests

### Basic Usage

In your test class, declare the fixtures you need:

```php
<?php
namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ArticlesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Articles',
        'app.Tags',
    ];

    public function testIndex(): void
    {
        $this->get('/articles');
        $this->assertResponseOk();
    }
}
```

### Accessing Fixture Data

```php
public function testWithFixtureData(): void
{
    // Get the Users table
    $users = $this->getTableLocator()->get('Users');
    
    // Fetch admin user from fixture
    $admin = $users->find()
        ->where(['email' => 'admin@example.com'])
        ->first();
    
    $this->assertEquals('admin', $admin->role);
}
```

### Testing with Relationships

```php
protected array $fixtures = [
    'app.Users',
    'app.Articles',
    'app.ArticlesTags',
    'app.Tags',
];

public function testArticleWithTags(): void
{
    $articles = $this->getTableLocator()->get('Articles');
    
    $article = $articles->find()
        ->contain(['Tags'])
        ->first();
    
    $this->assertNotEmpty($article->tags);
}
```

---

## Generating New Fixtures

When you need a fixture for a new model:

```bash
cd /Volumes/1TB_DAVINCI/docker/willow
docker compose exec -T willowcms bin/cake bake fixture ModelName
```

This will:
1. Analyze the database table schema
2. Generate a fixture file with proper structure
3. Add sample data placeholders

---

## Fixture Best Practices

### 1. Minimal Data
Only include the minimum data needed for tests:

```php
$this->records = [
    [
        'id' => 1,
        'title' => 'Test Article',
        'is_published' => 1,
        // Only include essential fields
    ],
];
```

### 2. Realistic IDs
Use UUIDs or sequential IDs that won't conflict:

```php
'id' => '90d91e66-5d90-412b-aeaa-4d51fa110794'  // Good
'id' => 1  // Also fine for testing
```

### 3. Descriptive Data
Make test data easy to identify:

```php
'email' => 'admin@example.com',  // Clear purpose
'title' => 'Test Article for Feature X',  // Descriptive
```

### 4. Multiple Scenarios
Include fixtures for different test scenarios:

```php
$this->records = [
    ['status' => 'active'],   // Normal case
    ['status' => 'inactive'], // Edge case
    ['status' => 'deleted'],  // Special case
];
```

---

## Common Fixture Patterns

### Controller Tests
```php
protected array $fixtures = [
    'app.Users',  // For authentication
    'app.ModelName',  // Primary model
];
```

### Admin Controller Tests
```php
protected array $fixtures = [
    'app.Users',  // Required for auth
    'app.Articles',  // Example model
    'app.Tags',  // Related model
];
```

### API Controller Tests
```php
protected array $fixtures = [
    'app.Users',  // For API auth if needed
    'app.Products',  // Data being queried
];
```

---

## Troubleshooting

### Missing Fixture Error
```
Error: Fixture app.ModelName not found
```

**Solution**: Generate the fixture:
```bash
docker compose exec -T willowcms bin/cake bake fixture ModelName
```

### Foreign Key Constraint Errors
```
Error: Cannot add or update a child row
```

**Solution**: Ensure parent fixtures are loaded first:
```php
protected array $fixtures = [
    'app.Users',  // Parent
    'app.Articles',  // Child (has user_id)
];
```

### Schema Mismatch
```
Error: Field 'column_name' doesn't have a default value
```

**Solution**: Update fixture records to include required fields or regenerate:
```bash
docker compose exec -T willowcms bin/cake bake fixture ModelName --force
```

---

## Testing Without Fixtures

For tests that don't need database data:

```php
class HelperTest extends TestCase
{
    // No fixtures needed
    protected array $fixtures = [];

    public function testUtilityFunction(): void
    {
        $result = SomeHelper::formatString('test');
        $this->assertEquals('Test', $result);
    }
}
```

---

## Performance Considerations

### Fixture Loading Time
- Each fixture adds ~10-50ms to test setup
- Only load fixtures you actually need
- Consider using transaction rollback instead of truncation

### Large Fixtures
For fixtures with many records:

```php
public function init(): void
{
    // Generate records programmatically
    $records = [];
    for ($i = 1; $i <= 100; $i++) {
        $records[] = [
            'id' => $i,
            'title' => "Test Article {$i}",
        ];
    }
    $this->records = $records;
    parent::init();
}
```

---

## References

- **CakePHP Fixtures Documentation**: https://book.cakephp.org/5/en/development/testing.html#fixtures
- **Test Refactoring Notes**: `docs/TEST_REFACTORING.md`
- **Controller Tests**: `docs/CONTROLLER_TESTS_COMPLETION_SUMMARY.md`
- **Existing Fixtures**: `app/tests/Fixture/`

---

## Summary

✅ **34 fixtures** available covering all major models  
✅ **3 test users** (admin, regular, inactive)  
✅ **Sample data** for articles, products, tags, settings  
✅ **Relationships** covered (tags, translations, images)  
✅ **AI metrics** for AI service testing  
✅ **Ready to use** in all controller tests
