# 🌿 Willow CMS

> **A Modern Content Management System Built with CakePHP 5.x and AI Integration**

[![Build Status](https://github.com/matthewdeaves/willow/workflows/CI/badge.svg)](https://github.com/matthewdeaves/willow/actions)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%20|%208.2%20|%208.3-blue)](https://php.net)
[![CakePHP](https://img.shields.io/badge/CakePHP-5.x-red)](https://cakephp.org)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

Willow CMS is a powerful, AI-enhanced content management system that combines the robustness of CakePHP 5.x with cutting-edge AI capabilities. Built with developers in mind, it offers a complete Docker development environment and production-ready features.

**🆓 FREE & OPEN SOURCE** • **🏢 COMMERCIAL USE ALLOWED** • **🔧 FULLY CUSTOMIZABLE**

**🚀 [Live Demo](https://willowcms.app) | 📖 [Development Blog](https://willowcms.app) | 📄 [MIT License](#-license)**

---

## ✨ Key Features

### 🤖 **AI-Powered Content Management**
- **Automatic Translation**: Support for 25+ languages with AI and Google Translate integration
- **SEO Optimization**: AI-generated meta titles, descriptions, and social media content
- **Smart Tagging**: Automatic article tagging based on content analysis
- **Image Analysis**: AI-powered alt text, keywords, and descriptions for images
- **Comment Moderation**: Intelligent spam and inappropriate content detection
- **Content Generation**: AI-powered article summaries and content enhancement

### 🎨 **Flexible Architecture**
- **Plugin-Based Theming**: Separate frontend (`DefaultTheme`) and admin (`AdminTheme`) interfaces
- **Multi-Language First**: Built-in internationalization with locale-aware routing
- **Queue-Based Processing**: Background jobs for heavy operations (image processing, AI tasks)
- **Modern Security**: IP blocking, rate limiting, CSRF protection, and secure authentication
- **Advanced Content Management**: WYSIWYG editor (Trumbowyg) with image galleries and responsive design
- **Image Gallery System**: Comprehensive image management with AI-powered descriptions and metadata

### 🛠️ **Developer Experience**
- **Docker Development Environment**: Complete setup with Nginx, PHP, MySQL, Redis, PHPMyAdmin, Mailpit, and Jenkins
- **Management Tool**: Interactive CLI (`./manage.sh`) for data management, backups, and system operations
- **Code Quality Tools**: PHP CodeSniffer, PHPStan, and comprehensive unit testing with 292+ tests
- **CakePHP 5.x Foundation**: Following modern MVC patterns and conventions
- **Developer Aliases**: Streamlined shell commands for common development tasks
- **GitHub Actions CI/CD**: Automated testing on PHP 8.1, 8.2, 8.3 with Redis integration

---

## 🚀 Quick Start

### Prerequisites
- [Docker](https://www.docker.com/get-started) (only requirement on your host machine)
- Git

### Installation

```bash
# Clone the repository
git clone git@github.com:matthewdeaves/willow.git
cd willow/

# Run the setup script
./setup_dev_env.sh
```

🎉 **That's it!** Your development environment is ready:

- **Main Site**: [http://localhost:8080](http://localhost:8080)
- **Admin Panel**: [http://localhost:8080/admin](http://localhost:8080/admin)
  - **Login**: `admin@test.com` / `password`

### Additional Services

- **phpMyAdmin**: [http://localhost:8082](http://localhost:8082) (root/password)
- **Mailpit**: [http://localhost:8025](http://localhost:8025) (email testing)
- **Redis Commander**: [http://localhost:8084](http://localhost:8084) (root/password)
- **Jenkins**: [http://localhost:8081](http://localhost:8081) (start with `./setup_dev_env.sh --jenkins`)

---

## 🔧 Development Workflow

### Developer Aliases

Install helpful shell aliases for streamlined development:

```bash
./setup_dev_aliases.sh
```

This provides shortcuts like:
- `cake_queue_worker` - Start background job processing
- `phpunit` - Run all tests
- `phpcs_sniff` - Check code standards
- `phpcs_fix` - Auto-fix code violations

### Essential Commands

#### Queue Workers (Required for AI Features)
```bash
# Start queue worker for AI processing, image handling, etc.
cake_queue_worker_verbose
# or
docker compose exec willowcms bin/cake queue worker --verbose
```

#### Testing
```bash
# Run all tests (292+ tests with comprehensive coverage)
phpunit

# Run with coverage report
phpunit_cov_html
# Accessible at http://localhost:8080/coverage/

# Run specific test file
phpunit tests/TestCase/Controller/UsersControllerTest.php
```

#### Code Quality
```bash
# Check coding standards
phpcs_sniff

# Auto-fix code violations
phpcs_fix

# Static analysis (PHPStan level 5)
phpstan_analyse

# All quality checks
composer cs-check && composer stan
```

#### Database Management
```bash
# Run migrations
cake_migrate

# Create migration after schema changes
bake_diff YourMigrationName

# Direct database access
docker compose exec mysql mysql -u cms_user -ppassword cms
```

### Management Tool

The interactive management tool provides easy access to common tasks:

```bash
./manage.sh
```

**Features:**
- 📊 Database backups and restoration
- 🌐 Internationalization management  
- 📁 File backup and restoration
- 🧹 Cache clearing and system maintenance
- 🔧 Interactive container shell access

---

## 🤖 AI Integration Setup

Willow CMS integrates with leading AI services for enhanced functionality:

### Anthropic Claude API
- Content analysis and generation
- SEO optimization
- Image analysis
- Comment moderation
- Article summarization

### Google Translate API
- Professional-grade translations
- Batch processing support
- 25+ language support

### Configuration

1. Navigate to **Settings**: [http://localhost:8080/admin/settings](http://localhost:8080/admin/settings)
2. Add your API keys:
   - Anthropic API key
   - Google Translate API key
3. Enable AI features and select desired languages
4. Start a queue worker to process AI jobs

---

## 🏗️ Architecture Overview

### Core Technologies
- **Framework**: CakePHP 5.x
- **Database**: MySQL 8.0+
- **Cache/Queue**: Redis
- **Container**: Docker + Alpine Linux
- **Web Server**: Nginx + PHP-FPM

### Project Structure
```
willow/
├── 📁 src/                          # Core application code
│   ├── 🎮 Controller/              # Frontend controllers
│   │   └── Admin/                  # Admin backend controllers
│   ├── 📊 Model/                   # Data models, entities, and behaviors
│   │   ├── Behavior/              # Reusable model behaviors
│   │   ├── Entity/                # Entity classes with business logic
│   │   └── Table/                 # Table classes with queries
│   ├── 🔌 Service/Api/             # AI and external API integrations
│   │   ├── Anthropic/             # Claude AI services
│   │   └── Google/                # Google Translate services
│   ├── ⚡ Job/                     # Background job classes
│   ├── 🛠️ Command/                 # CLI command tools
│   ├── 🛡️ Middleware/              # Security and rate limiting
│   ├── 👁️ View/                    # View helpers and cells
│   └── 🔧 Utility/                 # Helper and utility classes
├── 🎨 plugins/                     # Plugin-based themes
│   ├── AdminTheme/                # Administrative interface (Bootstrap)
│   │   ├── src/                   # Plugin controllers and logic
│   │   ├── templates/             # Admin templates and forms
│   │   └── webroot/               # Admin assets (CSS, JS)
│   └── DefaultTheme/              # Public website theme
│       ├── src/                   # Theme controllers
│       ├── templates/             # Public templates
│       └── webroot/               # Public assets
├── ⚙️ config/                      # Configuration files
│   ├── Migrations/                # Database migration files
│   ├── schema/                    # Database schema files
│   └── routes.php                 # URL routing configuration
├── 🧪 tests/                       # Comprehensive test suite (292+ tests)
│   ├── TestCase/                  # Test classes
│   └── Fixture/                   # Test data fixtures
├── 🐳 docker/                      # Docker development environment
├── 📁 webroot/                     # Public web assets and uploads
├── 🌍 resources/locales/           # Translation files (25+ languages)
└── 🔧 manage.sh                    # Interactive management tool
```

### Key Behaviors & Components
- **ImageAssociableBehavior**: Cross-model image associations via pivot table
- **SlugBehavior**: SEO-friendly URLs with history tracking and automatic redirects
- **OrderableBehavior**: Drag-and-drop content ordering for galleries and lists
- **CommentableBehavior**: Universal commenting system with AI moderation
- **QueueableImageBehavior**: Automated background image processing and analysis
- **ContentHelper**: Advanced content formatting with alignment and responsive images
- **GalleryCell**: Reusable gallery display component with translation support

---

## 🧪 Testing & Quality

### Continuous Integration
- **GitHub Actions**: Automated testing on PHP 8.1, 8.2, 8.3 with Redis and MySQL
- **Code Coverage**: HTML reports available at `/coverage/` with detailed metrics
- **Pre-commit Hooks**: Automatic test execution and code quality checks before pushes
- **292+ Tests**: Comprehensive test suite covering controllers, models, behaviors, and services

### Code Standards
- **PHP CodeSniffer**: CakePHP coding standards enforcement
- **PHPStan**: Static analysis (level 5)
- **Unit Tests**: Comprehensive test coverage with fixtures

### Running Tests
```bash
# All tests
docker compose exec willowcms php vendor/bin/phpunit

# Specific test file
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/UsersControllerTest.php

# With coverage report
docker compose exec willowcms php vendor/bin/phpunit --coverage-html webroot/coverage
```

---

## 🌐 Multi-Language Support

Willow CMS is built with internationalization as a core feature:

### Supported Languages
Over 25 languages including English, Spanish, French, German, Chinese, Japanese, and more.

### Features
- **Automatic Translation**: AI-powered content translation
- **Locale-Aware Routing**: Language-specific URLs
- **SEO Translation**: Meta content in multiple languages
- **Content Localization**: Articles, tags, and UI elements

### Commands
```bash
# Extract translatable strings
docker compose exec willowcms bin/cake i18n extract

# Generate translation files
docker compose exec willowcms bin/cake generate_po_files

# Import default translations
docker compose exec willowcms bin/cake load_default_18n
```

---

## 🚢 Production Deployment

For production environments, we provide a separate deployment repository optimized for AWS AppRunner:

**📦 [Production Deployment Guide](https://github.com/matthewdeaves/willow_cms_production_deployment)**

### Production Features
- **Optimized Performance**: Redis caching, query optimization
- **Security Hardened**: Production-ready security configurations
- **Scalable Architecture**: Cloud-native deployment patterns
- **Monitoring**: Built-in logging and performance monitoring

---

## 📚 Documentation

- **[Developer Guide](DeveloperGuide.md)**: Comprehensive development documentation
- **[CakePHP Book](https://book.cakephp.org/5/en/index.html)**: Framework documentation
- **[API Documentation](docs/)**: Generated API docs (if available)

### Key Documentation Sections
1. **Getting Started**: Shell aliases, project structure, and conventions
2. **Feature Development**: Database migrations and best practices
3. **Testing**: Unit tests, coverage reports, and CI/CD
4. **AI Integration**: Service classes and API configurations
5. **Environment Setup**: Docker configuration and deployment

---

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

### Development Process
1. **Fork** the repository
2. **Create** a feature branch
3. **Follow** coding standards (use `phpcs_sniff` and `phpcs_fix`)
4. **Write** tests for new features
5. **Submit** a pull request

### Code Standards
- Follow CakePHP 5.x conventions
- Maintain test coverage above 80%
- Use meaningful commit messages
- Document new features

---

## 📄 License

**Willow CMS is released under the MIT License** - one of the most permissive open source licenses available.

### What this means for you:

✅ **Commercial Use**: Use Willow CMS in commercial projects without restrictions  
✅ **Modification**: Modify the source code to fit your needs  
✅ **Distribution**: Share and redistribute Willow CMS freely  
✅ **Private Use**: Use Willow CMS in private/internal projects  
✅ **Patent Use**: Includes patent protection for users  

### Your only obligations:
- Include the original copyright notice and license text
- Include the [LICENSE](LICENSE) file in distributions

**No attribution required in your final product, no royalties, no restrictions on how you use it.**

See the [LICENSE](LICENSE) file for complete details.

### Why MIT License?

We chose the MIT License to ensure **maximum freedom for developers and organizations**:
- **Startups** can build commercial products without licensing fees
- **Enterprises** can modify and deploy without legal concerns  
- **Open Source Projects** can integrate and extend Willow CMS
- **Educational Institutions** can use it freely for teaching and research
- **Government Agencies** can deploy it without bureaucratic hurdles

**Our philosophy**: Great software should be accessible to everyone.

---

## 🆘 Support

- **Issues**: [GitHub Issues](https://github.com/matthewdeaves/willow/issues)
- **Discussions**: [GitHub Discussions](https://github.com/matthewdeaves/willow/discussions)
- **Documentation**: [Developer Guide](DeveloperGuide.md)

---

## 🙏 Acknowledgments

- **[CakePHP](https://cakephp.org)**: The robust PHP framework powering Willow CMS
- **[Anthropic](https://anthropic.com)**: AI capabilities via Claude API
- **[Google Cloud](https://cloud.google.com)**: Translation services
- **Community**: All contributors and users who make this project possible

---

<div align="center">
  <strong>🌿 Built with passion for the web development community</strong>
</div>