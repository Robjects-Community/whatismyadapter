# 🐳 Docker Restart Guide for Willow CMS

> **Complete guide for safely restarting Docker services in the Willow CMS development environment**

This guide provides comprehensive procedures for restarting Docker services in the Willow CMS development environment, including proper shutdown sequences, cleanup operations, and troubleshooting steps.

---

## 📋 Table of Contents

1. [Overview and Prerequisites](#-overview-and-prerequisites)
2. [Complete Shutdown Procedures](#-complete-shutdown-procedures)
3. [Cleanup Operations](#-cleanup-operations)
4. [Environment Restart](#-environment-restart)
5. [Verification Steps](#-verification-steps)
6. [Troubleshooting Guide](#-troubleshooting-guide)
7. [Quick Reference Commands](#-quick-reference-commands)

---

## 🎯 Overview and Prerequisites

### What This Guide Covers

This guide covers the complete process of restarting the Willow CMS Docker environment, which includes:

- **willowcms**: Main application container (Nginx + PHP-FPM + Redis)
- **mysql**: Database server (MySQL 8.0+)
- **mailpit**: Email testing interface
- **phpmyadmin**: Database management interface
- **redis-commander**: Redis monitoring (optional)
- **jenkins**: CI/CD server (optional)

### Prerequisites

Before following this guide, ensure you have:

- ✅ Docker and Docker Compose installed
- ✅ Willow CMS project directory accessible
- ✅ Proper file permissions for Docker operations
- ✅ Development aliases installed (optional but recommended)
- ✅ Understanding of your current development state

### When to Use This Guide

Use this restart procedure when you encounter:

- 🔄 Service connectivity issues
- 💾 Database connection problems
- 🚫 Container performance degradation
- ⚙️ Configuration changes requiring restart
- 🧹 Need for environment cleanup
- 🐛 Persistent development environment issues

### Important Considerations

⚠️ **Before proceeding:**

- Save all uncommitted work
- Note any running background processes
- Consider backing up important data
- Inform team members if working in shared environment
- Check for any active queue jobs that shouldn't be interrupted

---

## 🛑 Complete Shutdown Procedures

### Step 1: Graceful Application Shutdown

First, stop any active processes gracefully:

```bash
# Stop queue workers (if running)
docker compose exec willowcms pkill -f "queue worker" || true

# Clear any active sessions (optional)
docker compose exec willowcms bin/cake clear cache
```

### Step 2: Service-Specific Shutdown

Stop services in the correct order to prevent data corruption:

```bash
# Stop application containers first
docker compose stop willowcms phpmyadmin mailpit

# Stop optional services
docker compose stop redis-commander jenkins || true

# Finally stop database (allows for proper MySQL shutdown)
docker compose stop mysql
```

### Step 3: Complete Environment Shutdown

```bash
# Stop all services (alternative method)
docker compose down

# Or use development alias (if installed)
docker_down
```

### Step 4: Verify Shutdown

Confirm all containers are stopped:

```bash
# Check running containers
docker ps

# Check all containers (including stopped)
docker ps -a | grep willow

# Verify no Willow-related processes
docker compose ps
```

**Expected output:** No running containers should be listed.

---

## 🧹 Cleanup Operations

### Basic Cleanup (Standard Restart)

For routine restarts, perform basic cleanup:

```bash
# Remove stopped containers
docker compose rm -f

# Clean up unused networks
docker network prune -f

# Remove dangling images (optional)
docker image prune -f
```

### Comprehensive Cleanup (Deep Clean)

For persistent issues or major updates:

```bash
# Remove all stopped containers
docker container prune -f

# Remove unused volumes (⚠️ WARNING: This removes non-persistent data)
docker volume prune -f

# Remove unused networks
docker network prune -f

# Remove unused images
docker image prune -a -f

# Remove build cache
docker builder prune -a -f
```

### Database Volume Cleanup (⚠️ DANGEROUS)

**Only if you need to reset the database completely:**

```bash
# ⚠️ WARNING: This will delete ALL database data
# Make sure you have backups!

# List volumes to identify database volume
docker volume ls | grep mysql

# Remove specific MySQL volume (adjust name as needed)
docker volume rm willow_mysql_data

# Or remove all project volumes
docker compose down -v
```

### Cache and Temporary File Cleanup

```bash
# Clear application caches
rm -rf tmp/cache/models/*
rm -rf tmp/cache/persistent/*
rm -rf logs/*

# Clear Composer cache (if needed)
docker run --rm -v "$(pwd)":/app composer clear-cache
```

---

## 🚀 Environment Restart

### Step 1: Standard Restart

For most situations, use the standard restart procedure:

```bash
# Pull latest images (if needed)
docker compose pull

# Start services
docker compose up -d

# Or use development alias
docker_up
```

### Step 2: Rebuild Restart (After Code Changes)

If you've made changes to Dockerfiles or need to rebuild:

```bash
# Rebuild and start
docker compose up -d --build

# Or rebuild specific service
docker compose build willowcms
docker compose up -d willowcms
```

### Step 3: Fresh Environment Start

For a completely fresh environment:

```bash
# Ensure everything is down
docker compose down -v

# Remove old images (optional)
docker compose pull

# Start fresh
docker compose up -d --build
```

### Step 4: Service-Specific Restart

Restart individual services if needed:

```bash
# Restart main application
docker compose restart willowcms

# Restart database
docker compose restart mysql

# Restart multiple services
docker compose restart willowcms mysql mailpit
```

### Step 5: Initialize Application (Fresh Start)

If starting completely fresh, initialize the application:

```bash
# Wait for services to be ready
sleep 30

# Run migrations
docker compose exec willowcms bin/cake migrations migrate

# Clear cache
docker compose exec willowcms bin/cake clear cache

# Install/update dependencies (if needed)
docker compose exec willowcms composer install

# Set permissions
docker compose exec willowcms chown -R www-data:www-data /var/www/html/logs
docker compose exec willowcms chown -R www-data:www-data /var/www/html/tmp
```

---

## ✅ Verification Steps

### Step 1: Service Health Check

Verify all services are running properly:

```bash
# Check container status
docker compose ps

# Check logs for errors
docker compose logs --tail=50

# Check specific service logs
docker compose logs willowcms
docker compose logs mysql
docker compose logs mailpit
```

### Step 2: Application Connectivity

Test application endpoints:

```bash
# Test main application (expect HTTP 200)
curl -I http://localhost:8080

# Test admin panel (expect HTTP 200 or redirect)
curl -I http://localhost:8080/admin

# Test phpMyAdmin (expect HTTP 200)
curl -I http://localhost:8082

# Test Mailpit (expect HTTP 200)
curl -I http://localhost:8025
```

### Step 3: Database Connectivity

Verify database connection:

```bash
# Test database connection from application
docker compose exec willowcms bin/cake console

# Or test direct MySQL connection
docker compose exec mysql mysql -u cms_user -p -e "SHOW DATABASES;"

# Check database from application
docker compose exec willowcms bin/cake shell -c "echo 'Database connection test'; exit;"
```

### Step 4: Application Functionality

Test key application features:

```bash
# Test cache functionality
docker compose exec willowcms bin/cake clear cache

# Test queue system (if applicable)
docker compose exec willowcms bin/cake queue worker --verbose --max-iterations=1

# Check file permissions
docker compose exec willowcms ls -la tmp/
docker compose exec willowcms ls -la logs/
```

### Step 5: Service URLs Verification

Verify all service URLs are accessible:

- **Main Application**: [http://localhost:8080](http://localhost:8080)
- **Admin Panel**: [http://localhost:8080/admin](http://localhost:8080/admin)
- **phpMyAdmin**: [http://localhost:8082](http://localhost:8082)
- **Mailpit**: [http://localhost:8025](http://localhost:8025)
- **Redis Commander**: [http://localhost:8084](http://localhost:8084) *(if enabled)*
- **Jenkins**: [http://localhost:8081](http://localhost:8081) *(if enabled)*

---

## 🔧 Troubleshooting Guide

### Common Issues and Solutions

#### Issue: Containers Won't Start

**Symptoms:**
- Containers exit immediately
- "Port already in use" errors
- "Network not found" errors

**Solutions:**
```bash
# Check for port conflicts
sudo netstat -tulpn | grep :8080
sudo netstat -tulpn | grep :3306

# Kill processes using required ports
sudo lsof -ti:8080 | xargs kill -9
sudo lsof -ti:3306 | xargs kill -9

# Clean up networks
docker network prune -f

# Restart with fresh network
docker compose down
docker compose up -d
```

#### Issue: Database Connection Failed

**Symptoms:**
- "SQLSTATE[HY000] [2002] Connection refused"
- Application can't connect to MySQL
- Database container keeps restarting

**Solutions:**
```bash
# Check MySQL container logs
docker compose logs mysql

# Verify MySQL is ready
docker compose exec mysql mysqladmin ping -h localhost

# Reset database container
docker compose stop mysql
docker compose rm -f mysql
docker compose up -d mysql

# Wait longer for MySQL to initialize
sleep 60

# Test connection
docker compose exec willowcms bin/cake console
```

#### Issue: Permission Denied Errors

**Symptoms:**
- "Permission denied" in logs
- Can't write to files
- Cache issues

**Solutions:**
```bash
# Fix file ownership
docker compose exec willowcms chown -R www-data:www-data /var/www/html/tmp
docker compose exec willowcms chown -R www-data:www-data /var/www/html/logs
docker compose exec willowcms chown -R www-data:www-data /var/www/html/webroot

# Fix permissions
docker compose exec willowcms chmod -R 755 /var/www/html/tmp
docker compose exec willowcms chmod -R 755 /var/www/html/logs
docker compose exec willowcms chmod -R 755 /var/www/html/webroot

# Restart application
docker compose restart willowcms
```

#### Issue: Out of Disk Space

**Symptoms:**
- "No space left on device"
- Containers failing to start
- Performance issues

**Solutions:**
```bash
# Check disk usage
df -h

# Clean Docker system
docker system prune -a -f --volumes

# Remove old containers
docker container prune -f

# Remove unused volumes
docker volume prune -f

# Check Docker space usage
docker system df
```

#### Issue: Application Performance Issues

**Symptoms:**
- Slow response times
- High memory usage
- Containers restarting frequently

**Solutions:**
```bash
# Check container resource usage
docker stats

# Restart containers with resource limits
docker compose down
docker compose up -d

# Clear application cache
docker compose exec willowcms bin/cake clear cache

# Check logs for memory issues
docker compose logs willowcms | grep -i memory
docker compose logs willowcms | grep -i fatal
```

### Advanced Troubleshooting

#### Network Issues

```bash
# Inspect Docker networks
docker network ls
docker network inspect willow_default

# Test network connectivity between containers
docker compose exec willowcms ping mysql
docker compose exec willowcms ping redis

# Recreate network
docker compose down
docker network rm willow_default || true
docker compose up -d
```

#### Volume Issues

```bash
# Inspect volumes
docker volume ls | grep willow
docker volume inspect willow_mysql_data

# Check volume mounts
docker compose exec willowcms mount | grep /var/www/html

# Fix volume permissions
docker compose exec willowcms chown -R www-data:www-data /var/www/html
```

#### Image Issues

```bash
# Check image status
docker images | grep willow

# Force rebuild images
docker compose build --no-cache willowcms

# Pull latest base images
docker compose pull
docker compose up -d --build
```

---

## ⚡ Quick Reference Commands

### Essential Commands

```bash
# Quick restart (most common)
docker compose restart

# Full restart with cleanup
docker compose down && docker compose up -d

# Rebuild and restart
docker compose down && docker compose up -d --build

# Check service status
docker compose ps

# View logs
docker compose logs -f willowcms
```

### Emergency Commands

```bash
# Force stop everything
docker compose kill

# Nuclear option (removes everything)
docker compose down -v --remove-orphans
docker system prune -a -f --volumes

# Quick health check
curl -I http://localhost:8080 && echo "Application OK"
```

### Debugging Commands

```bash
# Interactive shell in main container
docker compose exec willowcms bash

# Database access
docker compose exec mysql mysql -u cms_user -p

# Check container resources
docker stats --no-stream

# Follow logs in real-time
docker compose logs -f
```

### Development Aliases (if installed)

```bash
# Start environment
docker_up

# Stop environment
docker_down

# View logs
docker_logs

# Clean up
docker_prune

# Execute in container
willowcms_exec [command]

# Interactive shell
willowcms_shell
```

---

## 📝 Additional Notes

### Pre-Restart Checklist

- [ ] Save all uncommitted work
- [ ] Stop queue workers gracefully
- [ ] Note current application state
- [ ] Check for running background processes
- [ ] Backup important data (if needed)

### Post-Restart Checklist

- [ ] Verify all services are running
- [ ] Test application connectivity
- [ ] Check database connection
- [ ] Verify file permissions
- [ ] Test key functionality
- [ ] Start queue workers (if needed)

### Best Practices

1. **Regular Restarts**: Restart services regularly to prevent resource accumulation
2. **Graceful Shutdown**: Always stop services gracefully to prevent data corruption
3. **Monitor Resources**: Keep an eye on disk space and container resources
4. **Backup Strategy**: Maintain regular backups before major operations
5. **Log Monitoring**: Check logs regularly for early warning signs

### Getting Help

If you encounter issues not covered in this guide:

1. Check the [Willow CMS Developer Guide](CLAUDE.md) for additional information
2. Review Docker Compose logs: `docker compose logs`
3. Check container resource usage: `docker stats`
4. Consult the Docker documentation for advanced troubleshooting
5. Consider reaching out to the development team with specific error messages

---

<div align="center">
  <strong>🐳 Happy Docker management with Willow CMS!</strong>
</div>
