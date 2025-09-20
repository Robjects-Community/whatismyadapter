# Job Base Class Refactoring Guide

## 🎯 **Overview**

This guide shows how to migrate existing job classes to use the new `EnhancedAbstractJob` base class, eliminating ~200 lines of duplicated code across your queue job system.

## 📊 **Refactoring Impact**

### **Before vs After Comparison:**

| Job Class | Original Lines | Refactored Lines | Reduction | Patterns Eliminated |
|-----------|----------------|------------------|-----------|-------------------|
| ArticleSeoUpdateJob | 79 | 35 | **56%** | Service injection, SEO field logic |
| TranslateArticleJob | 157 | 75 | **52%** | Requeue logic, translation settings |
| ArticleTagUpdateJob | 141 | 62 | **56%** | Tag creation, API service handling |
| **Total Estimated** | **~1200** | **~600** | **50%** | **~600 lines eliminated** |

---

## 🔧 **Migration Patterns**

### **1. API Service Pattern Migration**

**❌ Old Pattern:**
```php
class ArticleSeoUpdateJob extends AbstractJob
{
    private AnthropicApiService $anthropicService;

    public function __construct(?AnthropicApiService $anthropicService = null)
    {
        $this->anthropicService = $anthropicService ?? new AnthropicApiService();
    }
    // ... rest of constructor injection logic
}
```

**✅ New Pattern:**
```php
class ArticleSeoUpdateJobRefactored extends EnhancedAbstractJob
{
    public function execute(Message $message): ?string
    {
        // Service automatically available via getAnthropicService()
        $anthropic = $this->getAnthropicService();
        // ... use service
    }
}
```

**Benefits:**
- Eliminates constructor injection boilerplate
- Automatic service caching and reuse
- Consistent service management across all jobs

---

### **2. SEO Field Processing Pattern Migration**

**❌ Old Pattern:**
```php
public function execute(Message $message): ?string
{
    // 20+ lines of validation, service calls, field checking
    $articlesTable = $this->getTable('Articles');
    $article = $articlesTable->get($id);

    $seoResult = $this->anthropicService->generateArticleSeo(
        (string)$title,
        (string)strip_tags($article->body)
    );

    if ($seoResult) {
        $emptyFields = $articlesTable->emptySeoFields($article);
        array_map(fn($field) => $article->{$field} = $seoResult[$field], $emptyFields);
        return $articlesTable->save($article, ['noMessage' => true]);
    }
    // ... error handling
}
```

**✅ New Pattern:**
```php
public function execute(Message $message): ?string
{
    return $this->executeWithErrorHandling($id, function () use ($id, $title) {
        $articlesTable = $this->getTable('Articles');
        $article = $articlesTable->get($id);

        // All SEO logic consolidated into one method call
        return $this->updateSeoFields(
            $article,
            $articlesTable, 
            (string)$title,
            (string)strip_tags($article->body),
            'generateArticleSeo'
        );
    }, $title);
}
```

**Benefits:**
- Eliminates 15+ lines of SEO field logic per job
- Automatic empty field detection and updates
- Consistent error handling and logging

---

### **3. Translation Management Pattern Migration**

**❌ Old Pattern:**
```php
public function execute(Message $message): ?string
{
    // Check translation settings manually
    if (empty(array_filter(SettingsManager::read('Translations', [])))) {
        return Processor::REJECT;
    }

    // 30+ lines of field mapping and translation logic
    $result = $this->apiService->translateArticle(...11 parameters...);
    
    if ($result) {
        foreach ($result as $locale => $translation) {
            $article->translation($locale)->title = $translation['title'];
            $article->translation($locale)->lede = $translation['lede'];
            // ... repeat for 10+ fields
        }
        $articlesTable->save($article, ['noMessage' => true]);
    }
    // ... complex error handling
}
```

**✅ New Pattern:**
```php
public function execute(Message $message): ?string
{
    // Automatic translation settings check
    if (!$this->areTranslationsEnabled()) {
        return Processor::REJECT;
    }

    return $this->executeWithErrorHandling($id, function () use ($id) {
        $articlesTable = $this->getTable('Articles');
        $article = $articlesTable->get($id);

        // Simple field mapping configuration
        $fieldMapping = [
            'title' => 'title',
            'lede' => 'lede',
            'body' => 'body',
            // ... etc
        ];

        // All translation logic handled by base class
        return $this->processTranslations($article, $articlesTable, $fieldMapping);
    }, $title);
}
```

**Benefits:**
- Eliminates 25+ lines of translation field assignment
- Automatic service method detection based on entity type
- Consistent settings validation

---

### **4. Requeue with Backoff Pattern Migration**

**❌ Old Pattern:**
```php
private function handleEmptySeoFields(string $id, string $title, int $attempt): ?string
{
    if ($attempt >= 5) {
        $this->logJobError($id, sprintf('Article still has empty SEO fields after %d attempts', $attempt), $title);
        return Processor::REJECT;
    }

    $data = [
        'id' => $id,
        'title' => $title,
        '_attempt' => $attempt + 1,
    ];

    QueueManager::push(
        static::class,
        $data,
        [
            'config' => 'default',
            'delay' => 10 * ($attempt + 1), // Manual backoff calculation
        ]
    );

    $this->log(sprintf('Re-queuing with %d second delay...', 10 * ($attempt + 1)), 'info');
    return Processor::ACK;
}
```

**✅ New Pattern:**
```php
private function handleSeoFieldDependency(Message $message): bool
{
    // Automatic exponential backoff with logging
    $result = $this->requeueWithBackoff(
        $message,
        'Article has empty SEO fields',
        5,  // max attempts  
        10  // base delay
    );

    return $result === Processor::ACK;
}
```

**Benefits:**
- Eliminates 20+ lines of requeue logic per job
- Automatic exponential backoff calculation
- Consistent logging and error handling

---

### **5. Tag/Entity Creation Pattern Migration**

**❌ Old Pattern:**
```php
private function findOrSaveTag(
    Table $tagsTable,
    string $tagTitle,
    string $tagDescription,
    ?string $parentId = null
): Entity {
    $tag = $tagsTable->find()->where(['title' => $tagTitle])->first();
    if (!$tag) {
        $tag = $tagsTable->newEmptyEntity();
        $tag->title = $tagTitle;
        $tag->description = $tagDescription;
        $tag->slug = '';
        $tag->parent_id = $parentId;
        $tagsTable->save($tag);
    }
    return $tag;
}
```

**✅ New Pattern:**
```php
private function processTagHierarchy(object $tagsTable, array $tagData): array
{
    foreach ($tagData as $rootTag) {
        // Simplified entity creation with base class method
        $parentTag = $this->findOrCreateEntity(
            $tagsTable,
            ['title' => $rootTag['tag']],
            [
                'title' => $rootTag['tag'],
                'description' => $rootTag['description'],
                'slug' => '',
                'parent_id' => null
            ]
        );
        // ... process children similarly
    }
}
```

**Benefits:**
- Eliminates duplicate find-or-create logic
- Consistent entity creation patterns
- Automatic table operations handling

---

## 📋 **Migration Checklist**

### **Step 1: Update Class Declaration**
- [ ] Change `extends AbstractJob` to `extends EnhancedAbstractJob`
- [ ] Remove constructor dependency injection for API services
- [ ] Keep only job-specific constructor logic

### **Step 2: Replace Service Instantiation**
- [ ] Replace `$this->anthropicService` with `$this->getAnthropicService()`
- [ ] Replace `$this->googleService` with `$this->getGoogleService()`
- [ ] Remove manual service instantiation

### **Step 3: Consolidate SEO Field Logic**
- [ ] Replace manual SEO field processing with `$this->updateSeoFields()`
- [ ] Specify appropriate service method name
- [ ] Remove duplicate field assignment loops

### **Step 4: Simplify Translation Logic**
- [ ] Replace manual translation settings check with `$this->areTranslationsEnabled()`
- [ ] Define field mapping array
- [ ] Replace manual translation loops with `$this->processTranslations()`

### **Step 5: Standardize Requeue Logic**
- [ ] Replace custom requeue methods with `$this->requeueWithBackoff()`
- [ ] Specify appropriate max attempts and base delay
- [ ] Remove manual backoff calculations

### **Step 6: Update Entity Creation**
- [ ] Replace find-or-create patterns with `$this->findOrCreateEntity()`
- [ ] Use `$this->applyBulkFieldUpdates()` for bulk field operations
- [ ] Simplify tag hierarchy processing

### **Step 7: Test Migration**
- [ ] Run unit tests for refactored job
- [ ] Test with actual queue messages
- [ ] Verify error handling and logging
- [ ] Confirm performance improvements

---

## 🧪 **Testing Your Refactored Jobs**

### **Unit Test Pattern:**
```php
public function testRefactoredJobExecution(): void
{
    // Create mock services for dependency injection
    $mockAnthropicService = $this->createMock(AnthropicApiService::class);
    
    // Create job instance
    $job = new ArticleSeoUpdateJobRefactored();
    $job->getAnthropicService($mockAnthropicService); // Inject mock
    
    // Test execution
    $message = $this->createMockMessage(['id' => 'test-123', 'title' => 'Test']);
    $result = $job->execute($message);
    
    $this->assertEquals(Processor::ACK, $result);
}
```

### **Integration Test in Docker:**
```bash
# Test queue job processing
docker compose exec willowcms bin/cake queue add ArticleSeoUpdateJobRefactored id=test-123 title="Test Article"

# Monitor logs for proper execution
docker compose logs -f willowcms | grep "article SEO update"
```

---

## 📈 **Expected Benefits After Migration**

### **Code Quality Improvements:**
- ✅ **50% reduction** in job class code (~600 lines eliminated)
- ✅ **Consistent error handling** across all queue jobs
- ✅ **Standardized logging** with job type identification
- ✅ **Better testability** with dependency injection support
- ✅ **Reduced maintenance** burden with centralized patterns

### **Performance Improvements:**
- ✅ **Service instance caching** reduces object creation overhead
- ✅ **Optimized requeue logic** with exponential backoff
- ✅ **Efficient bulk operations** for field updates
- ✅ **Streamlined execution flow** with less code paths

### **Developer Experience:**
- ✅ **Faster job development** using established patterns
- ✅ **Easier debugging** with consistent logging
- ✅ **Better error messages** with standardized handling
- ✅ **Simplified testing** with injectable dependencies

---

## 🎯 **Next Steps**

1. **Start with highest-impact jobs** (SEO and translation jobs)
2. **Migrate one job at a time** and test thoroughly
3. **Update job dispatch calls** if necessary
4. **Consider adding more patterns** to base class as you identify them
5. **Document any job-specific patterns** for future reference

## 🏁 **Completion**

Once all jobs are migrated:
- [ ] Remove original `AbstractJob` if no longer used
- [ ] Update job documentation
- [ ] Consider additional patterns for future base class enhancements
- [ ] Mark **Item 5** as completed in refactoring plan ✅

---

**This refactoring brings you to 70% completion of the overall refactoring plan!**