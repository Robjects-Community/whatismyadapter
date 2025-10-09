# Willow CMS - Reorganized Structure

This branch contains a reorganized version of Willow CMS with improved separation of concerns.

## Key Changes

### Directory Structure

The CakePHP application has been moved to a dedicated `cakephp/` directory for better organization:

```
willow/
├── cakephp/                # Complete CakePHP application
│   ├── bin/               # CakePHP console commands
│   ├── config/            # Application configuration
│   ├── plugins/           # Theme plugins
│   ├── src/               # Core application code
│   ├── templates/         # View templates
│   ├── tests/             # Unit tests
│   ├── vendor/            # Composer dependencies
│   └── webroot/           # Public web assets
├── docker/                 # Docker configuration files
│   ├── willowcms/         # Main application container config
│   ├── mysql/             # MySQL container config
│   └── jenkins/           # Jenkins CI config
├── scripts/                # Development and utility scripts
└── docker-compose.yml      # Docker services orchestration
```

### Updated Files

The following files have been updated to work with the new structure:

1. **Docker Configuration**
   - `docker/willowcms/Dockerfile` - Updated to copy files from `cakephp/` directory
   - `docker-compose.yml` - Updated volume mounts to use `cakephp/` directory

2. **Scripts**
   - `setup_dev_env.sh` - Updated paths for the new structure
   - `dev_aliases.txt` - Updated command paths
   - `manage.sh` - Updated to work with new directory structure

3. **Environment Configuration**
   - Environment files now reference `cakephp/config/.env`
   - All CakePHP commands use absolute paths (`/var/www/html/bin/cake`)

## Benefits of This Structure

1. **Clear Separation**: Application code is clearly separated from infrastructure code
2. **Easier Deployment**: The `cakephp/` directory can be deployed independently
3. **Better Organization**: Docker, scripts, and application code are in distinct directories
4. **Simplified Maintenance**: Updates to application or infrastructure can be done independently
5. **Container Optimization**: Docker builds can be more efficient with clear boundaries

## Development Workflow

### Starting the Environment

```bash
# Start the development environment
./setup_dev_env.sh

# Install development aliases
./setup_dev_aliases.sh
```

### Running Commands

All CakePHP commands should be run from within the container:

```bash
# Access the container
docker compose exec willowcms /bin/sh

# Run CakePHP commands
/var/www/html/bin/cake migrations migrate
/var/www/html/bin/cake cache clear_all
```

Or use the development aliases:

```bash
cake_shell migrations migrate
cake_clear_cache
phpunit
```

### File Locations

- **Application code**: `cakephp/src/`
- **Configuration**: `cakephp/config/`
- **Tests**: `cakephp/tests/`
- **Public assets**: `cakephp/webroot/`
- **Uploads**: `cakephp/webroot/files/`
- **Logs**: `cakephp/logs/` and `logs/nginx/`
- **Temp files**: `cakephp/tmp/`

## Migration from Old Structure

If you're migrating from the old structure:

1. Pull this branch
2. Run `./setup_dev_env.sh --wipe` to clean old containers
3. Start fresh with `./setup_dev_env.sh`
4. Update any custom scripts to use the new paths

## Testing

The test suite remains unchanged but should be run with updated paths:

```bash
# Run all tests
docker compose exec willowcms php /var/www/html/vendor/bin/phpunit

# Or use the alias
phpunit
```

## Notes

- All database data and uploads are preserved in Docker volumes
- The application functionality remains unchanged
- This is a structural change only - no application logic has been modified
