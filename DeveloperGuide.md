# 🚀 Willow CMS Developer Guide

> **Comprehensive development documentation for contributing to Willow CMS**

This guide provides everything developers need to understand, contribute to, and extend Willow CMS. Whether you're a new contributor or an experienced developer, this guide will help you navigate the codebase and development workflows effectively.

---

## 📋 Table of Contents

1. [🏁 Getting Started](#-getting-started)
   - [Development Environment Setup](#development-environment-setup)
   - [Essential Shell Aliases](#essential-shell-aliases)
   - [Project Structure Overview](#project-structure-overview)

2. [🏗️ Architecture Deep Dive](#️-architecture-deep-dive)
   - [MVC Pattern Implementation](#mvc-pattern-implementation)
   - [Plugin-Based Theming](#plugin-based-theming)
   - [Behavior System](#behavior-system)
   - [Queue-Based Processing](#queue-based-processing)

3. [💻 Development Workflow](#-development-workflow)
   - [Feature Development Process](#feature-development-process)
   - [Database Migrations](#database-migrations)
   - [Code Generation with Bake](#code-generation-with-bake)
   - [Best Practices](#best-practices)

4. [🧪 Testing & Quality Assurance](#-testing--quality-assurance)
   - [Unit Testing](#unit-testing)
   - [Code Coverage](#code-coverage)
   - [Code Standards](#code-standards)
   - [Continuous Integration](#continuous-integration)

5. [🤖 AI Integration](#-ai-integration)
   - [Anthropic API Services](#anthropic-api-services)
   - [Google Translate API](#google-translate-api)
   - [Custom AI Extensions](#custom-ai-extensions)

6. [🌍 Internationalization](#-internationalization)
   - [Multi-Language Support](#multi-language-support)
   - [Translation Workflow](#translation-workflow)
   - [Locale Management](#locale-management)

7. [⚙️ Configuration & Environment](#️-configuration--environment)
   - [Environment Variables](#environment-variables)
   - [Docker Development Environment](#docker-development-environment)
   - [Production Considerations](#production-considerations)

8. [🔧 Tools & Utilities](#-tools--utilities)
   - [Management Tool](#management-tool)
   - [Command Line Tools](#command-line-tools)
   - [Development Aliases](#development-aliases)

---

## 🏁 Getting Started

### Development Environment Setup

Willow CMS uses Docker to provide a consistent development environment. The setup process is streamlined for quick onboarding:

```bash
# Clone the repository
git clone git@github.com:matthewdeaves/willow.git
cd willow/

# Start the development environment
./setup_dev_env.sh

# Optional: Start with Jenkins CI
./setup_dev_env.sh --jenkins
```

**What this sets up:**
- 🌐 **Nginx + PHP-FPM**: Web server with PHP 8.1+ support
- 🗄️ **MySQL 8.0+**: Database server with development data
- 🚀 **Redis**: Caching and queue management
- 📧 **Mailpit**: Email testing interface
- 🔍 **phpMyAdmin**: Database management interface
- 🏗️ **Jenkins** (optional): Continuous integration server

### Essential Shell Aliases

Install development aliases for improved productivity:

```bash
# Install aliases (supports bash and zsh)
./setup_dev_aliases.sh
```

**Key aliases available:**
- `cake_shell` - Execute CakePHP console commands
- `willowcms_exec` - Run commands in the container
- `willowcms_shell` - Interactive container shell
- `phpunit` - Run unit tests
- `phpcs_sniff` - Check code standards
- `phpcs_fix` - Auto-fix code violations
- `cake_queue_worker` - Start background job processing

**Git Integration:**
The alias setup also installs a pre-push hook that automatically runs PHPUnit tests before allowing code to be pushed to the repository.

### Project Structure Overview

```
willow/
├── 📁 src/                          # Core application code
│   ├── 🎮 Controller/              # Frontend controllers
│   │   └── Admin/                  # Admin backend controllers
│   ├── 📊 Model/                   # Data models and entities
│   │   ├── Behavior/              # Reusable model behaviors
│   │   ├── Entity/                # Entity classes
│   │   └── Table/                 # Table classes with business logic
│   ├── 🔌 Service/Api/             # AI and external API integrations
│   ├── ⚡ Job/                     # Background job classes
│   ├── 🛠️ Command/                 # CLI command tools
│   ├── 🛡️ Middleware/              # Security and request processing
│   └── 🔧 Utility/                 # Helper and utility classes
├── 🎨 plugins/                     # Plugin-based themes
│   ├── AdminTheme/                # Administrative interface
│   └── DefaultTheme/              # Public website theme
├── ⚙️ config/                      # Configuration files
│   ├── Migrations/                # Database migration files
│   └── schema/                    # Database schema files
├── 🧪 tests/                       # Unit and integration tests
│   ├── TestCase/                  # Test classes
│   └── Fixture/                   # Test data fixtures
├── 🐳 docker/                      # Docker configuration
├── 📁 webroot/                     # Public web assets
└── 🔧 manage.sh                    # Interactive management tool
```

---

## 🏗️ Architecture Deep Dive

### MVC Pattern Implementation

Willow CMS follows CakePHP's strict MVC architecture with clear separation of concerns:

#### **Models** (`src/Model/`)
- **Table Classes**: Business logic and database interactions
- **Entity Classes**: Data representation and validation
- **Behaviors**: Reusable functionality across models

```php
// Example: ArticlesTable.php - Business logic
class ArticlesTable extends Table {
    public function initialize(array $config): void {
        $this->addBehavior('Sluggable');
        $this->addBehavior('ImageAssociable');
        $this->addBehavior('Translate', ['fields' => ['title', 'body']]);
    }
}

// Example: Article.php - Entity with validation
class Article extends Entity {
    protected $_accessible = [
        'title' => true,
        'body' => true,
        'published' => true,
    ];
}
```

#### **Views** (Plugin-based templates)
- **AdminTheme**: Bootstrap-based admin interface
- **DefaultTheme**: Public-facing website templates
- **Element System**: Reusable template components

#### **Controllers** (`src/Controller/`)
- **Frontend Controllers**: Public website logic
- **Admin Controllers**: Administrative backend functionality
- **Component System**: Shared controller functionality

### Plugin-Based Theming

Willow CMS uses CakePHP plugins for complete theme separation:

```
plugins/AdminTheme/
├── src/
│   ├── Controller/AppController.php    # Admin base controller
│   ├── View/AppView.php               # Admin view configuration
│   └── Command/Bake/                  # Custom bake templates
├── templates/
│   ├── Admin/                         # Admin CRUD templates
│   ├── element/                       # Reusable elements
│   └── layout/                        # Admin layouts
└── webroot/
    ├── css/                           # Admin stylesheets
    └── js/                            # Admin JavaScript
```

### Behavior System

Powerful reusable behaviors extend model functionality:

#### **ImageAssociableBehavior**
```php
// Automatically handles image associations via pivot table
$this->addBehavior('ImageAssociable');

// Usage in entities
$article->associated_images; // Returns associated images
$article->largeImageUrl;     // Gets optimized image URL
$article->thumbnailImageUrl; // Gets thumbnail URL
```

#### **SlugBehavior**
```php
// SEO-friendly URLs with history tracking and automatic redirects
$this->addBehavior('Sluggable', [
    'slug' => ['source' => 'title'],
    'history' => true  // Maintains slug history for SEO redirects
]);

// Automatic slug generation and conflict resolution
// Handles URL changes gracefully with 301 redirects
```

#### **OrderableBehavior**
```php
// Drag-and-drop ordering functionality with position management
$this->addBehavior('Orderable', [
    'order_field' => 'sort_order',
    'scope' => ['gallery_id'] // Optional: scope ordering within groups
]);

// Automatic position calculation and reordering
$this->moveUp($entity);    // Move item up in order
$this->moveDown($entity);  // Move item down in order
```

#### **CommentableBehavior**
```php
// Universal commenting system with AI moderation
$this->addBehavior('Commentable');

// Automatic comment analysis and moderation
// Integration with CommentAnalysisJob for spam detection
```

#### **QueueableImageBehavior** 
```php
// Automated background image processing
$this->addBehavior('QueueableImage');

// Automatically queues ProcessImageJob and ImageAnalysisJob
// Handles thumbnail generation, alt text creation, and metadata extraction
```

#### **TranslateBehavior Integration**
```php
// Multi-language content support
$this->addBehavior('Translate', [
    'fields' => ['title', 'body', 'meta_description'],
    'defaultLocale' => 'en',
    'allowEmptyTranslations' => true
]);

// Automatic translation via TranslateArticleJob
// Locale-aware content retrieval and caching
```

### Queue-Based Processing

Heavy operations are processed asynchronously using CakePHP Queue:

```php
// Queue an AI job
$this->queue->push(ArticleSeoUpdateJob::class, [
    'article_id' => $article->id,
    'locale' => 'en'
]);

// Background job example
class ArticleSeoUpdateJob implements JobInterface {
    public function execute(array $data): void {
        $seoGenerator = new SeoContentGenerator();
        $seoGenerator->generateSeoContent($data['article_id']);
    }
}
```

**Key background jobs:**
- `ProcessImageJob` - Image resizing and optimization
- `ImageAnalysisJob` - AI-powered image analysis and alt text generation
- `ArticleSeoUpdateJob` - AI-generated SEO content and meta descriptions
- `ArticleTagUpdateJob` - Automatic tag generation based on content analysis
- `ArticleSummaryUpdateJob` - AI-powered content summarization
- `TranslateArticleJob` - Multi-language content translation
- `TranslateI18nJob` - Interface translation for all supported languages
- `TranslateImageGalleryJob` - Gallery metadata translation
- `TranslateTagJob` - Tag translation across languages
- `CommentAnalysisJob` - AI-powered comment moderation and spam detection
- `GenerateGalleryPreviewJob` - Gallery preview image generation
- `SendEmailJob` - Asynchronous email delivery

### View Layer & Modern Content Management

Willow CMS features a sophisticated view layer with modern content management capabilities:

#### **View Helpers**
```php
// ContentHelper - Advanced content formatting
$this->Content->enhanceContent($content, [
    'processResponsiveImages' => true,
    'enhanceAlignment' => true
]);

// GalleryHelper - Gallery placeholder processing
$this->Gallery->processGalleryPlaceholders($content);

// VideoHelper - YouTube integration with GDPR compliance
$this->Video->processVideoPlaceholders($content);
```

#### **View Cells**
```php
// GalleryCell - Reusable gallery display component
echo $this->cell('Gallery::display', [
    'galleryId' => $gallery->id,
    'locale' => $this->request->getAttribute('locale')
]);
```

#### **WYSIWYG Editor Integration**
- **Trumbowyg Editor**: Full-featured WYSIWYG with image upload and gallery integration
- **Content Alignment**: Frontend respects editor alignment (left/center/right) for text and images
- **Responsive Images**: Automatic responsive image processing and optimization
- **Gallery Placeholders**: Seamless gallery embedding within content
- **Video Integration**: YouTube video embedding with privacy controls

#### **Image Gallery System**
```php
// ImageGalleriesTable - Advanced gallery management
public function getGalleryForPlaceholder(
    string $galleryId,
    bool $requirePublished = true,
    ?string $cacheKey = null
): ?object {
    // Locale-aware caching and translation support
    // Automatic cache invalidation on content changes
}

// Gallery features:
// - Drag-and-drop image ordering
// - AI-powered image descriptions
// - Multi-language support
// - Preview generation
// - Responsive grid display
```

#### **Content Processing Pipeline**
1. **Raw Content** → Trumbowyg WYSIWYG editor
2. **Video Processing** → YouTube placeholder replacement with GDPR controls
3. **Gallery Processing** → Gallery placeholder replacement with translated content
4. **Content Enhancement** → Alignment processing and responsive image handling
5. **Final Output** → Optimized, accessible, and SEO-friendly content

---

## 💻 Development Workflow

### Feature Development Process

Follow this workflow for developing new features:

#### 1. **Environment Preparation**
```bash
# Start development environment
./setup_dev_env.sh

# Install aliases
./setup_dev_aliases.sh

# Start queue worker (essential for AI features)
cake_queue_worker_verbose
```

#### 2. **Database Schema Changes**
```bash
# Make schema changes in phpMyAdmin or preferred tool
# Generate migration after changes
bake_diff YourFeatureMigration

# Review generated migration in config/Migrations/
# Edit if necessary, then test
cake_migrate
```

#### 3. **Code Generation**
```bash
# Generate model, controller, and templates
cake_bake_model YourModel --theme AdminTheme
cake_bake_controller YourModel --theme AdminTheme
cake_bake_template YourModel --theme AdminTheme
```

#### 4. **Testing & Quality**
```bash
# Write tests first (TDD approach)
# Run specific tests
phpunit tests/TestCase/Controller/YourModelControllerTest.php

# Check code standards
phpcs_sniff

# Fix code violations
phpcs_fix

# Static analysis
phpstan_analyse
```

### Database Migrations

Willow CMS uses CakePHP Migrations for database versioning:

#### **Creating Migrations**
```bash
# After making schema changes, generate diff
bake_diff AddUserPreferences

# Create empty migration
cake_shell bake migration AddUserTable

# Migration with specific changes
cake_shell bake migration CreateTags name:string description:text
```

#### **Migration Best Practices**
- Keep migrations focused and atomic
- Test migrations in development before production
- Use descriptive migration names
- Always backup before running migrations in production
- Include rollback logic where possible

#### **Example Migration**
```php
// config/Migrations/20241225000000_AddUserPreferences.php
class AddUserPreferences extends AbstractMigration {
    public function change(): void {
        $table = $this->table('user_preferences');
        $table->addColumn('user_id', 'char', ['limit' => 36])
              ->addColumn('preference_key', 'string')
              ->addColumn('preference_value', 'text', ['null' => true])
              ->addColumn('created', 'datetime')
              ->addColumn('modified', 'datetime')
              ->addForeignKey('user_id', 'users', 'id')
              ->create();
    }
}
```

### Code Generation with Bake

Willow CMS extends CakePHP's bake functionality with custom templates:

#### **AdminTheme Bake Templates**
```bash
# Generate complete admin CRUD
cake_bake_model Products --theme AdminTheme
cake_bake_controller Products --theme AdminTheme  
cake_bake_template Products --theme AdminTheme
```

**Generated features:**
- Bootstrap-styled forms and layouts
- Search functionality
- Pagination
- Image upload support
- SEO fields integration
- Multi-language support

#### **Custom Bake Templates**
Located in `plugins/AdminTheme/src/Command/Bake/`, these templates generate:
- Controllers with search and filtering
- Templates with responsive design
- Forms with validation and file uploads
- Index views with sorting and pagination

### Best Practices

#### **Code Organization**
- Follow CakePHP 5.x naming conventions
- Use meaningful class and method names
- Implement proper error handling
- Document complex business logic
- Keep controllers thin, models fat

#### **Security Best Practices**
```php
// Always validate and sanitize input
$this->request->allowMethod(['post', 'put']);

// Use CSRF protection
$this->loadComponent('Security');

// Implement proper authorization
$this->Authorization->authorize($article, 'edit');

// Sanitize output
echo h($user->name);
```

#### **Performance Considerations**
```php
// Use eager loading to prevent N+1 queries
$articles = $this->Articles->find()
    ->contain(['Tags', 'Images', 'User'])
    ->where(['published' => true]);

// Implement caching for expensive operations
$result = Cache::remember('articles_popular', function() {
    return $this->Articles->getPopularArticles();
});
```

---

## 🧪 Testing & Quality Assurance

### Unit Testing

Willow CMS maintains comprehensive test coverage:

#### **Running Tests**
```bash
# All tests (292+ comprehensive tests)
phpunit

# Specific test file
phpunit tests/TestCase/Controller/ArticlesControllerTest.php

# Filter specific test methods
phpunit --filter testAdd tests/TestCase/Controller/ArticlesControllerTest.php

# With coverage report (HTML accessible at /coverage/)
phpunit_cov_html

# Text coverage report
phpunit_cov

# Test specific components
phpunit tests/TestCase/Model/Behavior/SlugBehaviorTest.php
phpunit tests/TestCase/Controller/Admin/ImageGalleriesControllerTest.php
phpunit tests/TestCase/Service/Api/Anthropic/
```

#### **Test Structure**
```
tests/
├── TestCase/
│   ├── Controller/          # Controller tests
│   ├── Model/              # Model and behavior tests
│   ├── Command/            # CLI command tests
│   └── Service/            # Service class tests
├── Fixture/                # Test data
└── bootstrap.php           # Test configuration
```

#### **Writing Tests**
```php
// Example controller test
class ArticlesControllerTest extends AppControllerTestCase {
    public function testAdd(): void {
        $data = [
            'title' => 'Test Article',
            'body' => 'Test content',
            'published' => true
        ];
        
        $this->post('/admin/articles/add', $data);
        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);
    }
}
```

### Code Coverage

Generate detailed coverage reports:

```bash
# HTML coverage report (accessible at /coverage/)
phpunit_cov_html

# Text coverage report
phpunit_cov
```

**Coverage targets:**
- Overall coverage: >80%
- Critical paths: >95%
- New features: 100%

### Code Standards

Maintain consistent code quality:

#### **PHP CodeSniffer**
```bash
# Check standards
phpcs_sniff

# Auto-fix violations
phpcs_fix

# Composer scripts
docker compose exec willowcms composer cs-check
docker compose exec willowcms composer cs-fix
```

#### **PHPStan Static Analysis**
```bash
# Run static analysis
phpstan_analyse

# Or via composer
docker compose exec willowcms composer stan
```

**Standards enforced:**
- CakePHP coding standards
- PSR-12 compliance
- Type declarations
- Documentation blocks

### Continuous Integration

GitHub Actions automatically test all commits and pull requests:

#### **CI Pipeline** (`.github/workflows/ci.yml`)
- **PHP Versions**: 8.1, 8.2, 8.3 (comprehensive matrix testing)
- **Services**: MySQL 8.0+ and Redis server setup
- **Tests**: PHPUnit with 292+ tests and coverage reporting
- **Code Quality**: PHPStan (level 5) and PHP CodeSniffer (CakePHP standards)
- **Dependencies**: Composer validation and security scanning
- **Performance**: Parallel test execution and optimized Docker builds
- **Pre-push Hooks**: Local test execution before remote pushes

---

## 🤖 AI Integration

### Anthropic API Services

Willow CMS integrates Claude AI for various content operations:

#### **Architecture**
```
src/Service/Api/
├── AbstractApiService.php          # Base API service
├── Anthropic/
│   ├── AnthropicApiService.php     # Main API client
│   ├── CommentAnalyzer.php         # Comment moderation
│   ├── ImageAnalyzer.php           # Image analysis
│   ├── SeoContentGenerator.php     # SEO content
│   ├── ArticleTagsGenerator.php    # Tag generation
│   ├── TextSummaryGenerator.php    # Content summarization
│   └── TranslationGenerator.php    # AI translation
└── Google/
    └── GoogleApiService.php        # Google Translate
```

#### **Usage Examples**
```php
// Generate SEO content
$seoGenerator = new SeoContentGenerator();
$seoContent = $seoGenerator->generate($article->title, $article->body);

// Analyze image
$imageAnalyzer = new ImageAnalyzer();
$analysis = $imageAnalyzer->analyze($imagePath);

// Moderate comment
$commentAnalyzer = new CommentAnalyzer();
$isAppropriate = $commentAnalyzer->analyze($comment->content);
```

#### **Configuration**
AI services are configured through the admin settings panel:
- API keys management
- Feature toggles
- Rate limiting
- Cost monitoring

### Google Translate API

Professional translation services:

```php
// Translate content
$googleApi = new GoogleApiService();
$translations = $googleApi->translateStrings([
    'Hello World',
    'Welcome to Willow CMS'
], 'en', 'es');
```

### Custom AI Extensions

Extend AI functionality by creating new service classes:

```php
class CustomAiService extends AbstractApiService {
    protected function getApiUrl(): string {
        return 'https://api.example.com/v1/';
    }
    
    public function customAnalysis(string $content): array {
        return $this->makeRequest('analyze', ['content' => $content]);
    }
}
```

---

## 🌍 Internationalization

### Multi-Language Support

Willow CMS is built with internationalization as a core feature:

#### **Supported Features**
- 25+ languages out of the box
- Automatic content translation
- Locale-aware routing
- SEO-optimized URLs per language
- Admin interface translation

#### **Configuration**
```php
// config/app.php
'defaultLocale' => env('APP_DEFAULT_LOCALE', 'en_US'),
'supportedLocales' => [
    'en_US', 'es_ES', 'fr_FR', 'de_DE', 'zh_CN', 'ja_JP'
    // ... more locales
],
```

### Translation Workflow

#### **Extract Translatable Strings**
```bash
# Extract strings from code
i18n_extract

# Manually extract with custom paths
docker compose exec willowcms bin/cake i18n extract \
    --paths /var/www/html/src,/var/www/html/plugins
```

#### **Manage Translations**
```bash
# Load default translations
i18n_load

# Generate PO files for all locales
i18n_gen_po

# Auto-translate using AI/Google
i18n_translate
```

#### **Translation Files**
```
resources/locales/
├── en_US/default.po     # English
├── es_ES/default.po     # Spanish
├── fr_FR/default.po     # French
└── ...
```

### Locale Management

#### **URL Structure**
```
https://example.com/en/articles/my-article
https://example.com/es/articles/mi-articulo
https://example.com/fr/articles/mon-article
```

#### **Content Translation**
```php
// Automatic translation behavior
class ArticlesTable extends Table {
    public function initialize(array $config): void {
        $this->addBehavior('Translate', [
            'fields' => ['title', 'body', 'meta_description']
        ]);
    }
}

// Usage
$article = $this->Articles->get(1);
$article->setLocale('es');
echo $article->title; // Spanish title
```

---

## ⚙️ Configuration & Environment

### Environment Variables

Use `config/.env.example` as a template:

#### **Core Configuration**
```bash
# Application settings
APP_NAME="Willow CMS"
DEBUG=true
APP_ENCODING="UTF-8"
APP_DEFAULT_LOCALE="en_US"
APP_DEFAULT_TIMEZONE="UTC"

# Database
DATABASE_URL="mysql://root:password@mysql:3306/willowcms?encoding=utf8mb4"

# Redis (cache and queue)
REDIS_URL="redis://redis:6379"

# Email
EMAIL_TRANSPORT_DEFAULT_URL="smtp://mailpit:1025"
```

#### **API Configuration**
```bash
# AI Services
ANTHROPIC_API_KEY="your_api_key_here"
GOOGLE_TRANSLATE_API_KEY="your_google_api_key"

# Feature toggles
AI_ENABLED=true
TRANSLATION_ENABLED=true
```

#### **Security Settings**
```bash
# Security salt (generate unique for each environment)
SECURITY_SALT="your_unique_security_salt_here"

# Session configuration
SESSION_TIMEOUT=3600
CSRF_PROTECTION=true
```

### Docker Development Environment

#### **Services Overview**
- **willowcms**: Main application container (Nginx + PHP-FPM + Redis)
- **mysql**: Database server
- **mailpit**: Email testing
- **phpmyadmin**: Database management
- **redis-commander**: Redis monitoring
- **jenkins**: CI/CD (optional)

#### **Service URLs**
- Main application: http://localhost:8080
- Admin panel: http://localhost:8080/admin
- phpMyAdmin: http://localhost:8082
- Mailpit: http://localhost:8025
- Redis Commander: http://localhost:8084
- Jenkins: http://localhost:8081

#### **Container Management**
```bash
# Start all services
docker_up

# Stop all services
docker_down

# View logs
docker_logs

# Clean up
docker_prune
```

### Production Considerations

#### **Performance Optimization**
- Enable Redis caching
- Configure queue workers
- Optimize image processing
- Enable view caching
- Database query optimization

#### **Security Hardening**
- Remove debug mode
- Secure API keys
- Configure HTTPS
- Set up rate limiting
- Regular security updates

#### **Monitoring**
- Application logs
- Performance metrics
- Queue monitoring
- Error tracking
- Resource usage

---

## 🔧 Tools & Utilities

### Management Tool

The interactive management tool (`./manage.sh`) provides a menu-driven interface:

#### **Data Management**
1. Import Default Data
2. Export Default Data  
3. Database Backup/Restore
4. File Backup/Restore

#### **Internationalization**
5. Extract i18n Messages
6. Load Default Translations
7. Run Automated Translations
8. Generate PO Files

#### **System Operations**
9. Clear Cache
10. Interactive Shell
11. System Updates

### Command Line Tools

Located in `src/Command/`:

#### **User Management**
```bash
# Create admin user
cake_shell create_user admin@example.com password Admin User
```

#### **Data Management**
```bash
# Import default data
cake_shell default_data_import

# Export current data
cake_shell default_data_export

# Generate test articles
cake_shell generate_articles 100
```

#### **Image Processing**
```bash
# Resize all images
cake_shell resize_images

# Test rate limiting
cake_shell test_rate_limit
```

### Development Aliases

Essential shortcuts for development:

#### **Testing Aliases**
- `phpunit` - Run all tests
- `phpunit_cov` - Generate coverage report
- `phpunit_cov_html` - HTML coverage report

#### **Code Quality Aliases**
- `phpcs_sniff` - Check code standards
- `phpcs_fix` - Auto-fix violations
- `phpstan_analyse` - Static analysis

#### **CakePHP Aliases**
- `cake_shell` - Execute CakePHP commands
- `cake_queue_worker` - Start queue worker
- `cake_migrate` - Run migrations
- `cake_clear_cache` - Clear all caches

#### **Docker Aliases**
- `willowcms_exec` - Execute commands in container
- `willowcms_shell` - Interactive container shell
- `docker_up` - Start Docker services
- `docker_down` - Stop Docker services

---

## 🎯 Quick Reference

### Essential Development Commands

```bash
# Start development environment
./setup_dev_env.sh && ./setup_dev_aliases.sh

# Run comprehensive tests and quality checks
phpunit && phpcs_sniff && phpstan_analyse

# Start background processing (required for AI features)
cake_queue_worker_verbose

# Generate code with AdminTheme templates
bake_diff MyFeature
cake_bake_model MyModel --theme AdminTheme
cake_bake_controller MyModel --theme AdminTheme
cake_bake_template MyModel --theme AdminTheme

# Manage internationalization
i18n_extract && i18n_gen_po && i18n_translate

# Database operations
cake_migrate && cake_clear_cache

# Direct database access
docker compose exec mysql mysql -u cms_user -ppassword cms

# Interactive management tool
./manage.sh
```

## 🚀 Recent Major Improvements

### **Content Management Enhancements**
- **WYSIWYG Editor**: Full Trumbowyg integration with proper frontend alignment rendering
- **Image Galleries**: Complete gallery system with drag-and-drop ordering and AI descriptions
- **Content Alignment**: Frontend now properly respects left/center/right alignment from editor
- **Responsive Images**: Automatic image optimization and responsive handling
- **Translation Integration**: Seamless multi-language support for galleries and content

### **Testing & Quality Improvements**
- **292+ Tests**: Comprehensive test suite covering all major components
- **CI/CD Enhancement**: GitHub Actions with Redis integration and matrix testing
- **Code Quality**: Automated PHP CodeSniffer fixes and PHPStan level 5 analysis
- **Pre-push Hooks**: Automatic test execution before commits reach repository

### **Developer Experience Upgrades**
- **Enhanced CLAUDE.md**: Comprehensive documentation with command examples
- **Management Tool**: Interactive CLI for data management, backups, and maintenance
- **Developer Aliases**: Streamlined commands for common development tasks
- **Docker Integration**: Complete development environment with all necessary services

### **AI & Translation Features**
- **Enhanced API Integration**: Improved Anthropic API service with better error handling
- **Gallery Translation**: Multi-language support for image gallery metadata
- **Content Translation**: Automatic translation workflows for articles and UI
- **Image Analysis**: AI-powered alt text and keyword generation for accessibility

### Common Troubleshooting

#### **Queue Not Processing**
```bash
# Check if queue worker is running
cake_queue_worker_verbose

# Clear queue if stuck
redis-cli FLUSHDB
```

#### **Tests Failing**
```bash
# Clear test cache
willowcms_exec rm -rf tmp/cache/models/*
willowcms_exec rm -rf tmp/cache/persistent/*

# Reset test database
phpunit tests/TestCase/YourTest.php
```

#### **Permission Issues**
```bash
# Fix file permissions
change_ownership
set_permissions
```

---

## 📚 Additional Resources

- **[CakePHP Book](https://book.cakephp.org/5/en/index.html)** - Framework documentation
- **[Anthropic API Docs](https://docs.anthropic.com/)** - AI integration reference
- **[Docker Documentation](https://docs.docker.com/)** - Container management
- **[PHPUnit Manual](https://phpunit.de/documentation.html)** - Testing framework
- **[Bootstrap Documentation](https://getbootstrap.com/docs/)** - UI framework

---

<div align="center">
  <strong>🌿 Happy coding with Willow CMS!</strong>
</div>