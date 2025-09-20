# CakePHP 5.x AI Adapter Quiz Integration Guide

## Overview
This guide provides step-by-step instructions for integrating the AI Adapter Quiz system into your existing CakePHP 5.x application from the willow repository.

## 1. Database Setup

### Run Migration
```bash
# Create the migration file in config/Migrations/
cp CreateAdapterQuizTables.php config/Migrations/20250906000001_CreateAdapterQuizTables.php

# Run the migration
bin/cake migrations migrate
```

### Seed Sample Data
```bash
# Create seeds for sample products
bin/cake bake seed Products
```

## 2. Model Integration

### Copy Model Files
```bash
# Copy to your src/Model/ directory
cp Product.php src/Model/Entity/
cp ProductsTable.php src/Model/Table/
```

### Create Additional Models
```bash
# Generate Quiz Submissions model
bin/cake bake model QuizSubmissions
```

## 3. Controller Setup

### Web Controllers
```bash
# Copy main controller
cp QuizController.php src/Controller/

# Create API namespace directory
mkdir -p src/Controller/Api/
cp ApiQuizController.php src/Controller/Api/QuizController.php
```

### Admin Controllers (Optional)
```bash
# Create admin controllers for management
bin/cake bake controller Admin/Products
bin/cake bake controller Admin/QuizSubmissions  
```

## 4. Routes Configuration

Add the routes to your `config/routes.php`:

```php
// Include the quiz routes
require_once 'quiz_routes.php';
```

Or copy the route definitions directly into your existing routes.php file.

## 5. View Templates

### Create View Directories
```bash
mkdir -p templates/Quiz/
mkdir -p templates/Products/
mkdir -p templates/Admin/Quiz/
```

### Basic Templates
Create these template files:

- `templates/Quiz/index.php` - Quiz selection page
- `templates/Quiz/akinator.php` - Akinator-style quiz
- `templates/Quiz/comprehensive.php` - Comprehensive quiz form
- `templates/Quiz/results.php` - Results display

## 6. Assets Integration

### CSS and JavaScript
```bash
# Create asset directories
mkdir -p webroot/css/quiz/
mkdir -p webroot/js/quiz/

# Copy CSS files
cp quiz-styles.css webroot/css/quiz/
cp quiz-akinator.css webroot/css/quiz/

# Copy JavaScript files  
cp quiz-app.js webroot/js/quiz/
cp quiz-api.js webroot/js/quiz/
```

### Asset Loading in Templates
```php
// In your layout or templates
<?= $this->Html->css(['quiz/quiz-styles', 'quiz/quiz-akinator']) ?>
<?= $this->Html->script(['quiz/quiz-app', 'quiz/quiz-api']) ?>
```

## 7. Configuration

### Add Configuration Settings
In `config/app_local.php`:

```php
'QuizSystem' => [
    'ai_enabled' => true,
    'confidence_threshold' => 0.6,
    'max_results' => 5,
    'enable_analytics' => true,
    'akinator_max_questions' => 20,
    'comprehensive_steps' => 6,
],
```

### Load Configuration in Controllers
```php
// In your controllers
$quizConfig = Configure::read('QuizSystem');
```

## 8. Testing

### Unit Tests
```bash
# Generate test files
bin/cake bake test Model/Table/ProductsTable
bin/cake bake test Controller/QuizController
bin/cake bake test Controller/Api/QuizController

# Run tests
bin/cake test
```

### Integration Tests
```bash
# Test API endpoints
bin/cake test --filter=ApiQuizControllerTest
```

## 9. API Usage Examples

### Starting a Quiz (JavaScript)
```javascript
// Start Akinator quiz
fetch('/api/quiz/start/akinator.json')
  .then(response => response.json())
  .then(data => {
    console.log('Quiz started:', data);
    displayQuestion(data.data.first_question);
  });

// Submit comprehensive quiz
const quizData = {
  quiz_type: 'comprehensive',
  answers: {
    device_type: 'laptop',
    manufacturer: 'apple',
    port_type: 'usbc',
    budget: [50, 100]
  }
};

fetch('/api/quiz/submit.json', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify(quizData)
})
.then(response => response.json())
.then(results => {
  displayResults(results.data.recommendations);
});
```

### Product Search
```javascript
// Search products with filters
const searchParams = new URLSearchParams({
  q: 'macbook charger',
  manufacturer: 'apple', 
  port_type: 'usbc',
  min_price: 20,
  max_price: 100,
  certified: true,
  limit: 10
});

fetch(`/api/products/search.json?${searchParams}`)
  .then(response => response.json())
  .then(data => {
    displayProducts(data.data.products);
  });
```

## 10. JSON Response Formats

### Quiz Start Response
```json
{
  "success": true,
  "data": {
    "session_id": "abc123",
    "quiz_type": "akinator",
    "quiz_info": {
      "title": "AI Adapter Genie",
      "max_questions": 10
    },
    "first_question": {
      "id": "q1",
      "question": "Is your device something you can hold in one hand?",
      "type": "binary",
      "options": [
        {"id": "yes", "label": "Yes"},
        {"id": "no", "label": "No"}
      ]
    },
    "started_at": "2025-09-06T12:00:00+00:00"
  }
}
```

### Quiz Results Response
```json
{
  "success": true,
  "data": {
    "submission_id": 123,
    "total_matches": 3,
    "overall_confidence": 0.847,
    "recommendations": [
      {
        "product": {
          "id": 1,
          "title": "MacBook Air USB-C Charger - 30W",
          "manufacturer": "Apple",
          "price": 59.00,
          "formatted_price": "$59.00",
          "rating": 4.8,
          "certified": true
        },
        "confidence_score": 0.923,
        "explanation": "This MacBook Air USB-C Charger is recommended because it matches your preferred Apple brand, has the USB-C port you need, and is officially certified for compatibility."
      }
    ],
    "submitted_at": "2025-09-06T12:05:00+00:00"
  }
}
```

## 11. Custom AI Logic Integration

### Extending the Matching Algorithm
```php
// In src/Model/Table/ProductsTable.php
public function findMatchingProducts(array $quizData): array
{
    // Add your custom AI logic here
    $aiProcessor = new CustomAIProcessor();
    $enhancedCriteria = $aiProcessor->enhanceCriteria($quizData);

    // Use enhanced criteria for matching
    $products = $this->find('byQuizCriteria', $enhancedCriteria)->toArray();

    // Apply custom scoring
    return $aiProcessor->scoreAndRankProducts($products, $quizData);
}
```

### External AI Service Integration
```php
// Example integration with OpenAI or other AI services
class AIRecommendationService
{
    public function generateExplanation($product, $userCriteria): string
    {
        $prompt = $this->buildPrompt($product, $userCriteria);

        // Call external AI service
        $response = $this->aiClient->complete($prompt);

        return $response['text'];
    }
}
```

## 12. Performance Optimization

### Caching
```php
// Cache quiz questions and product data
$questions = Cache::remember('quiz.akinator.questions', function() {
    return $this->loadAkinatorQuestions();
});

// Cache product search results
$cacheKey = 'products.search.' . md5(serialize($searchParams));
$products = Cache::remember($cacheKey, $searchResults, 3600); // 1 hour
```

### Database Indexing
```sql
-- Add indexes for better performance
CREATE INDEX idx_products_search ON products (manufacturer, port_type, device_cat);
CREATE INDEX idx_products_price ON products (price, rating);
CREATE INDEX idx_quiz_submissions_session ON quiz_submissions (session_id, created);
```

## 13. Security Considerations

### Rate Limiting
```php
// Add rate limiting middleware
$this->loadComponent('RateLimit', [
    'limit' => 100,
    'window' => 3600,
    'identifier' => 'ip'
]);
```

### Input Validation
```php
// Validate quiz submissions
public function validationDefault(Validator $validator): Validator
{
    $validator
        ->requirePresence('quiz_type')
        ->inList('quiz_type', ['comprehensive', 'akinator'])
        ->requirePresence('answers')
        ->notEmptyArray('answers');

    return $validator;
}
```

## 14. Deployment Checklist

- [ ] Run migrations on production database
- [ ] Copy all model, controller, and view files
- [ ] Update routes.php configuration  
- [ ] Copy and compile assets (CSS/JS)
- [ ] Set up configuration values
- [ ] Test all API endpoints
- [ ] Configure caching (Redis/Memcached)
- [ ] Set up logging and monitoring
- [ ] Configure rate limiting
- [ ] Test quiz flows end-to-end

## 15. Monitoring and Analytics

### Logging Quiz Activities
```php
// Add logging to track quiz usage
$this->log("Quiz started: {$quizType} by IP {$clientIp}", 'info');
$this->log("Quiz completed: {$submissionId} with confidence {$confidence}", 'info');
```

### Analytics Dashboard
Create admin views to track:
- Quiz completion rates
- Most popular device types
- Average confidence scores  
- Product recommendation success rates
- User conversion metrics

This integration guide provides everything needed to successfully implement the AI Adapter Quiz system into your CakePHP 5.x application while maintaining code quality and following framework best practices.
