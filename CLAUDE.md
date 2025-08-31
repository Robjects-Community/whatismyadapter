# CLAUDE.md

This file provides comprehensive guidance to Claude Code (claude.ai/code) when working with the Willow CMS codebase.

## Essential Commands

### Development Environment

Willow CMS uses Docker for development. All commands should be run through Docker containers:

```bash
# Start development environment
./setup_dev_env.sh

# Start development environment with Jenkins
./setup_dev_env.sh --jenkins

# Access the container shell
docker compose exec -it willowcms /bin/sh

# Management tool (interactive menu for common tasks)
./manage.sh
```

### Developer Aliases

Install helpful shell aliases (strongly recommended):

```bash
# Install aliases (supports both bash and zsh)
./setup_dev_aliases.sh
```

This adds functions and aliases from `dev_aliases.txt` to your shell profile. Key aliases include:
- `cake_shell` - Execute CakePHP console commands
- `willowcms_exec` - Execute any command in container
- `willowcms_shell` - Interactive shell in container
- `phpunit` - Run tests
- `cake_queue_worker` - Start queue worker

### Testing

```bash
# Run all tests
docker compose exec willowcms php vendor/bin/phpunit
# or with alias:
phpunit

# Run specific test file
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/UsersControllerTest.php
# or with alias:
phpunit tests/TestCase/Controller/UsersControllerTest.php

# Run tests with coverage (text)
docker compose exec willowcms php vendor/bin/phpunit --coverage-text
# or with alias:
phpunit_cov

# Run tests with coverage (HTML) - accessible at http://localhost:8080/coverage/
docker compose exec willowcms php vendor/bin/phpunit --coverage-html webroot/coverage tests/TestCase/
# or with alias:
phpunit_cov_html

# Filter specific test methods
phpunit --filter testLogin tests/TestCase/Controller/UsersControllerTest.php
```

### Code Quality

```bash
# PHP CodeSniffer - check for coding standard violations
docker compose exec willowcms vendor/bin/phpcs --standard=vendor/cakephp/cakephp-codesniffer/CakePHP src/ tests/
# or with alias:
phpcs_sniff

# PHP CodeSniffer - auto-fix violations
docker compose exec willowcms php vendor/bin/phpcbf
# or with alias:
phpcs_fix

# PHPStan - static analysis (level 5)
docker compose exec willowcms php vendor/bin/phpstan analyse src/
# or with alias:
phpstan_analyse

# Composer scripts
docker compose exec willowcms composer cs-check
docker compose exec willowcms composer cs-fix
docker compose exec willowcms composer stan
```

### Database & Cache

```bash
# Run migrations
docker compose exec willowcms bin/cake migrations migrate
# or with alias:
cake_migrate

# Create migration diff (after making schema changes)
docker compose exec willowcms bin/cake bake migration_diff YourMigrationName
# or with alias:
bake_diff YourMigrationName

# Create migration snapshot
bake_snapshot InitialSchema

# Rollback migrations
cake_rollback

# Clear all cache
docker compose exec willowcms bin/cake cache clear_all
# or with alias:
cake_clear_cache

# Direct MySQL database access
docker compose exec mysql mysql -u cms_user -ppassword cms
# Query examples:
docker compose exec mysql mysql -u cms_user -ppassword cms -e "SELECT * FROM settings WHERE key_name = 'editor';"
docker compose exec mysql mysql -u cms_user -ppassword cms -e "DESCRIBE articles;"
docker compose exec mysql mysql -u cms_user -ppassword cms -e "SELECT id, title, is_published FROM articles LIMIT 5;"
```

### Queue Workers

Queue workers are **essential** for AI features, image processing, and email sending:

```bash
# Start queue worker (required for AI features, image processing)
docker compose exec willowcms bin/cake queue worker --verbose
# or with alias:
cake_queue_worker_verbose

# Basic queue worker
cake_queue_worker
```

**Important**: Always run a queue worker when testing:
- AI content generation (SEO, tags, translations)
- Image uploads and processing
- Email sending
- Comment analysis

### Baking (Code Generation)

```bash
# Bake model/controller/template with AdminTheme
docker compose exec willowcms bin/cake bake model Dogs --theme AdminTheme
docker compose exec willowcms bin/cake bake controller Dogs --theme AdminTheme
docker compose exec willowcms bin/cake bake template Dogs --theme AdminTheme

# Using aliases:
cake_bake_model Dogs --theme AdminTheme
cake_bake_controller Dogs --theme AdminTheme
cake_bake_template Dogs --theme AdminTheme
```

### Internationalization (i18n)

```bash
# Extract translatable strings from code
docker compose exec willowcms bin/cake i18n extract --paths /var/www/html/src,/var/www/html/plugins,/var/www/html/templates
# or with alias:
i18n_extract

# Load default internationalization data
docker compose exec willowcms bin/cake load_default18n
# or with alias:
i18n_load

# Run automated translations (requires AI API keys)
docker compose exec willowcms bin/cake translate_i18n
# or with alias:
i18n_translate

# Generate PO files for all locales
docker compose exec willowcms bin/cake generate_po_files
# or with alias:
i18n_gen_po
```

### Data Management

```bash
# Import default data (AI prompts, email templates, i18n)
docker compose exec willowcms bin/cake default_data_import

# Export current data as defaults
docker compose exec willowcms bin/cake default_data_export
# or with alias:
export_data
```

### Debugging and Troubleshooting

```bash
# Investigate article translation and SEO issues
docker compose exec willowcms bin/cake investigate_article article-slug-here

# Examples:
docker compose exec willowcms bin/cake investigate_article this-is-a-test-page
docker compose exec willowcms bin/cake investigate_article my-blog-post
```

**InvestigateArticle Command**: Comprehensive debugging tool for AI-related issues:
- Checks if article exists and shows metadata
- Verifies translation status in articles_translations table  
- Reviews system logs for translation job activity/errors
- Reviews system logs for SEO generation job activity/errors
- Checks queue_jobs table for pending/failed jobs
- Useful when articles aren't appearing in other languages or missing SEO content

### Management Tool (manage.sh)

The interactive management tool provides menu-driven access to common development and maintenance tasks:

```bash
# Launch the management tool
./manage.sh
```

#### Available Menu Options:

**Data Management:**
1. **Import Default Data** - Import default datasets (AI prompts, email templates, i18n)
2. **Export Default Data** - Export current data as defaults for future installations
3. **Dump MySQL Database** - Create timestamped database backups to `./project_mysql_backups/`
4. **Load Database from Backup** - Restore database from a backup file

**Internationalization:**
5. **Extract i18n Messages** - Extract translatable strings from code
6. **Load Default i18n** - Import default internationalization data
7. **Translate i18n** - Run automated translations (requires API keys)
8. **Generate PO Files** - Generate translation files for all locales

**Asset Management:**
9. **Backup Files Directory** - Create backup of uploaded files to `./project_files_backups/`
10. **Restore Files from Backup** - Restore uploaded files from a backup

**System:**
11. **Clear Cache** - Clear all CakePHP caches
12. **Interactive Shell** - Open a shell session in the WillowCMS container
13. **Host System Update & Docker Cleanup** - Update host OS and clean unused Docker resources

#### Key Features:
- **Safety Checks**: Validates required Docker services are running before operations
- **Database Backups**: Creates SQL dumps with CREATE TABLE statements verification
- **File Backups**: Archives the entire `webroot/files` directory with proper permissions
- **Interactive Selection**: Shows timestamped backups sorted by date for easy selection
- **Error Handling**: Comprehensive error checking with rollback capabilities
- **Docker Integration**: All operations run through Docker containers

#### Usage Notes:
- Database backups are stored in `./project_mysql_backups/`
- File backups are stored in `./project_files_backups/`
- The tool requires `willowcms` and `mysql` services to be running
- Backups include timestamps in filenames (format: YYYYMMDD_HHMMSS)
- Database restore includes automatic cache clearing
- File restore preserves proper ownership (nobody:nobody) and permissions

## High-Level Architecture

### Core Design Patterns

1. **MVC Architecture**: Built on CakePHP 5.x following strict MVC patterns
   - **Models**: `src/Model/Table/` (table classes) and `src/Model/Entity/` (entity classes)
   - **Views**: Theme-based templates in `plugins/*/templates/`
   - **Controllers**: `src/Controller/` (frontend) and `src/Controller/Admin/` (backend)

2. **Plugin-Based Theming**: Frontend and admin interfaces are separate plugins
   - `plugins/DefaultTheme/` - Public-facing website theme
   - `plugins/AdminTheme/` - Administrative backend theme
   - Each plugin has its own controllers, templates, and assets

3. **Behavior System**: Reusable model behaviors for common functionality
   - `ImageAssociableBehavior` - Handles image associations across models via pivot table
   - `SlugBehavior` - Manages URL-friendly slugs with history tracking
   - `OrderableBehavior` - Provides drag-and-drop ordering functionality
   - `CommentableBehavior` - Adds commenting functionality to any model
   - `QueueableImageBehavior` - Queues image processing jobs

4. **Queue-Based Processing**: Heavy operations are offloaded to background jobs
   - `ProcessImageJob` - Image resizing and thumbnail generation
   - `ImageAnalysisJob` - AI-powered image analysis for alt text and keywords
   - `ArticleSeoUpdateJob` - AI-generated SEO content for articles
   - `ArticleTagUpdateJob` - Automatic tag generation for articles
   - `ArticleSummaryUpdateJob` - Generate article summaries
   - `CommentAnalysisJob` - AI comment moderation
   - `SendEmailJob` - Asynchronous email sending
   - `TranslateArticleJob`, `TranslateI18nJob`, `TranslateTagJob` - Translation tasks

5. **AI Integration Architecture**: Modular API service design
   - `AbstractApiService` - Base class for all API integrations
   - `AnthropicApiService` - Anthropic Claude API implementation
   - `GoogleApiService` - Google Translate API implementation
   - Specialized generators/analyzers for specific AI tasks

### Key Architectural Decisions

1. **Multi-Language First**: Built-in i18n support with TranslateBehavior on core models
   - Articles, Tags support automatic translation via AI/Google APIs
   - Locale-aware routing and content delivery
   - 25+ supported languages with locale-specific URLs

2. **SEO-Centric Design**: Every content type has comprehensive SEO fields
   - Meta titles, descriptions, keywords
   - Social media-specific descriptions (Facebook, Twitter, LinkedIn, Instagram)
   - AI-powered SEO content generation
   - URL slug management with history

3. **Security Layers**:
   - `IpBlockerMiddleware` - IP-based access control
   - `RateLimitMiddleware` - Request rate limiting
   - CSRF protection enabled by default
   - Authentication using CakePHP's Authentication plugin
   - Session security and timeout management

4. **Caching Strategy**: Multi-layer caching approach
   - **Redis**: Primary cache and queue storage
   - **File-based cache**: Fallback when Redis unavailable
   - **View caching**: For anonymous users and static content
   - **Query caching**: For expensive database operations

5. **Image Management**: Sophisticated image handling system
   - Multiple size generation on upload (thumbnails, medium, large)
   - AI-powered alt text and keyword generation
   - CDN-ready URL generation with `ImageUrlTrait`
   - Association tracking via `models_images` pivot table
   - Background processing for heavy operations

### Database Design Philosophy

- **UUID primary keys** for all tables (distributed-system ready)
- **Soft deletes** where appropriate (e.g., users table)
- **Audit fields** on all tables (created, modified, created_by, modified_by)
- **Normalized design** with strategic denormalization for performance
- **Slug history tracking** for SEO-friendly URL changes
- **Translation tables** for multi-language content (`articles_translations`)

### Key Database Tables

- `articles` - Main content with translations support
- `tags` - Hierarchical tagging system with translations
- `images` - Image metadata and AI-generated descriptions
- `models_images` - Pivot table for image associations
- `slugs` - URL slug history and redirects
- `users` - User accounts with role-based permissions
- `comments` - Universal commenting system
- `settings` - Application configuration
- `aiprompts` - AI prompt templates for various tasks
- `page_views` - Analytics and view tracking

### Integration Points

1. **Anthropic Claude API**: Used for content analysis and generation
   - Comment moderation and spam detection
   - Image analysis for alt text and keywords
   - SEO content generation (meta descriptions, titles)
   - Article summarization and tag generation
   - Translation services (backup to Google Translate)

2. **Google Translate API**: Professional translation service
   - More accurate than general-purpose AI for translations
   - Batch translation support with cost optimization
   - Automatic locale detection and validation
   - Primary translation service with AI fallback

3. **Redis**: Cache and queue backend
   - Queue job storage and processing
   - Session storage in production environments
   - Cache storage for high-traffic sites
   - Rate limiting data storage

### Development Workflow Considerations

- **Always run queue workers** when testing AI features
- **Use Docker environment** for consistency across development setups
- **Follow CakePHP 5.2 conventions** strictly (naming, file locations, coding standards)
- **Test with multiple languages** enabled when working on i18n features
- **Check rate limits** when testing API integrations
- **Use management tool** for complex data operations
- **Run tests before commits** (pre-push hook available)

### File Organization

```
src/
├── Application.php                 # Main application class
├── Command/                        # CLI commands
│   ├── CreateUserCommand.php
│   ├── DefaultDataImportCommand.php
│   ├── ResizeImagesCommand.php
│   └── ...
├── Controller/                     # Frontend controllers
│   ├── AppController.php          # Base controller
│   ├── ArticlesController.php
│   ├── UsersController.php
│   └── Admin/                      # Admin controllers
├── Model/
│   ├── Behavior/                   # Reusable behaviors
│   ├── Entity/                     # Entity classes
│   └── Table/                      # Table classes
├── Service/Api/                    # API integrations
│   ├── AbstractApiService.php
│   ├── Anthropic/                  # Anthropic API services
│   └── Google/                     # Google API services
├── Job/                           # Queue job classes
├── Middleware/                    # Custom middleware
└── Utility/                       # Utility classes
```

### Plugin Structure

```
plugins/
├── AdminTheme/                    # Admin backend theme
│   ├── src/                       # Plugin controllers and logic
│   ├── templates/                 # Admin templates
│   └── webroot/                   # Admin assets
└── DefaultTheme/                  # Public website theme
    ├── src/                       # Theme controllers
    ├── templates/                 # Public templates
    └── webroot/                   # Public assets
```

### Known Issues and Workarounds

1. **Sitemap Index Route**: The sitemap index route at `/sitemap.xml` currently has routing issues
   - **Workaround**: Use language-specific sitemaps directly (e.g., `/en/sitemap.xml`, `/fr/sitemap.xml`)
   - **Location**: `src/Controller/SitemapController.php`

2. **Queue Worker Memory**: Long-running queue workers may accumulate memory
   - **Workaround**: Restart queue workers periodically in production
   - **Solution**: Monitor memory usage and implement automatic restarts

3. **Image Processing**: Large images may timeout during processing
   - **Workaround**: Use queue workers for all image operations
   - **Configuration**: Adjust PHP memory and execution time limits

### Testing Strategy

- **Unit Tests**: Located in `tests/TestCase/`
- **Fixtures**: Test data in `tests/Fixture/`
- **Test Coverage**: Generate HTML reports with `phpunit_cov_html`
- **CI/CD**: GitHub Actions test on PHP 8.1, 8.2, 8.3
- **Pre-commit Hooks**: Available via `./setup_dev_aliases.sh`

### Environment Configuration

Use `config/.env.example` as template for environment-specific settings:
- Database connections
- API keys (Anthropic, Google)
- Cache configuration
- Queue settings
- Security settings

**Security Note**: Never commit `.env` files to version control. Use environment variables in production.

### Performance Considerations

1. **Queue Workers**: Essential for non-blocking operations
2. **Redis Caching**: Significantly improves performance
3. **Image Optimization**: Background processing prevents timeouts
4. **Database Indexing**: Optimized for slug-based lookups
5. **CDN Ready**: Image URLs support CDN integration

### API Rate Limits

- **Anthropic API**: Respect tier-based limits
- **Google Translate API**: Monitor usage and costs
- **Built-in Rate Limiting**: Configurable per endpoint

This architecture provides a solid foundation for scaling Willow CMS while maintaining code quality and developer experience.