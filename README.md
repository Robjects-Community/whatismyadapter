# WhatIsMyAdaptor - Local Development Setup

A CakePHP application for adapter management with Docker development environment.

## Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/Robjects-Community/WhatIsMyAdaptor.git
   cd WhatIsMyAdaptor
   ```

2. **Configure environment variables**
   ```bash
   # Copy and customize the environment file
   cp stack.env .env.local
   # Edit .env.local with your preferred settings
   ```

3. **Start the development environment**
   ```bash
   # Start all services
   docker-compose --env-file stack.env up -d
   
   # OR run the setup script
   ./setup_dev_env.sh
   ```

4. **Run database migrations**
   ```bash
   docker-compose --env-file stack.env exec willowcms bash -c 'cd /var/www/html && bin/cake migrations migrate'
   ```

5. **Create admin user**
   ```bash
   docker-compose --env-file stack.env exec willowcms bash -c 'cd /var/www/html && bin/cake create_user --email="admin@test.com" --username="admin" --password="password" --is_admin=true'
   ```

## Services & Access

- **WillowCMS Application**: http://localhost:8080
- **Admin Panel**: http://localhost:8080/admin  
- **PHPMyAdmin**: http://localhost:7771
- **Mailpit (Email Testing)**: http://localhost:7773
- **Redis Commander**: http://localhost:7774
- **Jenkins (Optional)**: http://localhost:7772

## Environment Variables

All configuration is managed through `stack.env`. Key variables include:

### Required Security Settings
```bash
SECURITY_SALT=your_64_character_hex_string
MYSQL_ROOT_PASSWORD=your_secure_root_password
MYSQL_PASSWORD=your_secure_user_password
REDIS_PASSWORD=your_secure_redis_password
WILLOW_ADMIN_PASSWORD=your_admin_password
```

### Application Settings
```bash
APP_NAME=WillowCMS
APP_HTTP_PORT=8080
APP_FULL_BASE_URL=http://localhost:8080
DEBUG=false
```

### Database Settings
```bash
MYSQL_DATABASE=cms
MYSQL_USER=cms_user
MYSQL_PORT=3310
```

## Development Commands

### CakePHP Commands
```bash
# Run migrations
docker-compose --env-file stack.env exec willowcms bin/cake migrations migrate

# Check table exists
docker-compose --env-file stack.env exec willowcms bin/cake check_table_exists settings

# Create user
docker-compose --env-file stack.env exec willowcms bin/cake create_user --email="user@example.com" --username="user" --password="password" --is_admin=false

# Clear cache
docker-compose --env-file stack.env exec willowcms bin/cake cache clear_all
```

### Docker Commands
```bash
# View logs
docker-compose --env-file stack.env logs willowcms

# Access container shell
docker-compose --env-file stack.env exec willowcms bash

# Restart services
docker-compose --env-file stack.env restart

# Stop all services
docker-compose --env-file stack.env down
```

## Setup Script

The `setup_dev_env.sh` script provides additional functionality:

```bash
# Normal startup
./setup_dev_env.sh

# With Jenkins and i18n data
./setup_dev_env.sh -j -i

# Wipe data and restart
./setup_dev_env.sh --wipe

# Just run migrations
./setup_dev_env.sh --migrate

# Non-interactive mode
./setup_dev_env.sh --no-interactive
```

## Volume Management

Data is persisted in Docker volumes:
- `mysql_data` - Database files
- `redis_data` - Redis persistence  
- `jenkins_home` - Jenkins configuration
- `willowcms_logs` - Application logs
- `app_uploads` - User uploaded files
- `app_tmp` - Temporary files

## Troubleshooting

### Database Connection Issues
```bash
# Check MySQL is ready
docker-compose --env-file stack.env exec mysql mysqladmin ping -h localhost -u root -p

# Reset database
docker-compose --env-file stack.env down -v
docker-compose --env-file stack.env up -d
```

### Port Conflicts
If you get port conflict errors, modify these variables in `stack.env`:
```bash
APP_HTTP_PORT=8080
MYSQL_PORT=3310
PHPMYADMIN_PORT=7771
# etc.
```

### Permission Issues (macOS)
The `.env` file is automatically created for Apple Silicon Macs to handle UID/GID mapping.

## Production Deployment

For production deployment, see the deployment guides in the documentation:
- `PORTAINER_DEPLOYMENT_GUIDE.md` - Portainer deployment
- `PORTAINER_ONLINE_DEPLOYMENT.md` - Online Portainer setup

**Important**: Always change default passwords in `stack.env` before deploying to production!

## Generate Secure Values

```bash
# Security salt (64 hex characters)
python3 -c "import secrets; print(secrets.token_hex(32))"

# Strong passwords (24+ characters)  
python3 -c "import secrets, string; print(''.join(secrets.choice(string.ascii_letters + string.digits + '!@#$%^&*') for i in range(24)))"
```
