# Changelog

All notable changes to Willow CMS will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2024-09-17

### 🚨 BREAKING CHANGES

- **Repository Restructure**: Complete reorganization with helper-files directory for non-essential documentation and resources
- **Docker Workflow Overhaul**: New Docker-based development environment with docker-compose.yml updates
- **Configuration Changes**: Environment configuration now lives in `./config/.env` instead of root
- **Theme Structure**: Themes must be under `./plugins/DefaultTheme/*` and `./plugins/AdminTheme/*`
- **Queue Workers Required**: Background queue workers are now mandatory for AI features, image processing, and email sending
- **Development Workflow**: New setup scripts (`setup_dev_env.sh`, `setup_dev_aliases.sh`) replace manual configuration

### ✨ Added

#### AI Integration
- Anthropic Claude API integration for content generation and analysis
- Google Translate API for professional translations
- AI-powered SEO content generation (meta descriptions, titles, keywords)
- Automatic article summarization and tag generation
- AI-driven image analysis for alt text and keywords
- Comment moderation and spam detection via AI

#### Developer Tools
- `manage.sh` - Interactive management tool for common development tasks
- `setup_dev_env.sh` - Automated development environment setup
- `setup_dev_aliases.sh` - Shell aliases for common Docker commands
- `dev_aliases.txt` - Comprehensive list of development shortcuts
- Pre-commit hooks for code quality checks
- GitHub Actions CI/CD pipeline for PHP 8.1, 8.2, 8.3

#### Queue System
- Background job processing system with Redis support
- Queue jobs for:
  - Image processing and thumbnail generation (`ProcessImageJob`)
  - AI-powered image analysis (`ImageAnalysisJob`)
  - SEO content updates (`ArticleSeoUpdateJob`)
  - Article tag generation (`ArticleTagUpdateJob`)
  - Article summarization (`ArticleSummaryUpdateJob`)
  - Comment analysis (`CommentAnalysisJob`)
  - Asynchronous email sending (`SendEmailJob`)
  - Translation tasks (`TranslateArticleJob`, `TranslateI18nJob`, `TranslateTagJob`)

#### Internationalization (i18n)
- Support for 25+ languages with locale-specific URLs
- Automated translation via AI and Google Translate APIs
- Batch translation support with cost optimization
- Locale-aware routing and content delivery
- i18n extraction and generation tools

#### Documentation
- `WARP.md` / `CLAUDE.md` - Comprehensive AI assistant guidance
- Docker environment documentation
- Cleanup and shutdown procedures
- Troubleshooting guides
- Architecture and design documentation
- Test refactoring documentation

#### Testing & Quality
- PHPStan static analysis integration (level 5)
- PHP CodeSniffer with CakePHP coding standards
- Test coverage reporting (text and HTML)
- PHPUnit test suite enhancements
- Pre-push code quality checks

### 🔄 Changed

#### Infrastructure
- Docker compose configuration optimized for development
- Redis as primary cache and queue backend
- Multi-layer caching strategy (Redis → File → View → Query)
- Database design with UUID primary keys
- Soft deletes implementation
- Audit fields on all tables

#### Code Organization
- MVC architecture strictly following CakePHP 5.x patterns
- Plugin-based theming system
- Behavior system for reusable model functionality
- Service-oriented API integration design
- Modular queue job architecture

#### Developer Experience
- Simplified Docker commands via aliases
- Interactive menu for database and file backups
- Automated migration and data import tools
- Improved error handling and logging
- Better rate limiting and IP blocking

### 🐛 Fixed

- PHPStan compatibility issues resolved
- PHP CodeSniffer violations corrected
- Docker environment stability improvements
- Memory management in long-running queue workers
- Image processing timeout issues
- Sitemap routing for language-specific URLs
- Session security and timeout handling
- Rate limiting implementation
- Error logging and debugging tools

### 📦 Dependencies

- CakePHP 5.x framework
- PHP 8.1+ requirement
- Redis for caching and queues
- MySQL 5.7+ / MariaDB 10.3+
- Docker and Docker Compose

### 🔧 Configuration

- Environment variables in `config/.env`
- Docker override support via `docker-compose.override.yml`
- Jenkins CI/CD integration support
- Pre-commit hooks configuration
- PHPStan level 5 analysis
- CakePHP CodeSniffer standards

### 📝 Migration Notes

To upgrade from v1.x to v2.0.0:

1. **Backup your data**:
   ```bash
   ./manage.sh  # Use option to dump database
   ```

2. **Update environment**:
   ```bash
   ./setup_dev_env.sh
   ./setup_dev_aliases.sh
   ```

3. **Update configuration**:
   - Copy `config/.env.example` to `config/.env`
   - Update with your API keys and settings
   
4. **Run migrations**:
   ```bash
   cake_migrate
   ```

5. **Start queue worker** (required for AI features):
   ```bash
   cake_queue_worker
   ```

6. **Import default data**:
   ```bash
   docker compose exec willowcms bin/cake default_data_import
   ```

### 🙏 Acknowledgments

This major release represents a significant evolution of Willow CMS with contributions from the community. Special thanks to all contributors who helped with testing, documentation, and feature development.

---

## Version History

### [1.6.0-dev] - Development
- Product system v2 implementation
- Admin interface improvements
- Bulk editing features

### [1.5.1-dev] - Development
- Bug fixes and performance improvements

### [1.5.0-dev] - Development
- Initial product features
- UI updates

### [1.4.0-beta] - Beta Release
- Base CMS functionality
- Article management
- User authentication
- Basic theming support

---

[2.0.0]: https://github.com/garzarobm/willow/compare/v1.4.0...v2.0.0
[1.4.0]: https://github.com/garzarobm/willow/releases/tag/v1.4.0