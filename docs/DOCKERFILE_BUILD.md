# WillowCMS Dockerfile - Build and Deployment Guide

## Overview

This document describes the production-ready, multi-stage Dockerfile created for WillowCMS, optimized for Docker Hub deployment with multi-platform support (AMD64 and ARM64).

## Table of Contents

- [Architecture](#architecture)
- [Features](#features)
- [Prerequisites](#prerequisites)
- [Building Locally](#building-locally)
- [Multi-Platform Builds](#multi-platform-builds)
- [Docker Hub Deployment](#docker-hub-deployment)
- [Environment Variables](#environment-variables)
- [Security Considerations](#security-considerations)
- [Troubleshooting](#troubleshooting)

---

## Architecture

The Dockerfile uses a **3-stage multi-stage build** process to optimize image size and build efficiency:

### Stage 1: Composer Binary
- **Base Image**: `composer:2`
- **Purpose**: Provides the official Composer binary
- **Output**: Composer executable for dependency management

### Stage 2: Composer Dependencies
- **Base Image**: `robjects/dhi-php-mirror-robjects:8.3-alpine3.22-dev`
- **Purpose**: Install and optimize PHP dependencies
- **Actions**:
  - Install minimal build tools (git, zip, unzip, bash)
  - Copy and install Composer dependencies with `--no-dev --optimize-autoloader`
  - Copy application code
- **Output**: Optimized vendor directory and application code

### Stage 3: Production Runtime
- **Base Image**: `robjects/dhi-php-mirror-robjects:8.3-alpine3.22-dev`
- **Purpose**: Final production-ready runtime environment
- **Components**:
  - Nginx web server
  - PHP 8.3-FPM
  - PHP extensions (43 extensions installed)
  - Redis client
  - MySQL client
  - ImageMagick
  - Security-hardened non-root user

---

## Features

### ✅ Multi-Platform Support
- **Platforms**: `linux/amd64`, `linux/arm64`
- **Build Arguments**: `TARGETPLATFORM`, `BUILDPLATFORM`, `UID`, `GID`
- Automatic platform detection for seamless deployment

### 🔒 Security Hardening
- **Non-root User**: Runs as `willowcms` user (UID: 1034, GID: 100)
- **No Hardcoded Secrets**: All sensitive data via environment variables
- **Minimal Attack Surface**: Only essential packages installed
- **Security Options**: `no-new-privileges:true` in docker-compose

### 🚀 Performance Optimization
- **Multi-stage Build**: Reduces final image size
- **Layer Caching**: Optimized layer ordering for faster rebuilds
- **Composer Optimization**: Production-only dependencies with autoloader optimization
- **OPcache**: PHP OPcache enabled for improved performance

### 📦 Complete PHP 8.3 Stack
- **Core Extensions**: ctype, curl, dom, fileinfo, gd, intl, mbstring, opcache, openssl, phar, session, tokenizer, xml, xmlreader, xmlwriter, simplexml
- **Database Extensions**: mysqli, pdo_mysql, pdo_sqlite
- **Additional Extensions**: bcmath, sockets, zip, pcntl
- **PECL Extensions**: imagick, msgpack, redis, xdebug (development)

### ⚙️ CakePHP 5.x Optimized
- All required extensions for CakePHP framework
- Proper configuration files for Nginx and PHP-FPM
- Directory structure optimized for CakePHP applications

### 🏥 Health Checks
- **Endpoint**: `http://localhost:80/fpm-ping`
- **Interval**: 30 seconds
- **Timeout**: 10 seconds
- **Retries**: 3
- **Start Period**: 40 seconds

---

## Prerequisites

### Required Tools
- Docker Desktop 20.10+ or Docker Engine 20.10+
- Docker Buildx (included in Docker Desktop)
- Docker Hub account (for pushing images)

### System Requirements
- **Local Development**: 4GB RAM minimum, 8GB recommended
- **Production**: 2GB RAM minimum, 4GB+ recommended
- **Disk Space**: 2GB for image layers and build cache

---

## Building Locally

### Quick Build (Current Platform Only)

Build for your current platform (ARM64 on Mac, AMD64 on Intel/AMD):

```bash
cd /Volumes/1TB_DAVINCI/docker/willow

# Build with default tag
docker build -t willowcms:latest .

# Build with custom registry
docker build -t garzarobmdocker/willowcms:latest .
```

### Build with Custom Arguments

Override default UID/GID for specific deployment environments:

```bash
docker build \
  --build-arg UID=1000 \
  --build-arg GID=1000 \
  -t willowcms:latest \
  .
```

### Testing the Local Build

Run the image locally to verify it works:

```bash
# Start the container
docker run -d \
  --name willowcms-test \
  -p 8080:80 \
  --env-file .env \
  garzarobmdocker/willowcms:latest

# Check logs
docker logs -f willowcms-test

# Test the application
curl http://localhost:8080

# Stop and remove
docker stop willowcms-test
docker rm willowcms-test
```

---

## Multi-Platform Builds

### Setup Docker Buildx

Create a multi-platform builder (one-time setup):

```bash
# Create and use a new builder instance
docker buildx create --name willowcms-multiplatform --driver docker-container --bootstrap

# Use the builder
docker buildx use willowcms-multiplatform

# Verify platform support
docker buildx inspect
```

### Build for Multiple Platforms

Build for both AMD64 and ARM64 architectures:

```bash
# Build for multiple platforms without pushing
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  -t garzarobmdocker/willowcms:latest \
  --load \
  .

# Note: --load works only with single platform
# For multi-platform, use --push or --output type=oci
```

### Build and Push to Docker Hub

Build for multiple platforms and push directly to Docker Hub:

```bash
# Login to Docker Hub
docker login

# Build and push
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  -t garzarobmdocker/willowcms:latest \
  --push \
  .
```

### Build with Multiple Tags

Create multiple tags (e.g., latest and versioned):

```bash
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  -t garzarobmdocker/willowcms:latest \
  -t garzarobmdocker/willowcms:1.0.0 \
  -t garzarobmdocker/willowcms:1.0 \
  --push \
  .
```

---

## Docker Hub Deployment

### Repository Setup

1. **Create Repository on Docker Hub**:
   - Go to https://hub.docker.com
   - Click "Create Repository"
   - Name: `willowcms`
   - Visibility: Public or Private
   - Click "Create"

2. **Login from CLI**:
   ```bash
   docker login
   # Enter your Docker Hub username and password
   ```

### Pushing Images

#### Option 1: Single Platform (Current Architecture)

```bash
# Build for current platform
docker build -t garzarobmdocker/willowcms:latest .

# Push to Docker Hub
docker push garzarobmdocker/willowcms:latest
```

#### Option 2: Multi-Platform (Recommended for Production)

```bash
# Build and push for both AMD64 and ARM64
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  -t garzarobmdocker/willowcms:latest \
  --push \
  .
```

### Verifying the Push

Check that the image is available on Docker Hub:

```bash
# Pull the image to verify
docker pull garzarobmdocker/willowcms:latest

# Inspect the manifest to see supported platforms
docker buildx imagetools inspect garzarobmdocker/willowcms:latest
```

---

## Environment Variables

### Required Environment Variables

The following variables **must** be set in your `.env` or `stack.env` file:

#### Application Configuration
```bash
APP_NAME=WillowCMS
DEBUG=false                              # Set to false in production
APP_ENCODING=UTF-8
APP_DEFAULT_LOCALE=en_US
APP_DEFAULT_TIMEZONE=America/Chicago
SECURITY_SALT=<64-character-random-string>
APP_FULL_BASE_URL=https://your-domain.com
```

#### Database Configuration
```bash
DB_HOST=mysql
DB_USERNAME=cms_user
DB_PASSWORD=<strong-password>
DB_DATABASE=cms
DB_PORT=3306

# Test Database (optional for production)
TEST_DB_HOST=mysql
TEST_DB_USERNAME=cms_user
TEST_DB_PASSWORD=<strong-password>
TEST_DB_DATABASE=cms_test
TEST_DB_PORT=3306
```

#### MySQL Root Configuration
```bash
MYSQL_ROOT_PASSWORD=<strong-root-password>
MYSQL_USER=cms_user
MYSQL_PASSWORD=<strong-password>
MYSQL_DATABASE=cms
```

#### Redis Configuration
```bash
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_USERNAME=root
REDIS_PASSWORD=<strong-redis-password>
REDIS_DATABASE=0
REDIS_URL=redis://${REDIS_USERNAME}:${REDIS_PASSWORD}@redis:6379/0
```

#### Email Configuration
```bash
EMAIL_HOST=mailpit                        # Or your SMTP server
EMAIL_PORT=1025                           # Or 587/465 for SMTP
EMAIL_TIMEOUT=30
EMAIL_USERNAME=                           # Leave empty for mailpit
EMAIL_PASSWORD=                           # Leave empty for mailpit
EMAIL_REPLY=hello@yourdomain.com
EMAIL_NOREPLY=noreply@yourdomain.com
```

#### Queue Configuration
```bash
QUEUE_DEFAULT_URL=redis://root:<password>@redis:6379/0
QUEUE_TEST_URL=redis://root:<password>@redis:6379/0
```

#### Admin Configuration
```bash
WILLOW_ADMIN_USERNAME=admin
WILLOW_ADMIN_PASSWORD=<strong-admin-password>
WILLOW_ADMIN_EMAIL=admin@yourdomain.com
```

### Optional Environment Variables

#### API Keys (if using external services)
```bash
OPENAI_API_KEY=<your-openai-key>
YOUTUBE_API_KEY=<your-youtube-key>
TRANSLATE_API_KEY=<your-translate-key>
```

#### Docker & UID/GID Configuration
```bash
DOCKER_UID=1034
DOCKER_GID=100
```

#### Production-specific
```bash
SSH_ENABLE=false
DEV_MODE=false
PRODUCTION_MODE=true
```

### Environment Variable Files

#### `.env` File (Local Development)
- Located in project root
- Used by `docker-compose.yml`
- **Never commit to version control**

#### `stack.env` File (Production/Portainer)
- Used by `docker-compose-portainer-deploy.yml`
- Contains production-specific values
- Deploy via Portainer or Docker Swarm
- **Never commit to version control**

#### `.env.example` File
- Template with placeholder values
- **Safe to commit to version control**
- Copy to `.env` and fill in actual values

---

## Security Considerations

### 🔐 Security Best Practices

#### 1. Non-Root User Execution
- Container runs as user `willowcms` (UID: 1034, GID: 100)
- No root privileges inside container
- Reduces attack surface significantly

#### 2. No Hardcoded Secrets
- All secrets via environment variables
- Secrets managed by `.env` or `stack.env`
- Never hardcode passwords in Dockerfile or docker-compose.yml

#### 3. Minimal Base Image
- Alpine Linux base (small attack surface)
- Only essential packages installed
- Regular security updates via base image updates

#### 4. File Permissions
- Proper ownership (willowcms:willowcms or UID:GID)
- Appropriate file permissions (755 for directories, 644 for files)
- Writable directories: `/var/www/html/tmp`, `/var/www/html/logs`, `/var/www/html/webroot/files`

#### 5. Network Security
- Use `security_opt: - no-new-privileges:true` in docker-compose
- Configure firewall rules for exposed ports
- Use TLS/SSL for public-facing deployments
- Consider using Docker secrets for sensitive data

#### 6. Redis Security
- Always set `REDIS_PASSWORD`
- Bind to internal network only (127.0.0.1)
- Use strong passwords (16+ characters)
- Enable Redis authentication

### 🛡️ Production Hardening Checklist

- [ ] Set `DEBUG=false` in production
- [ ] Use strong, unique passwords (16+ characters)
- [ ] Generate a unique 64-character `SECURITY_SALT`
- [ ] Enable HTTPS/TLS with valid certificates
- [ ] Configure firewall rules (only expose necessary ports)
- [ ] Disable `SSH_ENABLE` in production
- [ ] Set `DEV_MODE=false` and `PRODUCTION_MODE=true`
- [ ] Enable rate limiting on Nginx
- [ ] Implement regular backups
- [ ] Monitor logs for suspicious activity
- [ ] Keep base images updated
- [ ] Use Docker secrets or vault for sensitive data
- [ ] Regularly rotate passwords and API keys

---

## Troubleshooting

### Common Issues

#### Issue: Build fails with "GID in use" error

**Symptom**:
```
addgroup: gid '100' in use
```

**Solution**:
The Dockerfile automatically handles this by detecting existing GIDs and using the appropriate group. If you encounter this, the latest Dockerfile version includes the fix. Update your Dockerfile from the repository.

#### Issue: Permission denied errors in container

**Symptom**:
```
Permission denied: /var/www/html/tmp
```

**Solution**:
1. Check UID/GID in your `.env` file matches the user running Docker
2. Verify volume permissions on host system
3. Rebuild image with correct `--build-arg UID=<your-uid> --build-arg GID=<your-gid>`

#### Issue: Multi-platform build fails

**Symptom**:
```
ERROR: multiple platforms feature is currently not supported for docker driver
```

**Solution**:
```bash
# Create a new buildx builder
docker buildx create --name multiplatform --use
docker buildx inspect --bootstrap

# Try the build again
docker buildx build --platform linux/amd64,linux/arm64 -t yourimage:latest --push .
```

#### Issue: Push access denied

**Symptom**:
```
ERROR: push access denied, repository does not exist or may require authorization
```

**Solution**:
1. Login to Docker Hub: `docker login`
2. Verify repository exists on Docker Hub
3. Check you have push access to the repository
4. Ensure image name matches Docker Hub username: `<username>/<repo>:tag`

#### Issue: Composer fails to install dependencies

**Symptom**:
```
Could not find a matching version of package
```

**Solution**:
1. Check `composer.json` is valid
2. Verify `composer.lock` is up to date
3. Try running `composer update` locally first
4. Check network connectivity during build

#### Issue: Health check fails

**Symptom**:
```
Health check failed: curl --silent --fail http://localhost:80/fpm-ping
```

**Solution**:
1. Check Nginx and PHP-FPM are starting correctly: `docker logs <container-name>`
2. Verify Nginx configuration files are copied correctly
3. Ensure PHP-FPM is listening on correct socket
4. Increase health check `start_period` if services need more time to start

### Debugging Tips

#### View Build Logs
```bash
# Build with detailed output
docker build --progress=plain -t willowcms:latest .

# View buildx build logs
docker buildx build --progress=plain --platform linux/amd64,linux/arm64 -t willowcms:latest .
```

#### Inspect Running Container
```bash
# Enter the container shell (run as willowcms user)
docker exec -it <container-name> /bin/sh

# Enter as root for debugging
docker exec -it -u root <container-name> /bin/sh

# Check PHP-FPM status
docker exec -it <container-name> ps aux | grep php-fpm

# Check Nginx status
docker exec -it <container-name> ps aux | grep nginx
```

#### Check Logs
```bash
# Container logs
docker logs -f <container-name>

# Specific log files
docker exec -it <container-name> tail -f /var/www/html/logs/error.log
docker exec -it <container-name> tail -f /var/log/nginx/error.log
```

#### Verify Permissions
```bash
# Check user and group
docker exec -it <container-name> id

# Check file ownership
docker exec -it <container-name> ls -la /var/www/html/
```

### Getting Help

If you encounter issues not covered here:

1. **Check Container Logs**: `docker logs <container-name>`
2. **Review Health Check**: `docker inspect <container-name> | grep -A 10 Health`
3. **Inspect Image**: `docker image inspect willowcms:latest`
4. **GitHub Issues**: Report issues at https://github.com/matthewdeaves/willow/issues
5. **Docker Docs**: https://docs.docker.com/

---

## Additional Resources

- [Docker Multi-Platform Builds](https://docs.docker.com/build/building/multi-platform/)
- [Docker Security Best Practices](https://docs.docker.com/develop/security-best-practices/)
- [CakePHP 5.x Documentation](https://book.cakephp.org/5/en/index.html)
- [Alpine Linux Packages](https://pkgs.alpinelinux.org/packages)
- [Nginx Configuration](https://nginx.org/en/docs/)
- [PHP-FPM Configuration](https://www.php.net/manual/en/install.fpm.php)

---

## Change Log

### Version 1.0.0 (October 2025)
- Initial release
- Multi-stage build with 3 stages
- Multi-platform support (AMD64/ARM64)
- PHP 8.3 with 43 extensions
- Security hardening with non-root user
- CakePHP 5.x optimization
- Health checks configured
- Comprehensive documentation

---

## License

This documentation is part of the WillowCMS project.
See the [LICENSE](../LICENSE) file in the project root for details.
