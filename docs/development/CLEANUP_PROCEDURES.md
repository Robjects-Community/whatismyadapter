# Docker Cleanup Procedures for WillowCMS

This document provides comprehensive cleanup instructions for the WillowCMS Docker environment, including different cleanup scenarios, backup procedures, and recovery options.

## 📋 Table of Contents

1. [Overview](#overview)
2. [Before You Start](#before-you-start)
3. [Backup Procedures](#backup-procedures)
4. [Soft Cleanup (Preserve Data)](#soft-cleanup-preserve-data)
5. [Hard Cleanup (Fresh Start)](#hard-cleanup-fresh-start)
6. [Nuclear Cleanup (Complete Reset)](#nuclear-cleanup-complete-reset)
7. [Recovery Procedures](#recovery-procedures)
8. [Troubleshooting](#troubleshooting)

## 🔍 Overview

The WillowCMS Docker environment consists of the following services and volumes:

**Services:**
- `willowcms` - Main CMS application (garzarobmdocker/willowcms:latest)
- `mysql` - MySQL 8.4.3 database server
- `phpmyadmin` - Database management interface
- `jenkins` - CI/CD server (garzarobmdocker/jenkins:latest)
- `mailpit` - Email testing server
- `redis` - Redis cache server
- `redis-commander` - Redis management interface

**Named Volumes:**
- `mysql_data` - MySQL database files
- `redis_data` - Redis persistence data
- `rabbitmq_data` - RabbitMQ data (defined but not used by any service)
- `jenkins_home` - Jenkins configuration and jobs
- `mailpit_data` - Mailpit message storage

**Networks:**
- `cms_default` - Bridge network for inter-service communication

## ⚠️ Before You Start

**IMPORTANT CONSIDERATIONS:**

1. **Stop all services before cleanup:**
   ```bash
   docker-compose down
   ```

2. **Check for running containers:**
   ```bash
   docker ps -a
   ```

3. **Verify volume contents before deletion:**
   ```bash
   docker volume ls
   docker volume inspect mysql_data redis_data jenkins_home mailpit_data
   ```

4. **Create backups of critical data** (see backup procedures below)

## 💾 Backup Procedures

### Database Backups Using manage.sh

The project includes a management script that provides database backup functionality:

```bash
# Run the interactive management tool
./manage.sh

# Select option 3: "Dump MySQL Database" from the Data Management menu
```

This will create a timestamped backup in `./project_mysql_backups/db_dump_YYYYMMDD_HHMMSS.sql`

### Manual Database Backup Commands

```bash
# Create backup directory
mkdir -p ./project_mysql_backups

# Backup database using docker-compose
docker-compose exec mysql mysqldump \
  --verbose \
  --routines \
  --triggers \
  --events \
  --no-tablespaces \
  --single-transaction \
  -uroot \
  -p"$MYSQL_ROOT_PASSWORD" \
  "$DB_DATABASE" > "./project_mysql_backups/manual_backup_$(date +%Y%m%d_%H%M%S).sql"
```

### Volume Backup Commands

**MySQL Data Volume:**
```bash
# Create backup of MySQL volume
docker run --rm -v mysql_data:/data -v $(pwd)/backups:/backup alpine \
  tar czf /backup/mysql_data_backup_$(date +%Y%m%d_%H%M%S).tar.gz -C /data .
```

**Jenkins Home Volume:**
```bash
# Create backup of Jenkins volume  
docker run --rm -v jenkins_home:/data -v $(pwd)/backups:/backup alpine \
  tar czf /backup/jenkins_home_backup_$(date +%Y%m%d_%H%M%S).tar.gz -C /data .
```

**Redis Data Volume:**
```bash
# Create backup of Redis volume
docker run --rm -v redis_data:/data -v $(pwd)/backups:/backup alpine \
  tar czf /backup/redis_data_backup_$(date +%Y%m%d_%H%M%S).tar.gz -C /data .
```

**Mailpit Data Volume:**
```bash
# Create backup of Mailpit volume
docker run --rm -v mailpit_data:/data -v $(pwd)/backups:/backup alpine \
  tar czf /backup/mailpit_data_backup_$(date +%Y%m%d_%H%M%S).tar.gz -C /data .
```

### Configuration File Preservation

**Critical files to backup:**
```bash
# Create configuration backup
mkdir -p ./config_backups/$(date +%Y%m%d_%H%M%S)
cp -r ./config/ ./config_backups/$(date +%Y%m%d_%H%M%S)/
cp docker-compose.yml ./config_backups/$(date +%Y%m%d_%H%M%S)/
cp -r ./docker/ ./config_backups/$(date +%Y%m%d_%H%M%S)/
```

## 🧹 Soft Cleanup (Preserve Data)

This cleanup removes temporary resources while preserving your data and configurations.

### Step-by-Step Soft Cleanup

```bash
# 1. Stop all services
docker-compose down

# 2. Remove stopped containers (forced)
docker-compose rm -f

# 3. Clean up unused networks
docker network prune -f

# 4. Remove dangling images (untagged images)
docker image prune -f

# 5. Remove unused volumes (be careful - this won't remove named volumes)
docker volume prune -f

# 6. Clean up build cache
docker builder prune -f

# 7. Restart services
docker-compose up -d
```

### Alternative One-Liner for Soft Cleanup

```bash
# Stop, remove containers, clean networks and dangling images
docker-compose down && docker-compose rm -f && docker network prune -f && docker image prune -f && docker builder prune -f
```

## 🔥 Hard Cleanup (Fresh Start)

This cleanup removes all containers, images, and data volumes for a complete fresh start.

### Step-by-Step Hard Cleanup

```bash
# 1. Stop and remove all containers
docker-compose down -v

# 2. Remove all named volumes (THIS WILL DELETE YOUR DATA!)
docker volume rm mysql_data redis_data rabbitmq_data jenkins_home mailpit_data

# 3. Remove custom images
docker rmi garzarobmdocker/willowcms:latest garzarobmdocker/jenkins:latest

# 4. Remove additional images used by the stack
docker rmi mysql:8.4.3 phpmyadmin redis:7-alpine axllent/mailpit:latest rediscommander/redis-commander:latest

# 5. Clean build cache
docker builder prune -f

# 6. Remove custom networks
docker network rm willow_cms_default 2>/dev/null || true

# 7. Rebuild and start services
docker-compose up -d --build
```

### Hard Cleanup Script

Create a script for repeated hard cleanup:

```bash
#!/bin/bash
# hard_cleanup.sh

set -e

echo "🔥 Starting Hard Cleanup - This will remove ALL data!"
read -p "Are you sure you want to continue? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cleanup cancelled."
    exit 1
fi

echo "Stopping all services..."
docker-compose down -v

echo "Removing named volumes..."
docker volume rm mysql_data redis_data rabbitmq_data jenkins_home mailpit_data 2>/dev/null || echo "Some volumes may not exist"

echo "Removing custom images..."
docker rmi garzarobmdocker/willowcms:latest garzarobmdocker/jenkins:latest 2>/dev/null || echo "Some images may not exist"

echo "Removing additional images..."
docker rmi mysql:8.4.3 phpmyadmin redis:7-alpine axllent/mailpit:latest rediscommander/redis-commander:latest 2>/dev/null || echo "Some images may not exist"

echo "Cleaning build cache..."
docker builder prune -f

echo "Removing custom networks..."
docker network rm willow_cms_default 2>/dev/null || true

echo "✅ Hard cleanup complete! Run 'docker-compose up -d --build' to restart with fresh data."
```

## ☢️ Nuclear Cleanup (Complete Reset)

This is the most aggressive cleanup that removes everything Docker-related on your system.

### System-wide Cleanup

```bash
# ⚠️ WARNING: This will remove ALL Docker data on your system!

# Stop all running containers
docker stop $(docker ps -aq) 2>/dev/null || true

# Remove all containers
docker rm $(docker ps -aq) 2>/dev/null || true

# Remove all images
docker rmi $(docker images -q) 2>/dev/null || true

# Remove all volumes
docker volume rm $(docker volume ls -q) 2>/dev/null || true

# Remove all networks (except default ones)
docker network rm $(docker network ls -q) 2>/dev/null || true

# System-wide cleanup with volumes
docker system prune -a --volumes -f

# Clean build cache
docker builder prune -a -f
```

### Nuclear Cleanup Using External Context

Based on the provided external context, you can also use:

```bash
# Remove all stopped containers, images, and volumes
docker system prune --all -f && docker volume prune -f
```

## 🔄 Recovery Procedures

### Restoring from Database Backup

**Using manage.sh:**
```bash
./manage.sh
# Select option 4: "Load Database from Backup" from Data Management menu
```

**Manual database restore:**
```bash
# Copy SQL file to MySQL container and restore
docker-compose cp ./project_mysql_backups/db_dump_YYYYMMDD_HHMMSS.sql mysql:/tmp/restore.sql
docker-compose exec mysql mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$DB_DATABASE" < /tmp/restore.sql
```

### Restoring Volume Backups

**MySQL Volume:**
```bash
docker volume create mysql_data
docker run --rm -v mysql_data:/data -v $(pwd)/backups:/backup alpine \
  tar xzf /backup/mysql_data_backup_YYYYMMDD_HHMMSS.tar.gz -C /data
```

**Jenkins Volume:**
```bash
docker volume create jenkins_home
docker run --rm -v jenkins_home:/data -v $(pwd)/backups:/backup alpine \
  tar xzf /backup/jenkins_home_backup_YYYYMMDD_HHMMSS.tar.gz -C /data
```

## 🔧 Troubleshooting

### Common Issues and Solutions

**1. Volume Mount Permissions:**
```bash
# Fix ownership issues
docker-compose exec willowcms chown -R www-data:www-data /var/www/html
```

**2. Database Connection Issues:**
```bash
# Restart just the database service
docker-compose restart mysql

# Check database logs
docker-compose logs mysql
```

**3. Build Cache Issues:**
```bash
# Force rebuild without cache
docker-compose build --no-cache
docker-compose up -d
```

**4. Network Conflicts:**
```bash
# Remove and recreate networks
docker-compose down
docker network prune -f
docker-compose up -d
```

### Verification Commands

**Check service status:**
```bash
docker-compose ps
```

**Check volume sizes:**
```bash
docker system df -v
```

**Check logs:**
```bash
docker-compose logs [service_name]
```

**Test database connection:**
```bash
docker-compose exec willowcms bin/cake database_test
```

## 📝 Cleanup Checklists

### Pre-Cleanup Checklist
- [ ] Services are stopped (`docker-compose down`)
- [ ] Database backup created
- [ ] Configuration files backed up
- [ ] Volume backups created (if needed)
- [ ] Team notified (if shared environment)

### Post-Cleanup Checklist
- [ ] Services start successfully (`docker-compose up -d`)
- [ ] Database connection works
- [ ] Application loads correctly
- [ ] All required data restored
- [ ] Configuration files in place
- [ ] Logs show no critical errors

## 🚀 Quick Reference

### Soft Cleanup One-Liner
```bash
docker-compose down && docker-compose rm -f && docker network prune -f && docker image prune -f && docker builder prune -f && docker-compose up -d
```

### Hard Cleanup One-Liner
```bash
docker-compose down -v && docker volume rm mysql_data redis_data rabbitmq_data jenkins_home mailpit_data && docker rmi garzarobmdocker/willowcms:latest garzarobmdocker/jenkins:latest && docker builder prune -f
```

### Emergency Recovery
```bash
# If something goes wrong, restore from backup:
./manage.sh  # Select option 4 to restore database
docker-compose up -d --build
```

---

**⚠️ Important Notes:**
- Always create backups before any cleanup operation
- Test cleanup procedures in a development environment first
- Keep this documentation updated with any changes to the Docker setup
- Consider automation for regular cleanup schedules

**📞 Support:**
If you encounter issues, check the troubleshooting section above or consult the project documentation.
