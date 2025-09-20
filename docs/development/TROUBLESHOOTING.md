# 🔧 Willow CMS Troubleshooting Guide

> **Common issues and solutions for Willow CMS development environment**

This guide provides solutions for common problems encountered during Willow CMS development and deployment.

---

## 📋 Table of Contents

1. [🔌 Port Conflicts](#-port-conflicts)
2. [🔐 Permission Issues](#-permission-issues)
3. [🗄️ Database Connection Issues](#️-database-connection-issues)
4. [🏗️ Build Failures](#️-build-failures)
5. [⚡ Performance Issues](#-performance-issues)
6. [🐳 Docker-Specific Issues](#-docker-specific-issues)
7. [🧪 Testing Issues](#-testing-issues)
8. [🔄 Queue Processing Issues](#-queue-processing-issues)
9. [🌐 Network and Connectivity Issues](#-network-and-connectivity-issues)

---

## 🔌 Port Conflicts

Port conflicts occur when required ports are already in use by other services.

### **Common Ports Used by Willow CMS:**
- **8080**: Main application (willowcms)
- **3310**: MySQL database
- **8082**: phpMyAdmin
- **8081**: Jenkins CI/CD
- **8025**: Mailpit web interface
- **1125**: Mailpit SMTP
- **6379**: Redis
- **8084**: Redis Commander

### **Identify Processes Using Required Ports:**

#### **On macOS/Linux:**
```bash
# Check specific port
lsof -i :8080
netstat -tulpn | grep :8080

# Check all Willow CMS ports
for port in 8080 3310 8082 8081 8025 1125 6379 8084; do
  echo "Port $port:"
  lsof -i :$port
  echo "---"
done
```

#### **On Windows:**
```powershell
# Check specific port
netstat -ano | findstr :8080

# Check process details
tasklist /fi "pid eq PROCESS_ID"
```

### **Kill Conflicting Processes:**

#### **On macOS/Linux:**
```bash
# Kill process by PID
kill -9 PROCESS_ID

# Kill process by port (requires sudo)
sudo kill -9 $(lsof -t -i:8080)

# Force kill Docker containers using ports
docker container ls
docker container stop CONTAINER_ID
docker container rm CONTAINER_ID
```

#### **On Windows:**
```powershell
# Kill process by PID
taskkill /PID PROCESS_ID /F
```

### **Alternative Port Configuration:**

Modify `docker-compose.yml` to use different ports:

```yaml
services:
  willowcms:
    ports:
      - "8090:80"  # Changed from 8080 to 8090
  
  mysql:
    ports:
      - "3311:3306"  # Changed from 3310 to 3311
  
  phpmyadmin:
    ports:
      - "8083:80"  # Changed from 8082 to 8083
  
  jenkins:
    ports:
      - "8091:8080"  # Changed from 8081 to 8091
      - "50001:50000"  # Changed from 50000 to 50001
  
  mailpit:
    ports:
      - "1126:1025"  # Changed from 1125 to 1126
      - "8026:8025"  # Changed from 8025 to 8026
  
  redis-commander:
    ports:
      - "8085:8081"  # Changed from 8084 to 8085
```

**Update your .env file accordingly:**
```bash
APP_FULL_BASE_URL=http://localhost:8090
PMA_HOST=mysql
# Update any other port-dependent configurations
```

---

## 🔐 Permission Issues

Permission problems often occur due to UID/GID mismatches between host and container.

### **UID/GID Configuration:**

The docker-compose.yml shows UID: 501 and GID: 20. Verify these match your system:

```bash
# Check your user and group IDs
id -u  # Should return 501
id -g  # Should return 20

# If different, update docker-compose.yml
# Find your actual IDs and replace the values:
```

**Update docker-compose.yml with correct IDs:**
```yaml
services:
  willowcms:
    build:
      args:
        UID: 1000  # Your actual user ID
        GID: 1000  # Your actual group ID
```

### **Volume Mount Permissions:**

#### **Fix File Ownership:**
```bash
# From host system
sudo chown -R $(id -u):$(id -g) ./

# From within container
docker compose exec willowcms chown -R www-data:www-data /var/www/html
docker compose exec willowcms chmod -R 755 /var/www/html
docker compose exec willowcms chmod -R 777 /var/www/html/tmp
docker compose exec willowcms chmod -R 777 /var/www/html/webroot/img
docker compose exec willowcms chmod -R 777 /var/www/html/logs
```

#### **Set Proper Directory Permissions:**
```bash
# Make scripts executable
chmod +x setup_dev_env.sh
chmod +x setup_dev_aliases.sh
chmod +x manage.sh

# Set proper permissions for sensitive files
chmod 600 config/.env
chmod 644 docker-compose.yml

# Ensure web-writable directories
mkdir -p tmp/cache tmp/logs tmp/sessions tmp/tests
chmod -R 777 tmp/
chmod -R 777 webroot/img/uploads
```

### **SELinux Issues (on RHEL/CentOS):**
```bash
# Check SELinux status
sestatus

# Set SELinux context for Docker volumes
sudo setsebool -P container_manage_cgroup on
sudo chcon -Rt svirt_sandbox_file_t ./
```

---

## 🗄️ Database Connection Issues

Database connectivity problems are common during initial setup.

### **Verify MySQL is Running and Healthy:**

```bash
# Check MySQL container status
docker compose ps mysql

# Check MySQL logs
docker compose logs mysql

# Test database connectivity
docker compose exec mysql mysql -u root -p${MYSQL_ROOT_PASSWORD} -e "SELECT 1;"

# Connect to database from willowcms container
docker compose exec willowcms mysql -h mysql -u ${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE}
```

### **Check Credentials Match:**

Verify that credentials in `.env` match those in `docker-compose.yml`:

**config/.env should contain:**
```bash
# Database Configuration
DB_HOST=mysql
DB_USERNAME=cms_user
DB_PASSWORD=your_secure_password
DB_DATABASE=willowcms
DB_PORT=3306

# MySQL Root Password
MYSQL_ROOT_PASSWORD=your_root_password

# These must match exactly in both services
```

### **Database Initialization Problems:**

#### **Reset Database Completely:**
```bash
# Stop containers
docker compose down

# Remove database volume
docker volume rm willow_mysql_data

# Restart with fresh database
docker compose up -d mysql

# Wait for MySQL to initialize
sleep 30

# Check initialization
docker compose logs mysql | grep "ready for connections"
```

#### **Manual Database Setup:**
```bash
# Connect as root
docker compose exec mysql mysql -u root -p${MYSQL_ROOT_PASSWORD}

# Create database and user manually
CREATE DATABASE IF NOT EXISTS willowcms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'cms_user'@'%' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON willowcms.* TO 'cms_user'@'%';
FLUSH PRIVILEGES;
EXIT;

# Test the new user
docker compose exec mysql mysql -u cms_user -pyour_password willowcms -e "SELECT 1;"
```

#### **Import Database Schema:**
```bash
# If you have a SQL dump file
docker compose exec -i mysql mysql -u cms_user -p${DB_PASSWORD} ${DB_DATABASE} < backup.sql

# Run CakePHP migrations
docker compose exec willowcms bin/cake migrations migrate
```

### **Connection Timeout Issues:**

Add connection timeout settings to your CakePHP configuration:

**config/app_local.php:**
```php
'Datasources' => [
    'default' => [
        'host' => env('DB_HOST', 'mysql'),
        'username' => env('DB_USERNAME', 'cms_user'),
        'password' => env('DB_PASSWORD'),
        'database' => env('DB_DATABASE', 'willowcms'),
        'port' => env('DB_PORT', '3306'),
        'timeout' => 60,
        'persistent' => false,
        'flags' => [],
        'cacheMetadata' => true,
        'log' => false,
        'quoteIdentifiers' => false,
    ],
],
```

---

## 🏗️ Build Failures

Docker build issues can prevent containers from starting.

### **Clear Docker Cache and Rebuild:**

```bash
# Stop all containers
docker compose down

# Clear Docker cache
docker builder prune -a -f
docker system prune -a -f

# Remove specific images
docker rmi docker.io/garzarobmdocker/willowcms:latest
docker rmi docker.io/garzarobmdocker/jenkins:latest

# Rebuild without cache
docker compose build --no-cache --pull

# Start services
docker compose up -d
```

### **Network Timeout Solutions:**

#### **Build with Increased Timeout:**
```bash
# Set Docker client timeout
export DOCKER_CLIENT_TIMEOUT=120
export COMPOSE_HTTP_TIMEOUT=120

# Build with retry
docker compose build --pull || docker compose build --no-cache
```

#### **Use Different Base Images:**
If official images are slow, modify Dockerfiles to use faster mirrors:

**docker/willowcms/Dockerfile:**
```dockerfile
# Use a faster mirror
FROM php:8.1-fpm-alpine

# Add mirror for package installation
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories
```

### **Dockerfile Debugging Steps:**

#### **Debug Build Process:**
```bash
# Build with verbose output
docker compose build --progress=plain

# Build specific service
docker compose build willowcms

# Inspect build context
docker build --dry-run -f docker/willowcms/Dockerfile .
```

#### **Test Intermediate Layers:**
```bash
# Run container at specific layer
docker run -it --rm IMAGE_ID /bin/sh

# Check available space
docker run --rm IMAGE_ID df -h

# Verify dependencies
docker run --rm IMAGE_ID php -m
```

### **Common Build Fixes:**

#### **PHP Extension Issues:**
```bash
# Check required extensions
docker compose exec willowcms php -m | grep -E "(intl|mbstring|openssl|pdo_mysql|simplexml|xml)"

# Install missing extensions (add to Dockerfile)
RUN docker-php-ext-install intl mbstring pdo_mysql
```

#### **Composer Issues:**
```bash
# Clear Composer cache
docker compose exec willowcms composer clear-cache

# Update Composer
docker compose exec willowcms composer self-update

# Install dependencies with verbose output
docker compose exec willowcms composer install -v
```

---

## ⚡ Performance Issues

Performance problems can affect development productivity.

### **Resource Allocation Adjustments:**

#### **Increase Docker Resources:**
- **Docker Desktop**: Settings → Resources → Advanced
  - CPU: 4+ cores
  - Memory: 8+ GB
  - Swap: 2+ GB
  - Disk: 100+ GB

#### **Container Resource Limits:**
Add resource constraints to `docker-compose.yml`:

```yaml
services:
  willowcms:
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 2G
        reservations:
          cpus: '1.0'
          memory: 1G
  
  mysql:
    deploy:
      resources:
        limits:
          cpus: '1.0'
          memory: 1G
        reservations:
          cpus: '0.5'
          memory: 512M
```

### **Volume Performance on Different OS:**

#### **macOS Performance Optimization:**
```yaml
# Use delegated or cached volume mounts
services:
  willowcms:
    volumes:
      - .:/var/www/html/:cached  # Add :cached for better performance
      - ./docker/willowcms/config/app/cms_app_local.php:/var/www/html/config/app_local.php:ro
```

#### **Windows Performance Optimization:**
- Enable WSL 2 backend in Docker Desktop
- Store project files in WSL 2 filesystem
- Use bind mounts instead of volumes where possible

### **Cache Clearing Procedures:**

#### **Application Cache:**
```bash
# Clear CakePHP cache
docker compose exec willowcms bin/cake cache clear_all

# Clear specific caches
docker compose exec willowcms bin/cake cache clear _cake_core_
docker compose exec willowcms bin/cake cache clear _cake_model_

# Clear template cache
docker compose exec willowcms rm -rf tmp/cache/views/*
docker compose exec willowcms rm -rf tmp/cache/persistent/*
```

#### **System Cache:**
```bash
# Clear system caches
docker compose exec willowcms sync && echo 3 > /proc/sys/vm/drop_caches

# Redis cache
docker compose exec redis redis-cli FLUSHALL

# MySQL query cache
docker compose exec mysql mysql -u root -p -e "RESET QUERY CACHE;"
```

#### **Development Tools Cache:**
```bash
# PHPStan cache
docker compose exec willowcms vendor/bin/phpstan clear-result-cache

# Composer cache
docker compose exec willowcms composer clear-cache

# NPM cache (if using)
docker compose exec willowcms npm cache clean --force
```

---

## 🐳 Docker-Specific Issues

Issues specific to Docker containerization.

### **Container Won't Start:**

```bash
# Check container status
docker compose ps

# View container logs
docker compose logs willowcms
docker compose logs mysql

# Start specific service
docker compose up -d willowcms

# Restart problematic service
docker compose restart willowcms
```

### **Service Dependencies:**

Ensure services start in correct order:

```yaml
services:
  willowcms:
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_started
  
  mysql:
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      timeout: 20s
      retries: 10
```

### **Environment Variable Issues:**

```bash
# Check environment variables inside container
docker compose exec willowcms env | grep -E "(DB_|REDIS_|APP_)"

# Validate .env file format
# Ensure no spaces around = and no quotes unless needed
cat config/.env | grep -E "^[A-Z_]+=.*"

# Test environment loading
docker compose config
```

### **Network Connectivity:**

```bash
# Test inter-service connectivity
docker compose exec willowcms ping mysql
docker compose exec willowcms nc -zv mysql 3306
docker compose exec willowcms nc -zv redis 6379

# Check network configuration
docker network ls
docker network inspect willow_cms_default
```

---

## 🧪 Testing Issues

Problems with running tests in the development environment.

### **PHPUnit Configuration:**

```bash
# Run tests with verbose output
docker compose exec willowcms vendor/bin/phpunit --verbose

# Run specific test file
docker compose exec willowcms vendor/bin/phpunit tests/TestCase/Controller/ArticlesControllerTest.php

# Clear test cache
docker compose exec willowcms rm -rf tmp/cache/models/*
docker compose exec willowcms rm -rf tmp/cache/persistent/*
```

### **Test Database Issues:**

```bash
# Create test database
docker compose exec mysql mysql -u root -p${MYSQL_ROOT_PASSWORD} -e "
CREATE DATABASE IF NOT EXISTS willowcms_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON willowcms_test.* TO '${DB_USERNAME}'@'%';
FLUSH PRIVILEGES;"

# Run migrations on test database
docker compose exec willowcms bin/cake migrations migrate --connection=test
```

### **Code Coverage Issues:**

```bash
# Install Xdebug for coverage
# Add to Dockerfile:
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configure Xdebug
echo 'xdebug.mode=coverage' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Run coverage report
docker compose exec willowcms vendor/bin/phpunit --coverage-html coverage/
```

---

## 🔄 Queue Processing Issues

Problems with background job processing.

### **Redis Queue Not Processing:**

```bash
# Check Redis connectivity
docker compose exec willowcms redis-cli -h redis ping

# Start queue worker manually
docker compose exec willowcms bin/cake queue worker

# Check queue status
docker compose exec willowcms bin/cake queue status

# Clear stuck jobs
docker compose exec redis redis-cli FLUSHDB
```

### **Queue Worker Configuration:**

Add queue worker as a service:

```yaml
services:
  queue-worker:
    image: docker.io/garzarobmdocker/willowcms:latest
    command: bin/cake queue worker
    depends_on:
      - willowcms
      - redis
    environment:
      # Same as willowcms service
    volumes:
      - .:/var/www/html/
    networks:
      - cms_default
    restart: unless-stopped
```

### **Monitor Queue Performance:**

```bash
# Queue statistics
docker compose exec willowcms bin/cake queue stats

# Failed job analysis
docker compose exec redis redis-cli KEYS "queue:failed:*"

# Worker process monitoring
docker compose exec willowcms ps aux | grep "queue worker"
```

---

## 🌐 Network and Connectivity Issues

Network-related problems in the Docker environment.

### **DNS Resolution:**

```bash
# Test DNS resolution
docker compose exec willowcms nslookup mysql
docker compose exec willowcms nslookup redis

# Check /etc/hosts
docker compose exec willowcms cat /etc/hosts

# Add custom DNS if needed
```

### **Firewall and Security:**

```bash
# Check if firewall blocks Docker
sudo ufw status
sudo iptables -L DOCKER

# Allow Docker networks
sudo ufw allow from 172.16.0.0/12
sudo ufw allow from 192.168.0.0/16
```

### **External API Connectivity:**

```bash
# Test external API access
docker compose exec willowcms curl -I https://api.anthropic.com
docker compose exec willowcms curl -I https://translate.googleapis.com

# Check proxy settings if behind corporate firewall
docker compose exec willowcms env | grep -i proxy
```

---

## 🔧 Quick Diagnostic Commands

Run these commands for quick problem diagnosis:

### **System Health Check:**
```bash
#!/bin/bash
echo "=== Docker System Info ==="
docker system info

echo "=== Container Status ==="
docker compose ps

echo "=== Volume Usage ==="
docker system df

echo "=== Service Logs (last 50 lines) ==="
for service in willowcms mysql redis mailpit; do
  echo "--- $service ---"
  docker compose logs --tail=50 $service
done

echo "=== Network Connectivity ==="
docker compose exec willowcms ping -c 3 mysql || echo "MySQL connectivity failed"
docker compose exec willowcms ping -c 3 redis || echo "Redis connectivity failed"

echo "=== Database Status ==="
docker compose exec mysql mysqladmin -u root -p${MYSQL_ROOT_PASSWORD} status || echo "MySQL status check failed"

echo "=== Application Health ==="
curl -f http://localhost:8080/health || echo "Application health check failed"
```

### **Performance Diagnostics:**
```bash
#!/bin/bash
echo "=== Resource Usage ==="
docker stats --no-stream

echo "=== Disk Usage ==="
docker system df -v

echo "=== Memory Usage ==="
free -h

echo "=== Load Average ==="
uptime
```

### **Reset Everything (Nuclear Option):**
```bash
#!/bin/bash
echo "⚠️  WARNING: This will destroy all data!"
read -p "Are you sure? (yes/no): " confirm
if [ "$confirm" = "yes" ]; then
  docker compose down -v --remove-orphans
  docker system prune -a -f
  docker volume prune -f
  rm -rf tmp/cache/*
  echo "✅ Everything reset. Run ./setup_dev_env.sh to start fresh."
else
  echo "❌ Reset cancelled."
fi
```

---

## 📞 Getting Help

If you're still experiencing issues:

1. **Check logs**: Always start with container logs
2. **Search documentation**: Check the developer guide and CakePHP docs
3. **GitHub Issues**: Search existing issues in the repository
4. **Community Support**: Join the CakePHP community forums
5. **Professional Support**: Consider hiring a CakePHP consultant

### **Useful Log Locations:**
- Container logs: `docker compose logs [service_name]`
- Application logs: `logs/error.log`, `logs/debug.log`
- Nginx logs: `docker/willowcms/logs/nginx/`
- MySQL logs: Inside mysql container at `/var/log/mysql/`

### **Debug Mode:**
Enable debug mode for more detailed error information:

```bash
# In config/.env
DEBUG=true
LOG_DEBUG=true
LOG_ERROR=true
```

---

<div align="center">
  <strong>🌿 Happy troubleshooting with Willow CMS!</strong>
</div>
