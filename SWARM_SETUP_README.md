# WillowCMS Docker Swarm Development Environment

This directory contains a Docker Swarm-based development environment setup for WillowCMS that works with the `./app/` directory structure.

## Files Overview

- **`swarm_setup_dev_env.sh`** - Main setup script for Docker Swarm development environment
- **`willow-swarm-stack.yml`** - Docker Swarm stack configuration file
- **`app/`** - WillowCMS application directory (baked into Docker image)
- **`verify-app-structure.sh`** - Verification script to check deployment status

## Key Differences from Original Setup

### Docker Compose vs Docker Swarm
- **Original**: Uses `docker-compose` for local development
- **Swarm**: Uses `docker stack deploy` for swarm-based deployment
- **App Deployment**: App files are baked into the Docker image instead of volume-mounted

### Directory Structure
```
./app/                          # WillowCMS application files (source)
├── bin/                        # CakePHP CLI tools
├── config/                     # Application configuration
├── src/                        # Application source code
├── webroot/                    # Web accessible files
└── ... (complete WillowCMS)

willow-swarm-stack.yml          # Swarm stack definition
swarm_setup_dev_env.sh          # Setup script (Swarm version)
```

## Quick Start

1. **Ensure Docker Swarm is running**:
   ```bash
   docker swarm init  # (done automatically by script)
   ```

2. **Run the setup script**:
   ```bash
   ./swarm_setup_dev_env.sh
   ```

3. **Access the application**:
   - **Main Site**: http://localhost:7770
   - **Admin Login**: http://localhost:7770/en/users/login
   - **Credentials**: `admin@test.com` / `password`

## Script Usage

### Basic Commands
```bash
# Normal startup (with interactive prompts)
./swarm_setup_dev_env.sh

# Start with Jenkins and i18n data
./swarm_setup_dev_env.sh -j -i

# Non-interactive rebuild
./swarm_setup_dev_env.sh --rebuild --no-interactive

# Just run migrations
./swarm_setup_dev_env.sh --migrate
```

### Operations Available
- **`--wipe` (`-w`)**: Remove entire stack and recreate from scratch
- **`--rebuild` (`-b`)**: Rebuild Docker image and redeploy stack  
- **`--restart` (`-r`)**: Remove and restart stack (keep image)
- **`--migrate` (`-m`)**: Run database migrations only
- **`--continue` (`-c`)**: Continue with normal startup (default)

### Options Available
- **`--jenkins` (`-j`)**: Include Jenkins service in deployment
- **`--i18n` (`-i`)**: Load internationalization data during setup
- **`--no-interactive` (`-n`)**: Skip interactive prompts (automation mode)
- **`--help` (`-h`)**: Show help message

## Services and Ports

| Service | Internal Port | External Port | Access URL | Credentials |
|---------|---------------|---------------|------------|-------------|
| WillowCMS | 80 | 7770 | http://localhost:7770 | admin@test.com / password |
| MySQL | 3306 | 7710 | - | root / password |
| PHPMyAdmin | 80 | 7771 | http://localhost:7771 | root / password |
| Jenkins | 8080 | 7772 | http://localhost:7772 | (if enabled) |
| Mailpit Web | 8025 | 7773 | http://localhost:7773 | - |
| Mailpit SMTP | 1025 | 7725 | - | - |
| Redis Commander | 8081 | 7774 | http://localhost:7774 | admin / password |
| Redis | 6379 | 7776 | - | root password |

## Architecture Overview

### Docker Swarm Stack
The setup creates a Docker Swarm stack named `willowcms-swarm-test` with the following services:

1. **willowcms** (2 replicas)
   - PHP 8.3 + Nginx + Redis
   - Application files baked into image
   - Health checks enabled
   - Auto-restart on failure

2. **mysql** (1 replica)
   - MySQL 8.4.3 with UTF8MB4 support
   - Persistent data volume
   - Health checks with mysqladmin ping

3. **phpmyadmin** (1 replica)
   - Web-based MySQL administration
   - Connected to MySQL service

4. **mailpit** (1 replica)
   - Email testing service
   - SMTP server + web interface

5. **redis** (1 replica)
   - Redis cache server
   - Password-protected

6. **redis-commander** (1 replica)
   - Redis web interface

7. **jenkins** (optional, 1 replica)
   - CI/CD service (when enabled with `-j`)

### Application Image Build Process

The script automatically builds a Docker image containing:

1. **Base**: PHP 8.3 FPM Alpine Linux
2. **Web Server**: Nginx configured for CakePHP
3. **Application**: Complete WillowCMS app from `./app/`
4. **Dependencies**: Composer packages installed
5. **Services**: Supervisord managing nginx + php-fpm + redis

### Data Persistence

- **MySQL Data**: Persistent via Docker volume `mysql_data`
- **Jenkins Data**: Persistent via Docker volume `jenkins_home` 
- **Mailpit Data**: Persistent via Docker volume `mailpit_data`
- **Redis Data**: Persistent via Docker volume `redis_data`
- **Application Files**: Baked into image (not volume mounted)

## Development Workflow

### First Time Setup
1. Clone repository with WillowCMS in `./app/` directory
2. Run `./swarm_setup_dev_env.sh`
3. Script will:
   - Initialize Docker Swarm (if needed)
   - Build WillowCMS image from `./app/`
   - Deploy all services
   - Run database migrations
   - Create admin user
   - Import default data

### Subsequent Development
```bash
# Normal startup (database preserved)
./swarm_setup_dev_env.sh

# After making code changes
./swarm_setup_dev_env.sh --rebuild

# Reset everything (fresh start)
./swarm_setup_dev_env.sh --wipe
```

### Code Changes Workflow
Since application files are baked into the Docker image:
1. Make changes to files in `./app/`
2. Run `./swarm_setup_dev_env.sh --rebuild` 
3. Script rebuilds image with latest code
4. Redeploys stack with new image

### Database Operations
```bash
# Just run migrations (without full rebuild)
./swarm_setup_dev_env.sh --migrate

# Access database directly
docker exec -it $(docker ps -q --filter "name=mysql") mysql -u root -ppassword cms
```

### Debugging and Logs
```bash
# View service logs
docker service logs willowcms-swarm-test_willowcms

# Access running container
docker exec -it $(docker ps -q --filter "label=com.docker.swarm.service.name=willowcms-swarm-test_willowcms" | head -1) bash

# Check stack status
docker stack services willowcms-swarm-test

# Run verification script
./verify-app-structure.sh
```

## Comparison with Original Setup

### Advantages of Swarm Setup
✅ **Production-like**: Uses Docker Swarm (production deployment pattern)  
✅ **High Availability**: Multiple replicas with auto-restart  
✅ **Immutable Deployments**: App baked into image (better for production)  
✅ **Scalability**: Can easily scale services up/down  
✅ **Health Checks**: Built-in service health monitoring  
✅ **Rolling Updates**: Zero-downtime deployments possible  

### Advantages of Compose Setup  
✅ **Development Speed**: Volume mounts for instant code changes  
✅ **File Editing**: Direct file editing without rebuilds  
✅ **Simpler**: Less complex than swarm for simple development  
✅ **Debug Friendly**: Easy to modify configurations  

### When to Use Each

| Use Case | Setup Type | Reason |
|----------|------------|--------|
| Local Development (frequent code changes) | Docker Compose | Volume mounts for instant changes |
| Testing Production Deployment | Docker Swarm | Production-like environment |
| CI/CD Pipeline | Docker Swarm | Immutable deployments |
| Team Development | Docker Swarm | Consistent environment |
| Performance Testing | Docker Swarm | Multiple replicas, realistic load |

## Troubleshooting

### Common Issues

1. **Script fails with "Docker Swarm not active"**
   - Run: `docker swarm init`
   - Or let script initialize it automatically

2. **Port conflicts**
   - Check if ports 7770-7776 are already in use
   - Stop conflicting services or modify `willow-swarm-stack.yml`

3. **App directory not found**
   - Ensure `./app/` directory exists with WillowCMS files
   - Check that `./app/composer.json` exists

4. **Database connection issues**
   - Wait for MySQL service to be ready (script handles this)
   - Check logs: `docker service logs willowcms-swarm-test_mysql`

5. **Image build fails**
   - Ensure Docker has enough resources (memory/disk)
   - Check `./app/` directory permissions
   - Verify Composer files are valid

### Cleanup Commands
```bash
# Remove entire stack
docker stack rm willowcms-swarm-test

# Remove volumes (data loss!)
docker volume prune

# Remove images
docker image rm willowcms:portainer

# Leave swarm mode
docker swarm leave --force
```

## Advanced Configuration

### Modifying Services
Edit `willow-swarm-stack.yml` to:
- Change resource limits
- Modify environment variables
- Add new services
- Adjust port mappings

### Custom Image Configuration
The script builds a custom Dockerfile with:
- Alpine Linux base
- PHP 8.3 + extensions
- Nginx web server
- Redis server
- Supervisord process manager

### Performance Tuning
- Adjust replica counts in stack file
- Modify resource limits/reservations
- Configure Nginx worker processes
- Tune PHP-FPM settings

## Monitoring and Maintenance

### Health Checks
All services include health checks:
- **WillowCMS**: HTTP request to localhost
- **MySQL**: mysqladmin ping
- **Redis**: redis-cli ping

### Backup Procedures
```bash
# Database backup
docker exec $(docker ps -q --filter "name=mysql") mysqldump -u root -ppassword cms > backup.sql

# Volume backup
docker run --rm -v mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql_backup.tar.gz /data
```

### Monitoring Commands
```bash
# Service status
docker stack services willowcms-swarm-test

# Resource usage
docker stats

# Service logs
docker service logs -f willowcms-swarm-test_willowcms

# Stack events
docker events --filter type=service
```

This swarm-based setup provides a production-ready development environment that closely mirrors how WillowCMS would be deployed in a real production environment.
