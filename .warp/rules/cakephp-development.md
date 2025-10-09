# CakePHP 5.x Development Rule

## Description
Use CakePHP 5.x documentation and indexed directories for all PHP/PHPUnit/PHP* commands. Follow additional markdown files and the 'dev_aliases.txt' for project-specific custom commands.

## Project Structure Requirements

### Frontend Theme Location
- **DefaultTheme**: `./plugins/DefaultTheme/*`
- **AdminTheme**: `./plugins/AdminTheme/*`
- Ensure frontend fits in DefaultTheme for public routes
- Ensure admin interface fits in AdminTheme for admin routes

### Environment Configuration
- **Environment file location**: `./config/.env` (relative from root directory)
- Use this location for all environment variable configurations
- Never use `.env` files in the root directory

### Documentation References
- Follow project-specific markdown files for guidance
- Check `dev_aliases.txt` for custom command shortcuts
- Use CakePHP 5.x official documentation as reference

## Development Workflow

### Theme Development
```bash
# Frontend changes
./plugins/DefaultTheme/templates/
./plugins/DefaultTheme/webroot/

# Admin interface changes  
./plugins/AdminTheme/templates/
./plugins/AdminTheme/webroot/
```

### Environment Setup
```bash
# Check environment
cat ./config/.env

# Edit environment
nano ./config/.env
```

### Custom Commands
```bash
# Check available aliases
cat dev_aliases.txt

# Use project management
./manage.sh
```

## Apply This Rule
- Always use CakePHP 5.x patterns and conventions
- Place frontend code in appropriate theme directories
- Use `./config/.env` for environment variables
- Reference project documentation and aliases
- Follow MVC architectural patterns