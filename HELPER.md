# Willow CMS Directory Structure Reference

## Project Overview

Willow CMS is a PHP-based content management system built with CakePHP framework, containerized with Docker.

## Complete Directory Tree Structure

```text
willow/
├── 📄 Configuration Files (Root Level)
│   ├── AI_IMPROVEMENTS_IMPLEMENTATION_PLAN.md    # AI enhancement roadmap
│   ├── CLAUDE.md                                 # Claude AI interaction guide
│   ├── composer.json                             # PHP dependencies definition
│   ├── composer.lock                             # Locked dependency versions
│   ├── dev_aliases.txt                           # Development command aliases
│   ├── DeveloperGuide.md                         # Developer documentation
│   ├── docker-compose.yml                        # Docker services configuration
│   ├── dump.rdb                                  # Redis database dump
│   ├── HELPER.md                                 # This reference file
│   ├── index.php                                 # Application entry point
│   ├── LICENSE                                   # Project license
│   ├── manage.sh                                 # Project management script
│   ├── phpcs.xml                                 # PHP Code Sniffer config
│   ├── phpstan.neon                              # PHPStan static analysis config
│   ├── phpunit.xml.dist                          # PHPUnit testing config
│   ├── psalm.xml                                 # Psalm static analysis config
│   ├── README.md                                 # Project documentation
│   ├── REFACTORING_PLAN.md                       # Code refactoring plan
│   ├── setup_dev_aliases.sh                      # Development alias setup
│   ├── setup_dev_env.sh                          # Development environment setup
│   └── wait-for-it.sh                           # Docker service wait utility
│
├── 🎨 assets/                                    # Static assets & branding
│   ├── favicon.ico                               # Website favicon
│   ├── icon-text.png                             # Logo with text
│   ├── icon-text.xcf                             # GIMP source file
│   ├── icon.png                                  # Main icon
│   ├── text.xcf                                  # Text design source
│   ├── willow-text.xcf                          # Willow text logo source
│   └── willow.png                               # Willow brand image
│
├── ⚙️ bin/                                       # Executable scripts
│   ├── bash_completion.sh                        # Bash auto-completion
│   ├── cake                                      # CakePHP console (Unix)
│   ├── cake.bat                                  # CakePHP console (Windows)
│   └── cake.php                                  # CakePHP console PHP script
│
├── 🔧 config/                                    # Application configuration
│   ├── app_local.example.php                     # Local config template
│   ├── app_local.php                             # Local environment config
│   ├── app.php                                   # Main application config
│   ├── bootstrap_cli.php                         # CLI bootstrap
│   ├── bootstrap.php                             # Application bootstrap
│   ├── log_config.php                            # Logging configuration
│   ├── paths.php                                 # Path definitions
│   ├── plugins.php                               # Plugin configuration
│   ├── routes.php                                # URL routing rules
│   ├── security.php                              # Security settings
│   ├── Migrations/                               # Database migration files
│   └── schema/                                   # Database schema definitions
│
├── 📊 default_data/                              # Default/seed data (JSON)
│   ├── aiprompts.json                            # AI prompt templates
│   ├── articles_tags.json                        # Article-tag relationships
│   ├── articles_translations.json                # Article translations
│   ├── articles.json                             # Default articles
│   ├── blocked_ips.json                          # IP blocking data
│   ├── comments.json                             # Default comments
│   ├── cookie_consents.json                      # Cookie consent records
│   ├── email_templates.json                      # Email template data
│   ├── image_galleries_images.json               # Gallery-image links
│   ├── image_galleries_translations.json         # Gallery translations
│   ├── image_galleries.json                      # Image galleries
│   ├── images.json                               # Image metadata
│   ├── internationalisations.json                # i18n strings
│   ├── models_images.json                        # Model-image relationships
│   ├── page_views.json                           # Page view analytics
│   ├── phinxlog.json                             # Migration logs
│   ├── settings.json                             # System settings
│   ├── slugs.json                                # URL slugs
│   ├── system_logs.json                          # System log entries
│   ├── tags_translations.json                    # Tag translations
│   ├── tags.json                                 # Content tags
│   ├── user_account_confirmations.json           # Account confirmations
│   └── users.json                                # Default users
│
├── 🐳 docker/                                    # Docker-related files
│   ├── docker-volume-exports/                    # Volume backup exports
│   ├── github/                                   # GitHub integration configs
│   ├── jenkins/                                  # Jenkins CI/CD configs
│   ├── mysql/                                    # MySQL Docker configs
│   └── willowcms/                                # Willow CMS Docker configs
│
├── 🔗 hooks/                                     # Git hooks
│   └── pre-push                                  # Pre-push validation script
│
├── 📝 logs/                                      # Application logs
│   ├── debug.log                                 # Debug information
│   ├── error.log                                 # Error logs
│   └── nginx/                                    # Nginx server logs
│
├── 🔌 plugins/                                   # CakePHP plugins
│   ├── AdminTheme/                               # Admin interface theme
│   └── DefaultTheme/                             # Default frontend theme
│
├── 💾 project_files_backups/                    # File backups
│   └── files_backup_20250701_172137.tar.gz      # Timestamped backup
│
├── 🗄️ project_mysql_backups/                    # Database backups
│
├── 🌍 resources/                                 # Resource files
│   └── locales/                                  # Internationalization files
│
├── 💻 src/                                       # Source code (CakePHP MVC)
│   ├── Application.php                           # Main application class
│   ├── Command/                                  # CLI commands
│   ├── Console/                                  # Console utilities
│   ├── Controller/                               # MVC Controllers
│   ├── Error/                                    # Error handling
│   ├── Http/                                     # HTTP layer
│   ├── Job/                                      # Background jobs
│   ├── Log/                                      # Custom logging
│   ├── Middleware/                               # HTTP middleware
│   ├── Model/                                    # MVC Models (Entity/Table)
│   ├── Service/                                  # Business logic services
│   ├── Utility/                                  # Helper utilities
│   └── View/                                     # View helpers
│
├── 🖼️ templates/                                 # View templates (Twig/PHP)
│   ├── cell/                                     # View cells
│   ├── element/                                  # Reusable elements
│   ├── email/                                    # Email templates
│   ├── Error/                                    # Error page templates
│   └── layout/                                   # Layout templates
│
├── 🧪 tests/                                     # Test suite
│   ├── bootstrap.php                             # Test bootstrap
│   ├── schema.sql                                # Test database schema
│   ├── Fixture/                                  # Test data fixtures
│   ├── TestCase/                                 # Test cases
│   └── Traits/                                   # Test helper traits
│
├── 📁 tmp/                                       # Temporary files
│   ├── debug_kit.sqlite                          # Debug kit database
│   ├── cache/                                    # Application cache
│   ├── sessions/                                 # Session storage
│   └── tests/                                    # Test temp files
│
├── 🛠️ tool_modules/                              # Management tool modules
│   ├── asset_management.sh                       # Asset handling tools
│   ├── common.sh                                 # Common utilities
│   ├── data_management.sh                        # Data management tools
│   ├── internationalization.sh                   # i18n tools
│   ├── service_checks.sh                         # Service health checks
│   ├── system.sh                                 # System operations
│   └── ui.sh                                     # UI management tools
│
├── 📦 vendor/                                    # Composer dependencies
│   ├── autoload.php                              # Composer autoloader
│   └── ...                                       # Third-party packages
│
└── 🌐 webroot/                                   # Public web directory
    ├── favicon.ico                               # Public favicon
    └── ...                                       # CSS, JS, images, uploads
```

## Key Directory Purposes

### 🏗️ **Core Architecture**

- **`src/`** - Main application code following CakePHP MVC pattern
- **`config/`** - All configuration files for app, database, routes
- **`templates/`** - View layer templates and layouts

### 🔧 **Development & Deployment**

- **`docker/`** - Containerization configs for different environments
- **`tool_modules/`** - Custom management scripts for various operations
- **`tests/`** - Comprehensive test suite with fixtures

### 📊 **Data & Content**

- **`default_data/`** - Seed data for initial system setup
- **`webroot/`** - Publicly accessible files (CSS, JS, uploads)
- **`plugins/`** - Modular functionality extensions

### 🔍 **Monitoring & Maintenance**

- **`logs/`** - Application and server logs
- **`project_*_backups/`** - Automated backup storage
- **`tmp/`** - Temporary files and cache

## Development Workflow

1. Use `manage.sh` for common project operations
2. Docker Compose manages the development environment
3. CakePHP console (`bin/cake`) for CLI operations
4. PHPUnit for testing, PHPStan/Psalm for static analysis

## Getting Started

Run `./setup_dev_env.sh` to initialize the development environment with all necessary dependencies and configurations.


### setup_dev_aliases.sh
This script sets up development command aliases for easier access to common tasks.

If completed successfully, it creates aliases in your shell configuration file (e.g., `.bashrc`, `.zshrc`).

Once pre-requisites are met, it asks you to run one of the following options based on your total project configuration:


<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" class="logo" width="120"/>

# Understanding Willow CMS Interactive Menu Options: Rebuilding, Running Migrations, and Continue

Based on the logs from the Willow CMS GitHub repository and the Docker development environment setup, the interactive menu that appears during container startup provides five critical options for managing your development environment. Here's a comprehensive explanation of what each option does:

## Development Environment Initialization Process

The **setup_dev_env.sh** script in Willow CMS follows a systematic approach to prepare the development environment[^1]. The log output shows the complete initialization sequence:

### **Environment Detection and Setup**

The script first detects the host system (Apple Silicon Mac in this case) and creates appropriate environment files with correct UID:GID mappings for Docker volume permissions[^1]. It then creates necessary directories like `logs/nginx` for the web server.

### **Container Status Verification**

The system checks if Docker containers are already running and waits for critical services like MySQL to become available on port 3306 using a `wait-for-it.sh` script. This ensures database connectivity before proceeding.

### **Dependency Management**

Composer dependencies are installed or updated, and post-installation hooks are executed, including CakePHP-specific setup routines.

### **Database State Detection**

The script checks for the existence of a 'settings' table to determine if the database has been previously initialized. When an existing database is detected, it presents the interactive menu with five options.

## Interactive Menu Options Explained

### **[W]ipe Data - Complete Data Reset**

This option performs a **complete data wipe** of the development environment[^2]. Based on the data management module implementation, this includes:

- **Database Reset**: Drops and recreates the entire database, removing all tables, data, and schema
- **Volume Cleanup**: Clears Docker volumes containing persistent data
- **Cache Clearing**: Removes all CakePHP cache files and temporary data
- **Fresh Installation**: Runs initial migrations and imports default data

Use this when you need to start completely fresh or when your database has become corrupted.

### **re[B]uild - Container Reconstruction**

The rebuild option performs a **complete Docker environment reconstruction**[^1][^3]:

- **Image Rebuilding**: Rebuilds all Docker images from their Dockerfiles, incorporating any changes to the base configuration
- **Container Recreation**: Destroys existing containers and creates new ones
- **Dependency Updates**: Downloads and installs the latest versions of system dependencies
- **Configuration Refresh**: Applies any changes made to Docker configuration files

This is essential when you've modified Dockerfiles, updated base images, or need to incorporate system-level changes.

### **[R]estart - Service Restart**

The restart option provides a **soft restart** of the Docker services[^4]:

- **Container Restart**: Stops and starts existing containers without rebuilding
- **Service Refresh**: Restarts web servers, databases, and other services
- **Process Cleanup**: Terminates hanging processes and clears temporary locks
- **Quick Recovery**: Maintains existing data and configuration

Use this for resolving temporary service issues or applying configuration changes that don't require rebuilding.

### **run [M]igrations - Database Schema Updates**

The migrations option specifically handles **database schema evolution**[^5][^6][^7]:

- **CakePHP Migrations**: Executes pending database migrations using CakePHP's migration system
- **Schema Updates**: Applies changes to table structures, indexes, and constraints
- **Data Transformations**: Runs data migration scripts to update existing records
- **Version Control**: Tracks applied migrations to prevent duplicate execution

This is crucial for keeping your database schema in sync with code changes, especially when working with team members or deploying updates.

### **[C]ontinue - Proceed Without Changes**

The continue option allows you to **proceed with the existing setup**:

- **No Modifications**: Leaves all containers, data, and configuration unchanged
- **Service Verification**: Performs basic health checks to ensure services are running
- **Cache Warming**: May perform minimal cache operations to ensure optimal performance
- **Quick Start**: Proceeds directly to the development environment

Use this when your environment is already properly configured and you simply want to start development.

## Implementation Details

### **Database Management System**

Willow CMS uses a sophisticated database management system that includes automated backup and restore capabilities[^2]. The system can:

- **Create timestamped backups** with comprehensive verification
- **Restore from backups** with data integrity checks
- **Manage backup lifecycle** including cleanup operations
- **Validate SQL file integrity** before restoration


### **CakePHP Integration**

The migration system leverages **CakePHP 5.x's built-in migration capabilities**[^6][^7]:

- **Reversible Migrations**: Supports both up and down migration paths
- **Automated Generation**: Can automatically generate migrations from model changes
- **Dependency Management**: Handles migration dependencies and execution order
- **Error Handling**: Provides comprehensive error reporting and rollback capabilities


### **Docker Environment Architecture**

The development environment includes **multiple interconnected services**[^1]:

- **Nginx**: Web server for handling HTTP requests
- **PHP**: Application runtime with CakePHP framework
- **MySQL**: Primary database server
- **Redis**: Caching and session storage
- **PHPMyAdmin**: Database administration interface
- **Mailpit**: Email testing and debugging
- **Jenkins**: Continuous integration (optional)


## Best Practices and Recommendations

### **When to Use Each Option**

**Use Wipe [W] when:**

- Starting a new feature that requires clean data
- Database corruption has occurred
- You need to test installation procedures
- Switching between major development branches

**Use Rebuild [B] when:**

- Docker configuration has changed
- System dependencies need updating
- Performance issues suggest container problems
- Base images have been updated

**Use Restart [R] when:**

- Services appear unresponsive
- Configuration files have been modified
- Memory usage is high
- Simple connectivity issues occur

**Use Migrations [M] when:**

- Database schema has been updated
- Working with team members who have made schema changes
- Deploying to different environments
- Updating from version control

**Use Continue [C] when:**

- Environment is working correctly
- No changes have been made since last startup
- You want to resume previous work immediately
- Performing routine development tasks

This interactive system ensures that developers can efficiently manage their Willow CMS development environment while maintaining data integrity and system stability[^1][^2].

[^1]: [Willow CMS GitHub Repository](https://github.com/matthewdeaves/willow)
[^2]: [Willow CMS Docker Development Environment](https://github.com/matthewdeaves/willow/blob/main/setup_dev_env.sh)
[^3]: [Docker Rebuild Documentation](https://docs.docker.com/engine/reference/commandline/build/)
[^4]: [Docker Restart Command](https://docs.docker.com/engine/reference/commandline/restart/)
[^5]: [CakePHP Migrations Guide](https://book.cakephp.org/5/en/cli/migrations.html)
[^6]: [CakePHP Migration System](https://book.cakephp.org/5/en/cli/migrations.html#creating-migrations)
[^7]: [CakePHP Migration Best Practices](https://book.cakephp.org/5/en/cli/migrations.html#best-practices-for-migrations)
```