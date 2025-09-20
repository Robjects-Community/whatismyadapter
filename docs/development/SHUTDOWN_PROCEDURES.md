# WillowCMS Docker Environment Shutdown Procedures

This document provides detailed instructions for gracefully shutting down all services in the WillowCMS Docker environment.

## Table of Contents
1. [Quick Shutdown (Recommended)](#quick-shutdown-recommended)
2. [Service Dependencies Overview](#service-dependencies-overview)
3. [Graceful Shutdown Order](#graceful-shutdown-order)
4. [Individual Service Shutdown](#individual-service-shutdown)
5. [Verification Commands](#verification-commands)
6. [Data Persistence and Volume Management](#data-persistence-and-volume-management)
7. [Troubleshooting](#troubleshooting)

---

## Quick Shutdown (Recommended)

For most scenarios, use the standard Docker Compose command to stop all services:

```bash
# Navigate to the project directory
cd /Users/mikey/Docs/git-repo-loc/docker-hub/adaptercms-beta/willow

# Stop all services gracefully
docker-compose down
```

**What this does:**
- Stops all containers in the correct dependency order
- Removes containers and networks
- Preserves named volumes (data is kept safe)
- Takes 10 seconds per container to gracefully shutdown before forcing termination

---

## Service Dependencies Overview

The WillowCMS environment consists of the following services with these dependencies:

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   redis-commander│◄───┤    willowcms    │◄───┤     mysql       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                               │
                               ▼
                       ┌─────────────────┐
                       │     redis       │
                       └─────────────────┘

┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   phpmyadmin    │    │    jenkins      │    │    mailpit      │
│   (independent) │    │   (independent) │    │  (independent)  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

**Dependency Relationships:**
- **redis-commander** depends on **willowcms**
- **willowcms** depends on **mysql** and **redis**
- **phpmyadmin**, **jenkins**, and **mailpit** are independent services

---

## Graceful Shutdown Order

When shutting down manually, follow this order to respect dependencies:

### Step 1: Stop Dependent Services First
```bash
# Stop Redis Commander (depends on willowcms)
docker-compose stop redis-commander

# Stop independent services
docker-compose stop phpmyadmin jenkins mailpit
```

### Step 2: Stop Main Application
```bash
# Stop WillowCMS application
docker-compose stop willowcms
```

### Step 3: Stop Infrastructure Services
```bash
# Stop Redis and MySQL (core infrastructure)
docker-compose stop redis mysql
```

### Complete Manual Shutdown Command
```bash
# All services in correct order
docker-compose stop redis-commander phpmyadmin jenkins mailpit willowcms redis mysql
```

---

## Individual Service Shutdown

### Stop Specific Services

To stop individual services while keeping others running:

```bash
# Stop WillowCMS application only
docker-compose stop willowcms

# Stop MySQL database only
docker-compose stop mysql

# Stop Redis cache only
docker-compose stop redis

# Stop email testing interface
docker-compose stop mailpit

# Stop database management interface
docker-compose stop phpmyadmin

# Stop CI/CD server
docker-compose stop jenkins

# Stop Redis management interface
docker-compose stop redis-commander
```

### Restart Specific Services
```bash
# Restart a specific service
docker-compose restart willowcms

# Restart multiple services
docker-compose restart willowcms redis
```

---

## Verification Commands

### Check Container Status
```bash
# View all containers (running and stopped)
docker ps -a

# View only running containers
docker ps

# Check specific project containers only
docker-compose ps
```

### Detailed Status Information
```bash
# Show detailed container information
docker-compose ps -a

# Check logs for specific service during shutdown
docker-compose logs willowcms

# Check recent logs from all services
docker-compose logs --tail=20
```

### Network and Volume Status
```bash
# List Docker networks
docker network ls

# List Docker volumes
docker volume ls

# Check disk usage
docker system df
```

---

## Data Persistence and Volume Management

### ⚠️ Critical Data Warnings

**IMPORTANT:** The following volumes contain persistent data that will survive container restarts:

- **`mysql_data`** - Contains all MySQL databases and tables
- **`redis_data`** - Contains Redis cache and session data
- **`jenkins_home`** - Contains Jenkins configuration, jobs, and build history  
- **`mailpit_data`** - Contains stored email messages for testing

### Safe Shutdown (Preserves Data)
```bash
# Standard shutdown - keeps all data volumes
docker-compose down
```

### ⚠️ DESTRUCTIVE Operations (Use with Extreme Caution)

```bash
# DANGER: Remove containers AND all volumes (DELETES ALL DATA!)
docker-compose down -v

# DANGER: Remove everything including unused images
docker-compose down -v --rmi all

# DANGER: System-wide cleanup (affects other Docker projects too!)
docker system prune --all -f && docker volume prune -f
```

### Volume Backup Before Shutdown
```bash
# Backup MySQL data before shutdown
docker run --rm -v willowcms_mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql_backup_$(date +%Y%m%d_%H%M%S).tar.gz -C /data .

# Backup Jenkins data before shutdown
docker run --rm -v willowcms_jenkins_home:/data -v $(pwd):/backup alpine tar czf /backup/jenkins_backup_$(date +%Y%m%d_%H%M%S).tar.gz -C /data .
```

---

## Troubleshooting

### Containers Won't Stop Gracefully
```bash
# Force stop containers that won't respond
docker-compose kill

# Force stop specific service
docker-compose kill willowcms

# Nuclear option - force stop all containers system-wide
docker stop $(docker ps -q)
```

### Check for Hanging Processes
```bash
# Check what's preventing shutdown
docker-compose top

# Check system resources
docker stats

# Check container logs for errors
docker-compose logs --tail=50
```

### Clean Up After Failed Shutdown
```bash
# Remove stopped containers
docker-compose rm -f

# Remove unused networks
docker network prune -f

# Check for orphaned containers
docker ps -a --filter "status=exited"
```

### Port Conflicts After Restart
```bash
# Check what's using the ports
lsof -i :8080  # WillowCMS
lsof -i :3310  # MySQL
lsof -i :8082  # phpMyAdmin
lsof -i :8081  # Jenkins
lsof -i :6379  # Redis
lsof -i :8025  # Mailpit Web UI
lsof -i :1125  # Mailpit SMTP
lsof -i :8084  # Redis Commander
```

---

## Service-Specific Shutdown Notes

### WillowCMS (willowcms)
- **Graceful shutdown time:** 10 seconds
- **Dependencies:** Requires MySQL and Redis to function
- **Data:** Application files are mounted from host filesystem
- **Sessions:** Stored in Redis, will persist through Redis restarts

### MySQL (mysql)
- **Graceful shutdown time:** 30 seconds (may take longer with large datasets)
- **Data location:** `/var/lib/mysql` in `mysql_data` volume
- **Important:** Always allow full shutdown to prevent database corruption

### Redis (redis)
- **Graceful shutdown time:** 5 seconds
- **Data location:** `/data` in `redis_data` volume
- **Persistence:** Configured with AOF (Append Only File) for data durability

### Jenkins (jenkins)
- **Graceful shutdown time:** 30 seconds (may take longer during builds)
- **Important:** Active builds will be interrupted during shutdown
- **Data location:** `/var/jenkins_home` in `jenkins_home` volume

### Mailpit (mailpit)
- **Graceful shutdown time:** 5 seconds
- **Data location:** `/data` in `mailpit_data` volume
- **Note:** Stored emails will persist between restarts

### phpMyAdmin (phpmyadmin)
- **Graceful shutdown time:** 5 seconds
- **Dependencies:** None (can run independently)
- **Note:** Pure web interface, no persistent data

### Redis Commander (redis-commander)
- **Graceful shutdown time:** 5 seconds
- **Dependencies:** Requires WillowCMS service to be running
- **Note:** Pure web interface, no persistent data

---

## Emergency Procedures

### Complete Environment Reset
```bash
# 1. Stop everything
docker-compose down

# 2. Remove all containers and networks
docker-compose down --remove-orphans

# 3. If needed, remove volumes (DESTRUCTIVE!)
# docker-compose down -v

# 4. Clean up system (affects all Docker projects!)
# docker system prune -f
```

### Recovery After Crash
```bash
# Check for corrupted containers
docker ps -a --filter "status=exited"

# Remove corrupted containers
docker-compose rm -f

# Restart services
docker-compose up -d

# Check logs for issues
docker-compose logs --follow
```

---

## Best Practices

1. **Always use `docker-compose down`** for normal shutdowns
2. **Create backups** before major operations
3. **Check logs** if services don't start after shutdown
4. **Monitor disk space** - Docker can consume significant storage
5. **Use version control** for configuration changes
6. **Document any custom modifications** to this setup
7. **Test shutdown procedures** in development before using in production

---

*Last updated: $(date)*
*Environment: WillowCMS Docker Development Stack*
