# CakePHP Application Directory

This directory contains the complete CakePHP 5.x application for Willow CMS.

## Directory Structure

- `bin/` - CakePHP console commands and utilities
- `config/` - Application configuration files
  - `.env` - Environment-specific configuration
  - `app.php` - Main application configuration
  - `Migrations/` - Database migration files
- `plugins/` - Theme plugins
  - `AdminTheme/` - Administrative interface theme
  - `DefaultTheme/` - Public website theme
- `resources/` - Resource files and translations
  - `locales/` - Internationalization files
- `src/` - Core application source code
  - `Controller/` - MVC Controllers
  - `Model/` - Database models and entities
  - `Service/` - API and service classes
  - `Job/` - Background job classes
  - `Command/` - CLI commands
- `templates/` - View templates
- `tests/` - Unit and integration tests
- `tmp/` - Temporary files and cache
- `vendor/` - Composer dependencies
- `webroot/` - Public web assets
  - `css/` - Stylesheets
  - `js/` - JavaScript files
  - `img/` - Images
  - `files/` - User uploads

## Development

All CakePHP commands should be run from within the Docker container:

```bash
# Access the container
docker compose exec willowcms /bin/sh

# Run CakePHP commands
/var/www/html/bin/cake migrations migrate
/var/www/html/bin/cake cache clear_all
```

Or use the provided development aliases after running `./setup_dev_aliases.sh`:

```bash
cake_shell migrations migrate
cake_clear_cache
```

## Configuration

The application configuration is managed through:
1. Environment variables in `config/.env`
2. Application configuration in `config/app.php` and `config/app_local.php`
3. Docker environment variables passed through `docker-compose.yml`

## Testing

Run tests from the project root:

```bash
docker compose exec willowcms php /var/www/html/vendor/bin/phpunit
```

Or use the alias:
```bash
phpunit
```
