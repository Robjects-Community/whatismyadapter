#!/bin/bash

# WillowCMS Repository Reorganization Script
# This script safely reorganizes your WillowCMS project into a beautiful, maintainable structure

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

warn() {
    echo -e "${YELLOW}[WARN] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

# Check if we're in the right directory
if [[ ! -f "docker-compose.yml" ]] || [[ ! -d "cakephp" ]]; then
    error "This script should be run from the WillowCMS root directory"
    exit 1
fi

log "Starting WillowCMS Repository Reorganization..."

# Create backup
log "Creating backup of current structure..."
BACKUP_DIR="willow-backup-$(date +%Y%m%d_%H%M%S)"
tar -czf "${BACKUP_DIR}.tar.gz" . --exclude='./node_modules' --exclude='./vendor' --exclude='./.git'
log "Backup created: ${BACKUP_DIR}.tar.gz"

# Phase 1: Create new directory structure
log "Phase 1: Creating new directory structure..."

# Main directories
mkdir -p infrastructure/docker/{nginx,php,mysql}
mkdir -p deploy/{scripts,environments}
mkdir -p docs/{api,architecture,deployment,development}
mkdir -p tools/{scripts,quality,fixtures}
mkdir -p storage/{app/{uploads,cache,temp},backups/{database,files,logs},seeds}
mkdir -p assets/{images,fonts,brand}

# Application directories (will be created when we move cakephp/)
log "Created main directory structure"

# Phase 2: Move Docker and infrastructure files
log "Phase 2: Moving Docker and infrastructure files..."

# Move docker directory
if [[ -d "docker" ]]; then
    mv docker/* infrastructure/docker/ 2>/dev/null || true
    rmdir docker 2>/dev/null || warn "Could not remove empty docker directory"
fi

# Move docker-compose files
mv docker-compose*.yml deploy/ 2>/dev/null || warn "No docker-compose files to move"

# Keep main docker-compose.yml at root level for convenience
if [[ -f "deploy/docker-compose.yml" ]]; then
    cp deploy/docker-compose.yml ./
fi

log "Moved Docker infrastructure files"

# Phase 3: Move documentation
log "Phase 3: Organizing documentation..."

# Move markdown files to docs
find . -maxdepth 1 -name "*.md" -not -name "README.md" -exec mv {} docs/ \; 2>/dev/null || true

# Keep README.md at root
if [[ -f "docs/README.md" ]]; then
    mv docs/README.md ./README.md.temp
    mv README.md.temp ./README.md
fi

log "Organized documentation files"

# Phase 4: Rename and organize main application
log "Phase 4: Reorganizing main application..."

# Rename cakephp to app
mv cakephp/ app/

# Create service directories in the app
mkdir -p app/src/Service/{Admin,Image,Email}
mkdir -p app/src/Utility/{Traits,Enum}

# Move logs to app directory
if [[ -d "logs" ]]; then
    mv logs/ app/
fi

log "Renamed cakephp/ to app/ and created service directories"

# Phase 5: Organize configuration files
log "Phase 5: Organizing configuration files..."

# Create environments directory in app config
mkdir -p app/config/environments/

# Move .env files to environments
find . -maxdepth 1 -name ".env*" -exec mv {} app/config/environments/ \; 2>/dev/null || true

# Keep .env.example at root for convenience
if [[ -f "app/config/environments/.env.example" ]]; then
    cp app/config/environments/.env.example ./
fi

log "Organized configuration files"

# Phase 6: Move assets and cleanup
log "Phase 6: Moving assets and cleaning up..."

# Move assets to brand folder
if [[ -d "assets" ]]; then
    mkdir -p assets/brand
    find assets/ -maxdepth 1 -type f -exec mv {} assets/brand/ \; 2>/dev/null || true
fi

# Move backup directories to storage
if [[ -d "project_files_backups" ]]; then
    mv project_files_backups/ storage/backups/files/
fi

if [[ -d "project_mysql_backups" ]]; then
    mv project_mysql_backups/ storage/backups/database/
fi

if [[ -d "backups" ]]; then
    rsync -av backups/ storage/backups/ && rm -rf backups/
fi

log "Moved assets and backup files"

# Phase 7: Move development tools
log "Phase 7: Organizing development tools..."

# Move scripts to tools
mv setup_dev_aliases.sh tools/scripts/ 2>/dev/null || true
mv run_dev_env.sh tools/scripts/ 2>/dev/null || true
mv manage.sh tools/scripts/ 2>/dev/null || true

# Move code quality configs to tools
mv phpcs.xml tools/quality/ 2>/dev/null || true
mv psalm.xml tools/quality/ 2>/dev/null || true
if [[ -f "app/phpstan.neon" ]]; then
    mv app/phpstan.neon tools/quality/
fi

# Move dev aliases and make it a template
if [[ -f "dev_aliases.txt" ]]; then
    mv dev_aliases.txt tools/dev_aliases_template.txt
fi

log "Organized development tools"

# Phase 8: Cleanup unnecessary files
log "Phase 8: Cleaning up root directory..."

# Move miscellaneous files
mv default_data/ storage/seeds/ 2>/dev/null || true
mv checksums/ storage/backups/logs/ 2>/dev/null || true

# Remove or organize helper files
if [[ -d "helper-files(use-only-if-you-get-lost)" ]]; then
    mv "helper-files(use-only-if-you-get-lost)" tools/legacy-helpers/
fi

# Clean up test files in root
find . -maxdepth 1 -name "test_*.php" -exec mv {} tools/scripts/ \; 2>/dev/null || true

log "Cleaned up root directory"

# Phase 9: Create essential files
log "Phase 9: Creating essential management files..."

# Create Makefile
cat > Makefile << 'EOF'
# WillowCMS Development Commands
.PHONY: help install start stop test quality backup logs clean

help:            ## Show this help message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

install:         ## Install dependencies and setup environment
	docker-compose run --rm willowcms composer install
	@echo "Environment setup complete!"

start:           ## Start development environment
	docker-compose up -d
	@echo "Development environment started!"

stop:            ## Stop development environment
	docker-compose down
	@echo "Development environment stopped!"

test:            ## Run all tests
	docker-compose exec willowcms vendor/bin/phpunit

test-unit:       ## Run unit tests only
	docker-compose exec willowcms vendor/bin/phpunit --testsuite=unit

quality:         ## Run code quality checks
	docker-compose exec willowcms vendor/bin/phpcs --standard=PSR12 src/
	@echo "Code quality check complete!"

migrate:         ## Run database migrations
	docker-compose exec willowcms bin/cake migrations migrate

seed:            ## Seed database with sample data
	docker-compose exec willowcms bin/cake migrations seed

backup:          ## Create database backup
	@./tools/scripts/backup_db.sh

logs:            ## View application logs
	docker-compose logs -f willowcms

clean:           ## Clean up temporary files and caches
	docker-compose exec willowcms bin/cake cache clear_all
	docker system prune -f
	@echo "Cleanup complete!"

status:          ## Show service status
	docker-compose ps
EOF

# Create a comprehensive README
cat > README.md << 'EOF'
# WillowCMS 

A modern, professional Content Management System built with CakePHP 5.x and Docker.

## 🚀 Quick Start

```bash
# Clone the repository
git clone <your-repo-url>
cd willow

# Copy environment configuration
cp .env.example app/config/environments/.env.local

# Start the development environment
make start

# Install dependencies
make install

# Run migrations
make migrate

# Visit your application
open http://localhost:8080
```

## 📁 Project Structure

```
willow/
├── app/                    # Main CakePHP application
├── infrastructure/         # Docker and infrastructure configs
├── deploy/                 # Deployment configurations
├── docs/                   # Documentation
├── tools/                  # Development tools and scripts
├── storage/                # File storage and backups
└── assets/                 # Static assets and branding
```

## 🛠️ Development Commands

Use `make help` to see all available commands:

- `make start` - Start development environment
- `make test` - Run tests
- `make quality` - Check code quality
- `make backup` - Create database backup
- `make logs` - View application logs

## 📚 Documentation

- [Development Setup](docs/development/SETUP.md)
- [Architecture Overview](docs/ARCHITECTURE.md)
- [API Documentation](docs/API.md)
- [Deployment Guide](docs/DEPLOYMENT.md)

## 🎯 Features

- ✅ Modern CakePHP 5.x architecture
- ✅ Docker-based development environment
- ✅ Comprehensive testing suite
- ✅ Admin interface with file upload
- ✅ Log integrity verification
- ✅ Automated backups
- ✅ CI/CD ready

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
EOF

# Create development setup guide
cat > docs/development/SETUP.md << 'EOF'
# Development Setup Guide

## Prerequisites

- Docker and Docker Compose
- Git
- Make (optional but recommended)

## Environment Setup

1. **Clone the repository:**
   ```bash
   git clone <your-repo-url>
   cd willow
   ```

2. **Configure environment:**
   ```bash
   cp .env.example app/config/environments/.env.local
   # Edit app/config/environments/.env.local with your settings
   ```

3. **Start services:**
   ```bash
   make start
   # or: docker-compose up -d
   ```

4. **Install dependencies:**
   ```bash
   make install
   # or: docker-compose run --rm willowcms composer install
   ```

5. **Run migrations:**
   ```bash
   make migrate
   # or: docker-compose exec willowcms bin/cake migrations migrate
   ```

## Testing

Run the test suite with:
```bash
make test
# or: docker-compose exec willowcms vendor/bin/phpunit
```

## Code Quality

Check code quality with:
```bash
make quality
# or: docker-compose exec willowcms vendor/bin/phpcs
```

## Daily Development Workflow

1. Start your day: `make start`
2. Run tests: `make test`
3. Check code quality: `make quality` 
4. View logs: `make logs`
5. End of day: `make stop`
EOF

# Create backup script
cat > tools/scripts/backup_db.sh << 'EOF'
#!/bin/bash
# Database backup script

BACKUP_DIR="storage/backups/database"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="willow_backup_${DATE}.sql"

mkdir -p $BACKUP_DIR

echo "Creating database backup..."
docker-compose exec -T mysql mysqldump -u root -p$(grep DB_PASS .env | cut -d'=' -f2) willow_cms > "${BACKUP_DIR}/${BACKUP_FILE}"

echo "Database backup created: ${BACKUP_DIR}/${BACKUP_FILE}"

# Keep only last 5 backups
cd $BACKUP_DIR
ls -t willow_backup_*.sql | tail -n +6 | xargs rm -f 2>/dev/null || true

echo "Backup cleanup complete!"
EOF

chmod +x tools/scripts/backup_db.sh

log "Created essential management files"

# Phase 10: Update important file paths
log "Phase 10: Updating file paths..."

# Update docker-compose.yml paths if it exists
if [[ -f "docker-compose.yml" ]]; then
    # Update volume mappings from cakephp to app
    sed -i.bak 's|./cakephp|./app|g' docker-compose.yml
    rm -f docker-compose.yml.bak
    log "Updated Docker Compose paths"
fi

log "Updated configuration paths"

# Final summary
log "Repository reorganization complete!"

info ""
info "🎉 WillowCMS has been successfully reorganized!"
info ""
info "📁 New structure created:"
info "   • app/ - Main CakePHP application (formerly cakephp/)"
info "   • infrastructure/ - Docker and infrastructure"
info "   • deploy/ - Deployment configurations"  
info "   • docs/ - All documentation"
info "   • tools/ - Development tools and scripts"
info "   • storage/ - File storage and backups"
info "   • assets/ - Static assets and branding"
info ""
info "🛠️  Essential files created:"
info "   • Makefile - Common development commands"
info "   • README.md - Project overview"
info "   • docs/development/SETUP.md - Development guide"
info "   • tools/scripts/backup_db.sh - Database backup script"
info ""
info "💾 Backup created: ${BACKUP_DIR}.tar.gz"
info ""
info "🚀 Next steps:"
info "   1. Review the new structure"
info "   2. Test the application: make start && make test"
info "   3. Update any custom scripts to use new paths"
info "   4. Commit the changes to version control"
info ""
info "Run 'make help' to see all available commands!"
EOF