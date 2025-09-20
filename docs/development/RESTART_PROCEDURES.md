# Willow CMS Environment Restart Procedures

This document provides comprehensive restart instructions for the Willow CMS Docker environment, covering standard restarts, fresh installations, and service-specific troubleshooting.

## 📍 Prerequisites

- Docker and Docker Compose installed
- Working directory: `/Users/mikey/Docs/git-repo-loc/docker-hub/adaptercms-beta/willow/`
- Proper file permissions configured

## 🚀 Standard Restart

Use this for normal application restarts when configuration is already in place.

### Quick Standard Restart
```bash
# Navigate to project directory
cd /Users/mikey/Docs/git-repo-loc/docker-hub/adaptercms-beta/willow/

# Start all services in detached mode
docker-compose up -d
```

### Verification Steps
```bash
# Check service status
docker-compose ps

# Monitor service logs
docker-compose logs -f

# Test application access
curl -I http://localhost:8080
```

### Expected Service URLs After Start
- **Main Application**: http://localhost:8080
- **Admin Panel**: http://localhost:8080/admin
- **phpMyAdmin**: http://localhost:8082
- **Mailpit**: http://localhost:8025
- **Redis Commander**: http://localhost:8084
- **Jenkins**: http://localhost:8081 (if enabled)

---

## 🔧 Fresh Installation Restart

Use this for complete setup from scratch or when configuration files are missing.

### Step 1: Environment Configuration
```bash
# Navigate to project directory
cd /Users/mikey/Docs/git-repo-loc/docker-hub/adaptercms-beta/willow/

# Ensure .env file exists in ./config/
cp ./config/.env.example ./config/.env

# Edit .env with proper values (see configuration section below)
nano ./config/.env
```

### Step 2: Build and Start Services
```bash
# Build and start all services with fresh build
docker-compose up --build -d

# Alternative: Force rebuild without cache
docker-compose build --no-cache
docker-compose up -d
```

### Step 3: Database Initialization
```bash
# Check MySQL service is ready
docker-compose exec mysql mysqladmin ping -h localhost

# Run database migrations (if needed)
docker-compose exec willowcms bin/cake migrations migrate

# Create admin user (if needed)
docker-compose exec willowcms bin/cake create_user admin@example.com password "Admin User"
```

### Step 4: Verification
```bash
# Check all services are running
docker-compose ps

# View application logs
docker-compose logs willowcms

# Test database connection
docker-compose exec mysql mysql -u root -p -e "SHOW DATABASES;"
```

---

## 🎯 Service-Specific Restart Commands

### Individual Service Management

#### WillowCMS Application
```bash
# Restart main application only
docker-compose restart willowcms

# Rebuild and restart application
docker-compose up --build willowcms

# View application logs
docker-compose logs -f willowcms
```

#### MySQL Database
```bash
# Restart MySQL service
docker-compose restart mysql

# Check MySQL health
docker-compose exec mysql mysqladmin ping -h localhost

# Access MySQL shell
docker-compose exec mysql mysql -u root -p
```

#### Redis Cache
```bash
# Restart Redis service
docker-compose restart redis

# Test Redis connection
docker-compose exec redis redis-cli ping

# Clear Redis cache
docker-compose exec redis redis-cli FLUSHALL
```

#### Mailpit Email Testing
```bash
# Restart email service
docker-compose restart mailpit

# Check Mailpit status
docker-compose logs mailpit
```

#### phpMyAdmin
```bash
# Restart database management interface
docker-compose restart phpmyadmin

# Check phpMyAdmin logs
docker-compose logs phpmyadmin
```

#### Redis Commander
```bash
# Restart Redis management interface
docker-compose restart redis-commander

# Check Redis Commander logs
docker-compose logs redis-commander
```

#### Jenkins CI/CD (Optional)
```bash
# Restart Jenkins service
docker-compose restart jenkins

# Check Jenkins initialization
docker-compose logs jenkins
```

---

## ⚙️ Service Dependencies & Startup Order

### Dependency Chain
Services should be started in the following order to respect dependencies:

1. **mysql** → Database must be ready first
2. **redis** → Cache system needed by application
3. **willowcms** → Main application depends on mysql and redis
4. **phpmyadmin** → Depends on mysql
5. **redis-commander** → Depends on redis and willowcms
6. **mailpit** → Independent service
7. **jenkins** → Independent service (optional)

### Recommended Startup Sequence
```bash
# Start core services first
docker-compose up -d mysql redis

# Wait for services to be ready
sleep 10

# Start main application
docker-compose up -d willowcms

# Start management interfaces
docker-compose up -d phpmyadmin redis-commander mailpit

# Start optional services
docker-compose up -d jenkins
```

### Dependency Verification
```bash
# Check MySQL is ready before starting willowcms
docker-compose exec mysql mysqladmin ping -h localhost

# Check Redis is ready before starting willowcms
docker-compose exec redis redis-cli ping
```

---

## 🔧 Port Conflict Resolution

### Default Port Mappings
| Service | Host Port | Container Port | Purpose |
|---------|-----------|----------------|---------|
| willowcms | 8080 | 80 | Main application |
| mysql | 3310 | 3306 | Database server |
| phpmyadmin | 8082 | 80 | Database management |
| mailpit (SMTP) | 1125 | 1025 | Email SMTP |
| mailpit (Web) | 8025 | 8025 | Email web interface |
| redis | 6379 | 6379 | Cache server |
| redis-commander | 8084 | 8081 | Redis management |
| jenkins | 8081 | 8080 | CI/CD server |
| jenkins (agent) | 50000 | 50000 | Jenkins agents |

### Resolving Port Conflicts

#### Check for Port Usage
```bash
# Check if ports are in use
lsof -i :8080
lsof -i :8081
lsof -i :8082
lsof -i :8084
lsof -i :3310
lsof -i :6379
lsof -i :1125
lsof -i :8025

# Alternative using netstat
netstat -tlnp | grep -E ':(8080|8081|8082|8084|3310|6379|1125|8025)'
```

#### Modify Port Mappings
If ports are in conflict, edit `docker-compose.yml`:

```yaml
services:
  willowcms:
    ports:
      - "8090:80"  # Changed from 8080 to 8090
  
  jenkins:
    ports:
      - "8091:8080"  # Changed from 8081 to 8091
      - "50000:50000"
  
  phpmyadmin:
    ports:
      - "8092:80"  # Changed from 8082 to 8092
```

#### Stop Conflicting Services
```bash
# Stop services using conflicting ports
sudo systemctl stop apache2    # If Apache is using port 8080
sudo systemctl stop nginx      # If Nginx is using port 8080

# Kill processes using specific ports
sudo kill -9 $(lsof -t -i:8080)
```

---

## 🛠️ Troubleshooting Common Issues

### Service Won't Start

#### WillowCMS Application Issues
```bash
# Check application logs
docker-compose logs willowcms

# Check container status
docker-compose ps willowcms

# Restart with fresh build
docker-compose up --build willowcms

# Check environment configuration
docker-compose exec willowcms env | grep -E "(DB_|REDIS_|APP_)"
```

#### Database Connection Issues
```bash
# Test MySQL connection
docker-compose exec willowcms ping mysql

# Check MySQL service status
docker-compose exec mysql mysqladmin ping -h localhost

# Verify database credentials
docker-compose exec mysql mysql -u "${DB_USERNAME}" -p"${DB_PASSWORD}" -e "SELECT 1;"

# Check database exists
docker-compose exec mysql mysql -u root -p -e "SHOW DATABASES;"
```

#### Redis Connection Issues
```bash
# Test Redis connectivity
docker-compose exec willowcms ping redis

# Check Redis service
docker-compose exec redis redis-cli ping

# Test Redis from application container
docker-compose exec willowcms redis-cli -h redis ping
```

### Performance Issues

#### Clear Caches
```bash
# Clear application cache
docker-compose exec willowcms rm -rf tmp/cache/models/*
docker-compose exec willowcms rm -rf tmp/cache/persistent/*

# Clear Redis cache
docker-compose exec redis redis-cli FLUSHALL

# Restart services after cache clear
docker-compose restart willowcms redis
```

#### Memory Issues
```bash
# Check container resource usage
docker stats

# Increase memory limits in docker-compose.yml
services:
  willowcms:
    deploy:
      resources:
        limits:
          memory: 1G
        reservations:
          memory: 512M
```

### Permission Issues
```bash
# Fix file permissions
docker-compose exec willowcms chown -R www-data:www-data /var/www/html/
docker-compose exec willowcms chmod -R 755 /var/www/html/

# Fix specific directories
docker-compose exec willowcms chmod -R 777 /var/www/html/tmp/
docker-compose exec willowcms chmod -R 777 /var/www/html/logs/
```

---

## 📝 Environment Configuration (.env) Template

### Required Configuration Variables
```bash
# Application Configuration
APP_NAME="WillowCMS"
DEBUG=true
APP_ENCODING="UTF-8"
APP_DEFAULT_LOCALE="en_US"
APP_DEFAULT_TIMEZONE="UTC"
APP_FULL_BASE_URL="http://localhost:8080"

# Security
SECURITY_SALT="your-secure-random-salt-here-minimum-32-characters"

# Database Configuration
DB_HOST=mysql
DB_USERNAME=cms_user
DB_PASSWORD=cms_password
DB_DATABASE=cms
DB_PORT=3306

# Test Database
TEST_DB_HOST=mysql
TEST_DB_USERNAME=cms_user
TEST_DB_PASSWORD=cms_password
TEST_DB_DATABASE=cms_test
TEST_DB_PORT=3306

# MySQL Root
MYSQL_ROOT_PASSWORD=root_password

# Redis Configuration
REDIS_USERNAME=
REDIS_PASSWORD=
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_DATABASE=0
REDIS_URL="redis://redis:6379"
REDIS_TEST_URL="redis://redis:6379/1"

# Email Configuration
EMAIL_HOST=mailpit
EMAIL_PORT=1025
EMAIL_TIMEOUT=30
EMAIL_USERNAME=
EMAIL_PASSWORD=
EMAIL_REPLY="noreply@localhost"
EMAIL_NOREPLY="noreply@localhost"

# Queue Configuration
QUEUE_DEFAULT_URL="redis://redis:6379"
QUEUE_TEST_URL="redis://redis:6379/2"

# Admin User
WILLOW_ADMIN_USERNAME=admin
WILLOW_ADMIN_PASSWORD=admin_password
WILLOW_ADMIN_EMAIL=admin@localhost

# phpMyAdmin
PMA_HOST=mysql
PMA_USER=root
PMA_PASSWORD=root_password
UPLOAD_LIMIT=64M

# Mailpit
MP_MAX_MESSAGES=500
MP_DATABASE=/data/mailpit.db
MP_SMTP_AUTH_ACCEPT_ANY=true
MP_SMTP_AUTH_ALLOW_INSECURE=true

# Redis Commander
HTTP_USER=admin
HTTP_PASSWORD=admin

# Jenkins (Optional)
JAVA_OPTS="-Xmx1024m"

# API Keys (Optional)
YOUTUBE_API_KEY=
TRANSLATE_API_KEY=

# Development
EXPERIMENTAL_TESTS=false
```

---

## 🔄 Complete Environment Reset

### Full Reset Procedure
```bash
# Navigate to project directory
cd /Users/mikey/Docs/git-repo-loc/docker-hub/adaptercms-beta/willow/

# Stop all services
docker-compose down

# Remove volumes (WARNING: This deletes all data)
docker-compose down -v

# Remove images
docker-compose down --rmi all

# Clean up orphaned containers
docker system prune -f

# Rebuild from scratch
docker-compose up --build -d
```

### Selective Reset
```bash
# Reset only application container
docker-compose stop willowcms
docker-compose rm -f willowcms
docker-compose up --build willowcms

# Reset only database (WARNING: Deletes all data)
docker-compose stop mysql
docker volume rm willow_mysql_data
docker-compose up -d mysql
```

---

## 📋 Quick Reference Commands

### Daily Operations
```bash
# Start environment
docker-compose up -d

# Stop environment
docker-compose down

# Restart specific service
docker-compose restart [service_name]

# View logs
docker-compose logs -f [service_name]

# Check status
docker-compose ps
```

### Maintenance Commands
```bash
# Update images
docker-compose pull

# Rebuild services
docker-compose build --no-cache

# Clean up
docker system prune -f

# Monitor resources
docker stats
```

### Development Commands
```bash
# Access application shell
docker-compose exec willowcms bash

# Access MySQL shell
docker-compose exec mysql mysql -u root -p

# Access Redis CLI
docker-compose exec redis redis-cli

# Run CakePHP commands
docker-compose exec willowcms bin/cake [command]
```

---

## ⚠️ Important Notes

1. **Always backup data** before performing resets or major configuration changes
2. **Environment variables** must be properly configured in `./config/.env`
3. **Service dependencies** require proper startup order
4. **Port conflicts** can prevent services from starting
5. **File permissions** may need adjustment based on host system
6. **Database initialization** may take time on first startup
7. **Log monitoring** is essential for troubleshooting issues

---

## 📞 Support

For additional support:
- Check service logs: `docker-compose logs [service_name]`
- Review docker-compose.yml configuration
- Verify .env file completeness
- Consult Willow CMS documentation
- Check Docker and Docker Compose versions

---

*Last updated: January 2025*
