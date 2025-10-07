# Critical Models Testing Guide

**Date:** 2025-10-07  
**Status:** Ready for Enhancement  
**Priority:** HIGH

This guide provides detailed specifications for enhancing the three most critical model tests in WillowCMS.

---

## Overview

Three critical models require comprehensive test coverage:

1. **SettingsTable** - Configuration management (20+ tests needed)
2. **ArticlesTable** - Core CMS functionality (25+ tests needed)
3. **ProductsTable** - Product management (30+ tests needed)

**Total Test Methods to Add:** ~75+

---

## 1. SettingsTable Enhancement

### Model Overview
- **Purpose:** Manages application configuration with typed values
- **Key Features:** Value type casting, validation, category grouping
- **Complexity:** Medium
- **Priority:** 1

### Current Test Status
- ✅ Test stubs generated
- ⏳ Needs comprehensive enhancement
- File: `app/tests/TestCase/Model/Table/SettingsTableTest.php`

### Test Methods Needed (20+)

#### Initialization Tests (2)
```php
testInitialize()
- Verify table, display field, primary key configuration
- Verify Timestamp behavior attached

testTableConfiguration()
- Verify table name is 'settings'
- Verify display field is 'category'
- Verify primary key is 'id'
```

#### Validation Tests (12)
```php
testValidationDefaultSuccess()
- Valid setting with all required fields

testValidationCategoryRequired()
- Missing category should fail

testValidationCategoryMaxLength()
- Category exceeding 255 chars should fail

testValidationKeyNameRequired()
- Missing key_name should fail

testValidationKeyNameMaxLength()
- key_name exceeding 255 chars should fail

testValidationValueTypeRequired()
- Missing value_type should fail

testValidationValueTypeInList()
- Only 'text', 'numeric', 'bool', 'textarea', 'select', 'select-page' allowed
- Invalid types should fail

testValidationValueRequired()
- Missing value should fail

testValidationValueCustomNumeric()
- value_type='numeric' requires numeric value
- Non-numeric should fail

testValidationValueCustomBool()
- value_type='bool' requires 0 or 1
- Other values should fail

testValidationValueCustomText()
- value_type='text' cannot be empty
- Empty string should fail

testValidationAllValueTypes()
- Test each valid value_type with appropriate value
```

#### getSettingValue() Method Tests (4)
```php
testGetSettingValueSingleSetting()
- Retrieve single setting by category and key_name
- Verify correct value returned with proper type casting

testGetSettingValueAllCategorySettings()
- Retrieve all settings for a category (key_name=null)
- Verify returns associative array

testGetSettingValueNonExistent()
- Request non-existent setting should return null

testGetSettingValueEmptyCategory()
- Request settings for non-existent category
- Verify returns empty array
```

#### Value Casting Tests (3)
```php
testCastValueBooleanTrue()
- Various truthy values ('1', 'true', 1, true) cast to true

testCastValueBooleanFalse()
- Various falsy values ('0', 'false', 0, false) cast to false

testCastValueNumeric()
- String numbers cast to integers
- Verify '42' becomes int 42

testCastValueString()
- Default type, values remain strings
```

#### CRUD Operations (3)
```php
testCreateSettingSuccess()
- Create new setting with valid data
- Verify saved and retrievable

testUpdateSettingSuccess()
- Update existing setting's value
- Verify changes persisted

testDeleteSettingSuccess()
- Delete setting
- Verify no longer exists
```

---

## 2. ArticlesTable Enhancement  

### Model Overview
- **Purpose:** Core CMS content management with multi-language support
- **Key Features:** Translation, SEO, slug generation, AI integration, menu management
- **Complexity:** Very High
- **Priority:** 2

### Current Test Status
- ✅ Test stubs generated  
- ⏳ Needs comprehensive enhancement
- File: `app/tests/TestCase/Model/Table/ArticlesTableTest.php`

### Test Methods Needed (25+)

#### Initialization Tests (3)
```php
testInitialize()
- Verify all behaviors attached (Timestamp, Translate, Slug, Commentable, Orderable, ImageAssociable, QueueableImage)
- Verify associations (Users, Tags, PageViews, Products)

testTranslateBehaviorConfiguration()
- Verify translatable fields configured correctly
- Verify defaultLocale setting

testAssociations()
- Test belongsTo Users
- Test belongsToMany Tags
- Test hasMany PageViews, Products
```

#### Validation Tests (6)
```php
testValidationDefaultSuccess()
testValidationUserIdRequired()
testValidationUserIdUuid()
testValidationTitleRequired()
testValidationTitleMaxLength()
testValidationBodyOptional()
testValidationImageOptional()
```

#### beforeSave Callback Tests (3)
```php
testBeforeSavePublicationDate()
- When is_published changes to true, published date set to now
- Verify timestamp set correctly

testBeforeSaveWordCount()
- When body is set or modified, word_count calculated
- Test with various HTML content
- Verify strips tags before counting

testBeforeSaveNoChanges()
- When neither is_published nor body changes
- Verify no unnecessary updates
```

#### afterSave Callback Tests (5)
```php
testAfterSaveArticleTaggingJob()
- When AI.articleTags enabled
- Verify ArticleTagUpdateJob queued

testAfterSaveSummaryGenerationJob()
- When AI.articleSummaries enabled and summary empty
- Verify ArticleSummaryUpdateJob queued

testAfterSaveSeoUpdateJob()
- When article published and AI.articleSEO enabled
- Verify ArticleSeoUpdateJob queued only if SEO fields empty

testAfterSaveTranslationJob()
- When article published and AI.articleTranslations enabled
- Verify TranslateArticleJob queued

testAfterSaveImageGenerationJob()
- When article published, kind='article', AI.imageGeneration enabled
- Verify image generation job queued if needed
```

#### Custom Finder Tests (8)
```php
testGetFeatured()
- Returns only kind='article', featured=1, is_published=1
- Ordered by lft ASC
- Results cached

testGetRootPages()
- Returns only kind='page', parent_id IS NULL, is_published=1
- Ordered by lft ASC

testGetMainMenuPages()
- Returns only kind='page', is_published=1, main_menu=1
- Ordered by lft ASC

testGetFooterMenuPages()
- Returns only kind='page', is_published=1, footer_menu=1
- Ordered by lft ASC

testGetFooterMenuPagesWithChildren()
- Returns parent pages and all children
- Test inheritance logic
- Verify no duplicates

testGetMainMenuPagesWithChildren()
- Returns parent pages and all children
- Test inheritance logic

testGetArchiveDates()
- Returns hierarchical array of years and months
- Only includes published articles with kind='article'
- Ordered DESC

testGetRecentArticles()
- Returns top 3 recent articles
- Only published, kind='article'
- Contains Users and Tags
```

---

## 3. ProductsTable Enhancement

### Model Overview
- **Purpose:** Simplified product management with verification and compatibility
- **Key Features:** Search, tagging, verification, reliability scoring, compatibility filtering
- **Complexity:** Very High  
- **Priority:** 3

### Current Test Status
- ✅ Test stubs generated
- ⏳ Needs comprehensive enhancement
- File: `app/tests/TestCase/Model/Table/ProductsTableTest.php`

### Test Methods Needed (30+)

#### Initialization Tests (3)
```php
testInitialize()
- Verify all behaviors and associations
- Verify slug behavior configured

testAssociations()
- Test belongsTo Users, Articles
- Test belongsToMany Tags
- Test hasMany ProductsReliability

testBehaviors()
- Verify Timestamp, Sluggable behaviors
```

#### Validation Tests (10)
```php
testValidationDefaultSuccess()
testValidationTitleRequired()
testValidationTitleMaxLength()
testValidationSlugRequired()
testValidationSlugUnique()
testValidationDescriptionOptional()
testValidationManufacturerOptional()
testValidationModelNumberOptional()
testValidationPriceDecimal()
testValidationIsPublishedBoolean()
testValidationFeaturedBoolean()
```

#### getPublishedProducts() Tests (4)
```php
testGetPublishedProductsBasic()
- Returns only is_published=true
- Contains Users, Tags, Articles
- Ordered by created DESC

testGetPublishedProductsFilterByTag()
- Filter by tag slug
- Verify only matching products returned

testGetPublishedProductsFilterByManufacturer()
- LIKE search on manufacturer

testGetPublishedProductsFilterByFeatured()
- Only featured products when featured=true
```

#### Search Tests (3)
```php
testSearchProductsByTitle()
testSearchProductsByDescription()
testSearchProductsByManufacturer()
testSearchProductsByModelNumber()
```

#### Related Products Tests (3)
```php
testGetRelatedProductsByTags()
- Returns products sharing tags
- Excludes current product
- Limited to specified count

testGetRelatedProductsNoTags()
- Returns empty array when product has no tags

testGetRelatedProductsLimit()
- Respects limit parameter
```

#### View Count Tests (2)
```php
testIncrementViewCount()
- Increments by 1
- Multiple calls increment correctly

testIncrementViewCountNonExistent()
- Gracefully handles non-existent product ID
```

#### Verification & Reliability Tests (3)
```php
testGetProductsByStatusPending()
testGetProductsByStatusApproved()
testGetProductsByStatusRejected()
```

#### Compatibility Filtering Tests (3)
```php
testGetByPortCompatibility()
testGetByDeviceCompatibility()
testGetCertifiedProducts()
```

---

## Testing Patterns from UsersTableTest

### Pattern 1: Organized by Concern
```php
// ============================================================
// Section Name
// ============================================================

/**
 * Test description
 *
 * @return void
 */
public function testMethodName(): void
{
    // Arrange
    $data = [
        //...
    ];
    
    // Act
    $result = $this->Table->method($data);
    
    // Assert
    $this->assertSomething($result);
}
```

### Pattern 2: Validation Testing
```php
// Test success case first
public function testValidationSuccess(): void
{
    $entity = $this->Table->newEntity($validData);
    $this->assertEmpty($entity->getErrors());
}

// Then test each validation rule
public function testValidationFieldRequired(): void
{
    $entity = $this->Table->newEntity($missingFieldData);
    $this->assertNotEmpty($entity->getError('field'));
    $this->assertArrayHasKey('_required', $entity->getError('field'));
}
```

### Pattern 3: Custom Finder Testing
```php
public function testCustomFinder(): void
{
    // Create test data
    $entity = $this->Table->newEntity($testData);
    $this->Table->save($entity);
    
    // Use finder
    $results = $this->Table->find('customFinder')->all();
    
    // Assert results
    $this->assertNotEmpty($results);
    foreach ($results as $result) {
        $this->assertEquals($expectedValue, $result->field);
    }
}
```

---

## Execution Plan

### Step 1: SettingsTable (Week 1)
1. Enhance test file with 20+ methods
2. Run tests: `docker compose exec willowcms vendor/bin/phpunit tests/TestCase/Model/Table/SettingsTableTest.php`
3. Fix schema issues if needed
4. Achieve 90%+ coverage
5. Document patterns used

### Step 2: ArticlesTable (Week 2)
1. Enhance test file with 25+ methods
2. Mock AI services and queue jobs
3. Test all behaviors and callbacks
4. Run tests incrementally
5. Achieve 85%+ coverage

### Step 3: ProductsTable (Week 3)
1. Enhance test file with 30+ methods
2. Test all search and filtering
3. Test verification workflows
4. Run tests incrementally
5. Achieve 90%+ coverage

---

## Success Criteria

### SettingsTable
- ✅ All validation rules tested
- ✅ getSettingValue() fully tested
- ✅ Value type casting verified
- ✅ 90%+ code coverage
- ✅ All tests passing

### ArticlesTable
- ✅ All behaviors tested
- ✅ beforeSave/afterSave callbacks verified
- ✅ All custom finders tested
- ✅ AI job integration mocked and tested
- ✅ 85%+ code coverage
- ✅ All tests passing

### ProductsTable
- ✅ All search methods tested
- ✅ Verification logic tested
- ✅ Compatibility filtering tested
- ✅ Related products logic verified
- ✅ 90%+ code coverage
- ✅ All tests passing

---

## Common Testing Utilities Needed

### Mock Services Trait
```php
trait MockServicesTrait
{
    protected function mockAIService(): void
    {
        // Mock Anthropic/Google services
    }
    
    protected function mockQueueService(): void
    {
        // Mock queue job creation
    }
}
```

### Test Data Builders
```php
trait TestDataBuildersTrait
{
    protected function buildValidSetting(): array
    {
        return [
            'category' => 'Test',
            'key_name' => 'test_setting',
            'value' => 'test_value',
            'value_type' => 'text',
        ];
    }
    
    protected function buildValidArticle(): array
    {
        // ...
    }
    
    protected function buildValidProduct(): array
    {
        // ...
    }
}
```

---

## References

- **Pattern Reference:** `app/tests/TestCase/Model/Table/UsersTableTest.php`
- **CakePHP Testing:** https://book.cakephp.org/5/en/development/testing.html
- **PHPUnit Docs:** https://phpunit.de/documentation.html
- **Project Documentation:** `docs/testing/MODEL_TESTS_PROGRESS.md`

---

**Next Action:** Use AI assistance to enhance each test file following this guide and the UsersTableTest pattern.
