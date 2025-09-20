# AI Adapter Quiz System - CakePHP 5.x Integration Files

## 📁 Complete File Structure

```
ai-adapter-quiz-system/
├── 🗄️ Database/
│   └── CreateAdapterQuizTables.php        # CakePHP migration file
│
├── 🎛️ Models/
│   ├── Product.php                        # Product entity with AI matching logic
│   └── ProductsTable.php                  # Product table with AI algorithms
│
├── 🎮 Controllers/
│   ├── QuizController.php                 # Main web interface controller
│   └── ApiQuizController.php              # JSON API endpoints controller
│
├── 🛣️ Routes/
│   └── quiz_routes.php                    # Complete routing configuration
│
├── 🎨 Templates/
│   ├── quiz_index.php                     # Quiz selection homepage
│   ├── quiz_akinator.php                  # Akinator-style quiz interface
│   └── quiz_results.php                   # AI recommendations display
│
├── 📊 Data/
│   ├── adapter_finder_quiz_structure.json # Comprehensive quiz JSON
│   ├── akinator_adapter_quiz.json         # Akinator quiz JSON  
│   └── adapter_products_database.csv      # Sample product data
│
├── 🌐 Web App Demo/
│   └── [Live Demo Application]            # Full working demo
│
├── 🔌 WordPress Plugins/
│   ├── ai-adapter-finder-pro.php          # Main WordPress plugin
│   ├── class-formidable-integration.php   # Formidable Forms integration
│   └── class-elementor-integration.php    # Elementor widget integration
│
└── 📖 Documentation/
    └── INTEGRATION_GUIDE.md               # Complete setup guide
```

## 🚀 Quick Start for CakePHP 5.x

### Step 1: Database Setup
```bash
# Copy migration file
cp CreateAdapterQuizTables.php config/Migrations/$(date +%Y%m%d%H%M%S)_CreateAdapterQuizTables.php

# Run migration
bin/cake migrations migrate

# Optional: Seed with sample data
bin/cake migrations seed --seed ProductsSeed
```

### Step 2: Install Models
```bash
# Copy model files to your CakePHP app
cp Product.php src/Model/Entity/
cp ProductsTable.php src/Model/Table/

# Generate QuizSubmissions model
bin/cake bake model QuizSubmissions
```

### Step 3: Install Controllers
```bash
# Copy controllers
cp QuizController.php src/Controller/

# Create API directory and copy API controller
mkdir -p src/Controller/Api/
cp ApiQuizController.php src/Controller/Api/QuizController.php
```

### Step 4: Setup Routes
Add to your `config/routes.php`:

```php
// Include quiz routes
$routes->scope('/', function (RouteBuilder $routes) {
    // Web interface routes
    $routes->connect('/quiz', ['controller' => 'Quiz', 'action' => 'index']);
    $routes->connect('/quiz/akinator', ['controller' => 'Quiz', 'action' => 'akinator']);
    $routes->connect('/quiz/comprehensive', ['controller' => 'Quiz', 'action' => 'comprehensive']);
    $routes->connect('/quiz/results', ['controller' => 'Quiz', 'action' => 'results']);
});

// API routes
$routes->scope('/api', function (RouteBuilder $routes) {
    $routes->setExtensions(['json']);

    $routes->scope('/quiz', function (RouteBuilder $routes) {
        $routes->get('/start/{type}', ['controller' => 'Api\Quiz', 'action' => 'start']);
        $routes->post('/next-question', ['controller' => 'Api\Quiz', 'action' => 'nextQuestion']);
        $routes->post('/submit', ['controller' => 'Api\Quiz', 'action' => 'submit']);
    });

    $routes->scope('/products', function (RouteBuilder $routes) {
        $routes->get('/search', ['controller' => 'Api\Quiz', 'action' => 'search']);
        $routes->get('/{id}', ['controller' => 'Api\Quiz', 'action' => 'product']);
    });
});
```

### Step 5: Install Templates
```bash
# Create template directories
mkdir -p templates/Quiz/

# Copy template files
cp quiz_index.php templates/Quiz/index.php
cp quiz_akinator.php templates/Quiz/akinator.php  
cp quiz_results.php templates/Quiz/results.php
```

### Step 6: Test Installation
```bash
# Test web interface
curl http://localhost/quiz

# Test API endpoints
curl http://localhost/api/quiz/start/akinator.json
curl http://localhost/api/products/search.json?q=macbook
```

## 🔗 API Endpoint Reference

### Quiz Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/api/quiz/start/{type}.json` | Start new quiz session |
| POST | `/api/quiz/next-question.json` | Get next question |
| POST | `/api/quiz/submit.json` | Submit quiz & get results |

### Product Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/api/products/search.json` | Search products |
| GET | `/api/products/{id}.json` | Get product details |

### Example API Usage

#### Start Akinator Quiz
```javascript
fetch('/api/quiz/start/akinator.json')
  .then(response => response.json())
  .then(data => {
    console.log('Quiz started:', data.data.first_question);
  });
```

#### Submit Comprehensive Quiz
```javascript
const answers = {
  device_type: 'laptop',
  manufacturer: 'apple',
  port_type: 'usbc',
  budget: [50, 100]
};

fetch('/api/quiz/submit.json', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    quiz_type: 'comprehensive',
    answers: answers
  })
})
.then(response => response.json())
.then(results => {
  console.log('Recommendations:', results.data.recommendations);
});
```

#### Search Products
```javascript
const params = new URLSearchParams({
  q: 'macbook charger',
  manufacturer: 'apple',
  port_type: 'usbc',
  min_price: 20,
  max_price: 100
});

fetch(`/api/products/search.json?${params}`)
  .then(response => response.json())
  .then(data => {
    console.log('Products found:', data.data.products);
  });
```

## 🧠 AI Algorithm Features

### Smart Product Matching
- **Weighted Confidence Scoring**: Each product gets a confidence score (0.0-1.0)
- **Multi-factor Analysis**: Considers manufacturer, port type, device category, price range
- **Learning Algorithm**: Improves recommendations based on user selections
- **Explanation Generation**: AI-powered explanations for each recommendation

### Akinator Decision Tree
- **Binary Elimination**: Efficiently narrows down options with yes/no questions
- **Dynamic Question Flow**: Next question determined by previous answers
- **Confidence Tracking**: Real-time confidence meter shows progress
- **Early Termination**: Stops when confidence threshold (85%) is reached

### Comprehensive Quiz Logic
- **Step-by-step Form**: 6 detailed questions covering all aspects
- **Conditional Logic**: Questions adapt based on previous answers
- **Range Inputs**: Budget sliders and multi-select options
- **Validation**: Client and server-side validation

## 📊 Database Schema

### Products Table
```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    manufacturer VARCHAR(100),
    port_type VARCHAR(50),
    form_factor VARCHAR(50),
    device_gender VARCHAR(20),
    device_cat VARCHAR(100),
    device_compatibility TEXT,
    price DECIMAL(10,2),
    rating DECIMAL(3,2),
    certified BOOLEAN DEFAULT FALSE,
    status VARCHAR(20) DEFAULT 'pending',
    rel_score DECIMAL(5,4),
    views INT DEFAULT 0,
    image_url VARCHAR(500),
    features JSON,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Quiz Submissions Table
```sql
CREATE TABLE quiz_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    session_id VARCHAR(50),
    quiz_type VARCHAR(20) DEFAULT 'comprehensive',
    quiz_data JSON,
    recommendations JSON,
    selected_product_id INT,
    confidence_score DECIMAL(5,4),
    ip_address VARCHAR(45),
    user_agent TEXT,
    completed BOOLEAN DEFAULT FALSE,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🎯 Configuration Options

Add to `config/app_local.php`:

```php
'QuizSystem' => [
    'ai_enabled' => true,
    'confidence_threshold' => 0.6,
    'max_results' => 5,
    'enable_analytics' => true,
    'akinator_max_questions' => 20,
    'comprehensive_steps' => 6,
    'cache_questions' => true,
    'cache_duration' => 3600, // 1 hour
],
```

## 🔒 Security Features

- **CSRF Protection**: Built-in CakePHP CSRF tokens
- **Input Validation**: Server-side validation for all inputs
- **Rate Limiting**: API endpoint rate limiting (configurable)
- **SQL Injection Prevention**: CakePHP ORM protection
- **XSS Protection**: Auto-escaping in templates

## 📈 Analytics & Monitoring

### Tracked Events
- Quiz start/completion rates
- Question abandonment points
- Product recommendation accuracy
- User selection patterns
- API endpoint performance

### Built-in Logging
```php
// Quiz completion logging
$this->log("Quiz completed: type={$quizType}, confidence={$confidence}", 'info');

// Product recommendation logging  
$this->log("Products recommended: count={$count}, session={$sessionId}", 'info');
```

## 🧪 Testing

### Unit Tests
```bash
# Test models
bin/cake test Model/Table/ProductsTableTest
bin/cake test Model/Entity/ProductTest

# Test controllers
bin/cake test Controller/QuizControllerTest
bin/cake test Controller/Api/QuizControllerTest
```

### API Testing
```bash
# Test API endpoints
curl -X GET http://localhost/api/quiz/start/akinator.json
curl -X POST http://localhost/api/quiz/submit.json -d '{"quiz_type":"comprehensive","answers":{"device_type":"laptop"}}'
```

## 🚀 Production Deployment

### Performance Optimization
- Enable OPCache for PHP
- Use Redis/Memcached for caching
- CDN for static assets
- Database query optimization

### Monitoring Setup
- Application performance monitoring (APM)
- Error tracking (Sentry, Bugsnag)
- Database monitoring
- API endpoint monitoring

## 📞 Support & Maintenance

### Regular Tasks
- Update product database
- Monitor quiz completion rates
- Review AI recommendation accuracy
- Update quiz questions based on analytics

### Troubleshooting
- Check database connections
- Verify API endpoint responses
- Monitor server logs for errors
- Test quiz flows end-to-end

## 🎉 Success Metrics

After successful integration, you should see:
- ✅ Quiz pages loading correctly
- ✅ API endpoints returning JSON responses
- ✅ Database tables populated
- ✅ AI recommendations being generated
- ✅ Analytics data being collected

## 📋 Integration Checklist

- [ ] Database migration completed
- [ ] Models installed and working
- [ ] Controllers copied and functional
- [ ] Routes configured properly
- [ ] Templates displaying correctly
- [ ] API endpoints responding
- [ ] JavaScript functionality working
- [ ] CSS styles applied
- [ ] Sample data loaded
- [ ] Quiz flows tested
- [ ] Analytics configured
- [ ] Error handling working
- [ ] Security measures in place
- [ ] Performance optimized
- [ ] Documentation updated

---

**Total Files Created:** 15+ files covering complete CakePHP 5.x integration
**Estimated Integration Time:** 2-4 hours for experienced CakePHP developers
**Production Ready:** Yes, with built-in security, validation, and error handling
**Scalable:** Supports high-traffic deployments with caching and optimization
