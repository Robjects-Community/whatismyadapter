# WillowCMS Hardened Image - Portainer Stack Deployment

This document describes the hardened Docker image deployment of WillowCMS using Portainer stacks with enhanced security features.

## Overview

The hardened deployment uses:
- **Docker Hardened Images (DHI)**: `robjects/dhi-php-mirror-robjects:8.3-alpine3.22-dev`
- **Enhanced Security**: Non-root execution, minimal attack surface, no-new-privileges
- **CakePHP 5.x**: Latest framework with `app/` directory structure
- **Direct Process Management**: No supervisord dependency for better performance
- **Flexible Volumes**: Support for both Docker-managed and host-mounted storage

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    Portainer Stack: WillowCMS                  │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌───────────┐  ┌─────────────┐  ┌──────────┐ │
│  │  WillowCMS  │  │   MySQL   │  │    Redis    │  │ PHPMyAdmin│ │
│  │  (Hardened) │  │    8.0    │  │ 7.2-alpine  │  │  latest  │ │
│  │             │  │           │  │             │  │          │ │
│  │ Port: 8080  │  │Port: 3310 │  │ Port: 6379  │  │Port: 8082│ │
│  └─────────────┘  └───────────┘  └─────────────┘  └──────────┘ │
│                                                                 │
│  ┌─────────────┐  ┌─────────────────────────────────────────┐   │
│  │   Mailpit   │  │         Redis Commander               │   │
│  │   latest    │  │             latest                     │   │
│  │             │  │                                        │   │
│  │Port: 8025   │  │           Port: 8084                   │   │
│  └─────────────┘  └─────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

## Security Features

### 🔒 Docker Hardened Image (DHI)
- Uses curated base image: `robjects/dhi-php-mirror-robjects:8.3-alpine3.22-dev`
- Regularly updated with security patches
- Minimal attack surface with only required packages

### 🛡️ Security Configurations
- **Non-root execution**: Runs as `nobody` user (UID/GID configurable)
- **no-new-privileges**: Prevents privilege escalation
- **Alpine 3.22**: Latest Alpine Linux with security updates
- **Minimal dependencies**: Python3/pip removed after use

### 🔐 Secrets Management
- All sensitive data in environment variables
- No hardcoded passwords, API keys, or secrets
- Uses `.env` files for configuration isolation

## Files and Structure

```
/Volumes/1TB_DAVINCI/docker/willow/
├── portainer-stacks/
│   └── docker-compose.yml          # Portainer stack configuration
├── stack.env                       # Environment variables
├── tools/
│   ├── backup/
│   │   └── backup_portainer_stack.sh  # Backup script with checksums
│   └── compose/
│       └── validate.sh             # Compose validation script
└── docs/
    └── portainer-willowcms-hardened.md  # This documentation
```

## Environment Configuration

### Core Variables (stack.env)
```bash
# Application
APP_ENV=production
DEBUG=false
GIT_REF=main-clean

# Hardened Image
WILLOWCMS_IMAGE=willowcms:main-clean-hardened

# Security
SECURITY_SALT=your-32-character-random-string
WILLOW_ADMIN_PASSWORD=secure-admin-password

# Database
MYSQL_ROOT_PASSWORD=secure-root-password
MYSQL_PASSWORD=secure-user-password

# Redis
REDIS_PASSWORD=secure-redis-password
```

### Volume Configuration
The stack supports both Docker-managed volumes (default) and host-mounted volumes:

**Docker-managed volumes (default):**
```bash
# Uses Docker volume names - automatically managed
```

**Host-mounted volumes (optional):**
```bash
WILLOWCMS_CODE_PATH=/host/path/to/code:/var/www/html
WILLOWCMS_LOGS_PATH=/host/path/to/logs:/var/www/html/logs
WILLOWCMS_STORAGE_PATH=/host/path/to/storage:/var/www/html/tmp
```

## Deployment Methods

### Method 1: Git-based Deployment (Recommended)

1. **Portainer Configuration:**
   - Repository URL: `https://github.com/your-org/willow.git`
   - Reference/Branch: `main-clean`
   - Compose path: `portainer-stacks/docker-compose.yml`

2. **Environment Variables:**
   - Upload `stack.env` content to Portainer environment variables
   - Override sensitive values directly in Portainer UI

### Method 2: Local File Deployment

1. **Upload Files:**
   - Copy `portainer-stacks/docker-compose.yml` content to Portainer editor
   - Set environment variables manually in Portainer UI

## Backup and Recovery

### Creating Backups
```bash
# Create numbered backup with checksums
./tools/backup/backup_portainer_stack.sh
```

### Verifying Backups
```bash
cd /path/to/backup/directory
shasum -c SHA256SUMS
```

### Restoring from Backup
```bash
# Restore configuration files
cp backup/docker-compose.yml portainer-stacks/
cp backup/stack.env ./

# Redeploy in Portainer or locally
```

## Validation and Testing

### Validate Configuration
```bash
# Validate compose configuration
./tools/compose/validate.sh
```

### Health Checks
```bash
# HTTP connectivity
curl -fS http://localhost:8080/

# Database connectivity
docker compose --env-file stack.env exec willowcms mysql -h mysql -u willow_user -p

# CakePHP functionality
docker compose --env-file stack.env exec willowcms bin/cake --version
```

### Running Tests
```bash
# Targeted PHPUnit tests (user preference)
docker compose --env-file stack.env exec willowcms php vendor/bin/phpunit --filter Controller
docker compose --env-file stack.env exec willowcms php vendor/bin/phpunit --filter Model
docker compose --env-file stack.env exec willowcms php vendor/bin/phpunit --filter View
```

## Monitoring and Maintenance

### Log Management
```bash
# Check service logs
docker compose --env-file stack.env logs willowcms

# Validate log integrity (checksums)
docker compose --env-file stack.env exec willowcms bash -c 'find /var/www/html/logs -type f -print0 | xargs -0 shasum -a 256'
```

### Database Maintenance
```bash
# Run migrations
docker compose --env-file stack.env exec willowcms bin/cake migrations migrate

# Clear caches
docker compose --env-file stack.env exec willowcms bin/cake cache clear_all
```

## Troubleshooting

### Common Issues

1. **Build Context Issues:**
   ```bash
   # Ensure Portainer can access the Git repository
   # Verify branch name is exactly "main-clean"
   ```

2. **Environment Variable Issues:**
   ```bash
   # Validate configuration
   ./tools/compose/validate.sh
   ```

3. **Permission Issues:**
   ```bash
   # Check UID/GID configuration
   docker compose --env-file stack.env exec willowcms id
   ```

### Rollback Procedure

1. **Using Backup:**
   ```bash
   # Restore from latest backup
   cp tools/backup/stack/001_20241004_003654/docker-compose.yml portainer-stacks/
   cp tools/backup/stack/001_20241004_003654/stack.env ./
   ```

2. **In Portainer:**
   - Change Git reference to previous branch
   - Or revert to previous image tag
   - Redeploy with "recreate" option

## Performance Optimizations

### Resource Limits
```yaml
# Add to docker-compose.yml if needed
deploy:
  resources:
    limits:
      memory: 512M
      cpus: '0.5'
```

### Volume Performance
- Use host-mounted volumes for better I/O performance
- Consider SSD storage for database volumes
- Regular cleanup of log files

## Security Best Practices

1. **Regular Updates:**
   - Monitor DHI base image updates
   - Update Alpine packages regularly
   - Keep PHP and extensions current

2. **Environment Security:**
   - Rotate passwords regularly
   - Use strong, unique passwords
   - Monitor access logs

3. **Network Security:**
   - Use internal networks where possible
   - Implement firewall rules
   - Monitor exposed ports

## Support and Maintenance

### Helper Scripts
- `tools/backup/backup_portainer_stack.sh`: Create versioned backups
- `tools/compose/validate.sh`: Validate Docker Compose configuration

### Key Features Implemented
- ✅ Versioned backups with SHA-256 checksums
- ✅ Environment variable isolation (no hardcoded secrets)
- ✅ Flexible volume configuration
- ✅ Security hardening (no-new-privileges, non-root)
- ✅ CakePHP 5.x compatibility
- ✅ MySQL client availability
- ✅ Organized tool structure in `./tools/`

---

**Note:** This deployment follows user preferences for security, organization, and backup practices. All scripts are organized under `./tools/` and documentation is kept in `./docs/` to maintain a clean root directory.