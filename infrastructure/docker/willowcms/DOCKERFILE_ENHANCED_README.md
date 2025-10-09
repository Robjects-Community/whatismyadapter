# Enhanced Multi-Stage Dockerfile for WillowCMS

## 📋 Overview

The enhanced Dockerfile (`Dockerfile.enhanced`) provides a production-ready, multi-stage build for WillowCMS with the following features:

### ✨ Key Features

- **🏗️ Multi-Stage Build**: Separates build-time dependencies from runtime, resulting in smaller images
- **🔒 Security Hardened**: Runs as non-root user, no hardcoded secrets, minimal attack surface
- **🌍 Multi-Platform Support**: Works on AMD64 and ARM64 architectures
- **⚡ Performance Optimized**: Composer autoloader optimization, efficient layer caching
- **🚀 CakePHP 5.x Ready**: All required PHP extensions and configurations
- **📊 Health Checks**: Built-in health monitoring for container orchestration
- **🔐 Environment Variable Support**: All secrets managed via .env files

## 📐 Architecture

### Build Stages

```
┌─────────────────────────────────────────────────────────────┐
│  Stage 1: Composer Binary                                   │
│  Purpose: Extract clean composer binary                     │
│  Base: composer:2                                           │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  Stage 2: Dependencies                                       │
│  Purpose: Install & optimize Composer dependencies          │
│  Base: robjects/dhi-php-mirror-robjects:8.3-alpine3.22-dev │
│  Actions:                                                    │
│    - Install git, zip, unzip, bash                          │
│    - Copy application code                                   │
│    - Run composer dump-autoload --no-dev --optimize         │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  Stage 3: Production                                         │
│  Purpose: Final runtime image                               │
│  Base: robjects/dhi-php-mirror-robjects:8.3-alpine3.22-dev │
│  Components:                                                 │
│    - PHP 8.3 + Extensions                                   │
│    - Nginx web server                                        │
│    - MySQL client                                            │
│    - Redis support                                           │
│    - ImageMagick                                             │
│  User: willowcms (non-root)                                 │
└─────────────────────────────────────────────────────────────┘
```

## 🚀 Quick Start

### Prerequisites

- Docker 20.10+ installed
- Docker Compose 2.0+ installed
- `.env` file configured (see Configuration section)

### Option 1: Build Locally

```bash
# Navigate to project root
cd /Volumes/1TB_DAVINCI/docker/willow

# Build with default UID/GID (1000:1000)
docker build \
  -f infrastructure/docker/willowcms/Dockerfile.enhanced \
  -t willowcms:enhanced .

# Build with custom UID/GID (e.g., your Mac user)
docker build \
  -f infrastructure/docker/willowcms/Dockerfile.enhanced \
  --build-arg UID=501 \
  --build-arg GID=20 \
  -t willowcms:enhanced .
```

### Option 2: Use with Docker Compose

Update your `docker-compose.yml`:

```yaml
services:
  willowcms:
    image: willowcms:enhanced
    build:
      context: .
      dockerfile: infrastructure/docker/willowcms/Dockerfile.enhanced
      args:
        - UID=${DOCKER_UID:-1000}
        - GID=${DOCKER_GID:-1000}
    # ... rest of configuration
```

Then run:

```bash
docker compose build
docker compose up -d
```

### Option 3: Multi-Platform Build

For deploying to different architectures:

```bash
# Create buildx builder if not exists
docker buildx create --name multiplatform --use

# Build for both AMD64 and ARM64
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  -f infrastructure/docker/willowcms/Dockerfile.enhanced \
  -t willowcms:enhanced \
  --push .
```

## ⚙️ Configuration

### Environment Variables (.env)

Your `.env` file should contain:

```bash
# ============================================================================
# User & Permissions
# ============================================================================
DOCKER_UID=501    # Your user ID (run 'id -u' to find yours)
DOCKER_GID=20     # Your group ID (run 'id -g' to find yours)

# ============================================================================
# Database Configuration
# ============================================================================
MYSQL_ROOT_PASSWORD=your_secure_root_password
MYSQL_DATABASE=willowcms_db
MYSQL_USER=willowcms_user
MYSQL_PASSWORD=your_secure_db_password

# ============================================================================
# Redis Configuration
# ============================================================================
REDIS_PASSWORD=your_secure_redis_password
REDIS_USERNAME=default
REDIS_DATABASE=0

# ============================================================================
# Application Security
# ============================================================================
SECURITY_SALT=your_64_character_random_string_here

# ============================================================================
# Application Settings
# ============================================================================
APP_NAME=WillowCMS
DEBUG=false  # Set to false in production
APP_ENV=production
APP_FULL_BASE_URL=https://yourdomain.com
```

### Finding Your UID/GID

```bash
# On macOS or Linux
id

# Output example:
# uid=501(mikey) gid=20(staff) groups=20(staff),...
```

Use `UID=501` and `GID=20` in your `.env` file.

## 🔧 Build Arguments

The Dockerfile accepts the following build arguments:

| Argument | Default | Description |
|----------|---------|-------------|
| `UID` | `1000` | User ID for the willowcms user |
| `GID` | `1000` | Group ID for the willowcms user |
| `TARGETPLATFORM` | Auto-detected | Target platform (e.g., linux/amd64) |
| `BUILDPLATFORM` | Auto-detected | Build platform |

## 📦 What's Included

### PHP Extensions

- **Core**: ctype, curl, dom, fileinfo, gd, intl, mbstring, opcache, openssl, phar, session, tokenizer, xml, xmlreader, xmlwriter, simplexml
- **Database**: pdo_mysql, pdo_sqlite, mysqli
- **Additional**: bcmath, sockets, zip, pcntl
- **PECL**: imagick, msgpack, redis, xdebug

### System Packages

- **Web Server**: Nginx
- **Database Client**: MySQL Client
- **Caching**: Redis
- **Image Processing**: ImageMagick
- **Utilities**: curl, wget, unzip, bash

## 🏃 Running the Container

### Standalone Run

```bash
docker run -d \
  --name willowcms \
  -p 8080:80 \
  -e MYSQL_HOST=mysql \
  -e MYSQL_USER=willowcms_user \
  -e MYSQL_PASSWORD=your_password \
  -e MYSQL_DATABASE=willowcms_db \
  -e REDIS_HOST=redis \
  -e REDIS_PASSWORD=your_redis_password \
  -e SECURITY_SALT=your_64_char_salt \
  willowcms:enhanced
```

### With Docker Compose

```bash
# Start all services
docker compose up -d

# View logs
docker compose logs -f willowcms

# Stop services
docker compose down

# Rebuild and restart
docker compose up -d --build
```

## 🩺 Health Checks

The container includes a built-in health check that:

- Checks every 30 seconds
- Times out after 3 seconds
- Waits 5 seconds after container start
- Marks unhealthy after 3 consecutive failures

Monitor health:

```bash
# Check health status
docker ps

# View detailed health check logs
docker inspect willowcms | jq '.[0].State.Health'
```

## 🔍 Troubleshooting

### Permission Issues

If you encounter permission errors with mounted volumes:

```bash
# Check what user the container is running as
docker compose exec willowcms id

# If UID/GID don't match your host user, rebuild with correct values:
export DOCKER_UID=$(id -u)
export DOCKER_GID=$(id -g)
docker compose build --no-cache
docker compose up -d
```

### Container Won't Start

```bash
# Check container logs
docker compose logs willowcms

# Check if ports are already in use
lsof -i :8080

# Verify configuration files exist
ls -la infrastructure/docker/willowcms/config/
```

### Database Connection Issues

```bash
# Verify MySQL is running
docker compose ps mysql

# Test database connection from container
docker compose exec willowcms mysql -h mysql -u willowcms_user -p

# Check environment variables
docker compose exec willowcms env | grep -E '(MYSQL|DB_)'
```

### PHP Extension Issues

```bash
# List loaded PHP extensions
docker compose exec willowcms php -m

# Check PHP configuration
docker compose exec willowcms php -i

# Verify extension load order
docker compose exec willowcms ls -la /etc/php83/conf.d/
```

## 🧪 Testing the Build

### 1. Build the Image

```bash
docker build \
  -f infrastructure/docker/willowcms/Dockerfile.enhanced \
  --build-arg UID=$(id -u) \
  --build-arg GID=$(id -g) \
  -t willowcms:test .
```

### 2. Run a Test Container

```bash
docker run --rm -it willowcms:test /bin/bash
```

### 3. Verify Inside Container

```bash
# Check user
whoami
id

# Check PHP version
php -v

# Check PHP extensions
php -m | grep -E '(redis|mysqli|imagick)'

# Check Nginx
nginx -v

# Check application files
ls -la /var/www/html/
```

## 📊 Performance Optimization

### Image Size

The multi-stage build significantly reduces image size:

```bash
# Check image size
docker images | grep willowcms

# Compare with single-stage build
docker history willowcms:enhanced
```

### Build Cache

To maximize build cache efficiency:

```bash
# Build with BuildKit
export DOCKER_BUILDKIT=1
docker build -f infrastructure/docker/willowcms/Dockerfile.enhanced -t willowcms:enhanced .

# Clear build cache if needed
docker builder prune -af
```

## 🔐 Security Best Practices

### ✅ What This Dockerfile Does

- ✅ Runs as non-root user (`willowcms`)
- ✅ No hardcoded secrets or passwords
- ✅ Minimal base image (Alpine Linux)
- ✅ Regular security updates via base image
- ✅ Health checks for monitoring
- ✅ Proper file permissions

### ⚠️ What You Should Do

- ⚠️ Keep `.env` file out of version control
- ⚠️ Use strong, unique passwords
- ⚠️ Regularly update base images
- ⚠️ Monitor container logs
- ⚠️ Use HTTPS in production
- ⚠️ Implement proper firewall rules

## 📚 Additional Resources

### Related Files

- `Dockerfile` - Root level Dockerfile (alternative version)
- `Dockerfile.multistage` - Previous multi-stage version
- `docker-compose.yml` - Docker Compose configuration
- `.env.example` - Example environment variables
- `CLEANUP_PROCEDURES.md` - Cleanup and maintenance guide

### Documentation

- [CakePHP 5.x Documentation](https://book.cakephp.org/5/en/index.html)
- [Docker Multi-Stage Builds](https://docs.docker.com/build/building/multi-stage/)
- [Docker Security Best Practices](https://docs.docker.com/engine/security/)
- [Alpine Linux Packages](https://pkgs.alpinelinux.org/packages)

## 🆘 Getting Help

If you encounter issues:

1. Check the logs: `docker compose logs willowcms`
2. Verify configuration files are present
3. Ensure `.env` file has all required variables
4. Check UID/GID matches your host user
5. Verify all required ports are available
6. Review the troubleshooting section above

## 📝 Changelog

### Version 1.0.0-enhanced (Current)

- ✨ Initial enhanced multi-stage Dockerfile
- ✨ Flexible UID/GID support
- ✨ Security hardening
- ✨ Multi-platform support
- ✨ Comprehensive documentation
- ✨ Health check integration
- ✨ Optimized Composer autoloader
- ✨ No hardcoded secrets

---

**Created**: October 2025  
**Author**: WillowCMS Team  
**License**: MIT
