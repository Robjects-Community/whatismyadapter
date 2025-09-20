# Willow CMS Automation Scripts

This directory contains helper scripts for automating common development and deployment tasks for the Willow CMS Docker environment.

## Available Scripts

### 🔄 restart-environment.sh
**Automated shutdown, cleanup, and restart with verification**

Provides automated restart functionality with different reset levels and comprehensive verification.

```bash
# Standard restart with verification
./scripts/restart-environment.sh

# Soft reset (preserves data, clears containers)
./scripts/restart-environment.sh --soft

# Hard reset (destroys all data)
./scripts/restart-environment.sh --hard --force

# Quick restart without health checks
./scripts/restart-environment.sh --no-verify
```

**Features:**
- ✅ Soft/hard reset options
- ✅ Automatic service verification after restart
- ✅ Port connectivity testing
- ✅ HTTP endpoint validation
- ✅ Colored logging with timestamps
- ✅ Error handling and cleanup
- ✅ User confirmations for destructive operations

### 🏥 health-check.sh
**Comprehensive health check with status reporting and CI/CD integration**

Performs detailed health checks on all services with multiple output formats for both human operators and automated systems.

```bash
# Check all services with table output
./scripts/health-check.sh

# JSON output for CI/CD integration
./scripts/health-check.sh --format=json

# Check specific service only
./scripts/health-check.sh --service=willowcms

# Quick check with verbose logging
./scripts/health-check.sh --timeout=10 --verbose
```

**Features:**
- ✅ Multi-format output (table, json, simple)
- ✅ Individual service checking
- ✅ Response time measurements
- ✅ Resource usage monitoring
- ✅ CI/CD compatible exit codes
- ✅ Database and Redis connectivity tests
- ✅ HTTP endpoint verification

**Exit Codes:**
- `0` - All services healthy
- `1` - Some services unhealthy  
- `2` - Critical services down
- `3` - Cannot connect to Docker
- `4` - Invalid arguments

### 💾 backup-and-reset.sh
**Automated backup, full environment reset, and restore capability**

Comprehensive backup and restore system for data protection and environment management.

```bash
# Create backup with timestamp
./scripts/backup-and-reset.sh --backup

# Restore from specific backup
./scripts/backup-and-reset.sh --restore --restore-from=./backups/backup_2024-01-15_14-30-25

# Backup current state, then reset environment
./scripts/backup-and-reset.sh --backup-reset --force

# Full reset without backup (DESTRUCTIVE!)
./scripts/backup-and-reset.sh --reset --force
```

**Features:**
- ✅ Complete data backup (database, Redis, uploads, configs)
- ✅ Docker volume backup and restore
- ✅ Selective backup components (--no-volumes, --no-env, --no-uploads)
- ✅ Backup verification with checksums
- ✅ Automated restore process
- ✅ Backup manifest and summary generation
- ✅ List available backups

**Backup Components:**
- MySQL database dumps
- Redis data exports
- User uploaded files
- Configuration files (.env, docker-compose.yml)
- Docker volumes (optional)
- Application logs and cache

## Common Usage Examples

### Daily Development Workflow
```bash
# Morning startup
./scripts/health-check.sh --format=table

# If issues found, restart environment
./scripts/restart-environment.sh --soft

# Before major changes, create backup
./scripts/backup-and-reset.sh --backup
```

### CI/CD Integration
```bash
# Health check in pipeline
./scripts/health-check.sh --format=json --timeout=30
EXIT_CODE=$?

if [ $EXIT_CODE -ne 0 ]; then
  echo "Services unhealthy, attempting restart..."
  ./scripts/restart-environment.sh --force --no-verify
fi
```

### Disaster Recovery
```bash
# Create emergency backup
./scripts/backup-and-reset.sh --backup --backup-dir=/external/backups

# Full environment reset and restore
./scripts/backup-and-reset.sh --reset --force
./scripts/backup-and-reset.sh --restore --restore-from=/external/backups/backup_2024-01-15_14-30-25
```

### Development Environment Reset
```bash
# Clean reset for testing
./scripts/backup-and-reset.sh --backup-reset --no-volumes --force

# Quick restart after reset
./scripts/restart-environment.sh --force
```

## Script Locations and Logs

### File Structure
```
scripts/
├── restart-environment.sh    # Environment restart automation
├── health-check.sh          # Service health monitoring  
├── backup-and-reset.sh      # Backup and recovery operations
└── README.md               # This documentation

logs/                       # Created automatically
├── restart-environment.log
├── health-check.log  
└── backup-and-reset.log

backups/                    # Created automatically
└── backup_YYYY-MM-DD_HH-MM-SS/
    ├── database.sql
    ├── redis.rdb
    ├── uploads/
    ├── config/
    ├── volumes/
    ├── BACKUP_MANIFEST
    └── BACKUP_SUMMARY
```

### Log Files
All scripts generate detailed logs in the `logs/` directory:
- Timestamps for all operations
- Color-coded console output
- Persistent file logging
- Error tracking and debugging information

## Environment Requirements

### Prerequisites
- Docker and Docker Compose
- Bash shell (macOS/Linux)
- Standard Unix utilities (curl, nc, tar, gzip)
- Write permissions in project directory

### Supported Services
- **willowcms** - Main application container
- **mysql** - Database server
- **redis** - Cache and session storage
- **phpmyadmin** - Database management UI
- **mailpit** - Email testing server
- **redis-commander** - Redis management UI
- **jenkins** - CI/CD server (optional)

## Security Considerations

### Backup Security
- Backups contain sensitive data including database dumps
- Environment files contain API keys and passwords
- Store backups in secure, encrypted locations
- Regularly rotate backup encryption keys
- Implement backup retention policies

### Script Permissions
```bash
# Set appropriate permissions
chmod 750 scripts/*.sh          # Owner: rwx, Group: r-x, Other: ---
chown -R $USER:docker scripts/   # Ensure proper ownership
```

### Production Usage
- Always test scripts in development first
- Use `--force` flag carefully in production
- Implement proper backup verification
- Monitor log files for security issues
- Use separate backup storage for production

## Troubleshooting

### Common Issues

**Permission Denied:**
```bash
chmod +x scripts/*.sh
```

**Docker Not Running:**
```bash
# Check Docker status
docker info

# Start Docker if needed (macOS)
open -a Docker
```

**Backup Restoration Fails:**
```bash
# Check backup integrity
./scripts/backup-and-reset.sh --verify --restore-from=./backups/backup_2024-01-15_14-30-25

# Check log files
tail -f logs/backup-and-reset.log
```

**Services Won't Start:**
```bash
# Check specific service logs
docker-compose logs willowcms
docker-compose logs mysql

# Try hard reset
./scripts/restart-environment.sh --hard --force
```

### Debug Mode
Enable verbose logging for detailed troubleshooting:
```bash
./scripts/health-check.sh --verbose
./scripts/restart-environment.sh --no-verify  # Skip verification for speed
```

## Integration with Existing Tools

### Docker Compose Integration
These scripts work alongside existing Docker Compose workflows:
```bash
# Traditional approach
docker-compose down
docker-compose up -d

# Enhanced approach with verification
./scripts/restart-environment.sh --soft
```

### CakePHP Development
Compatible with existing CakePHP development tools referenced in the user's rules:
- Works with existing docker-compose.yml configuration
- Respects CakePHP 5.x directory structure
- Integrates with MySQL database setup
- Supports development workflow with queue workers

### CI/CD Pipeline Integration
```yaml
# GitHub Actions example
- name: Health Check
  run: ./scripts/health-check.sh --format=json --timeout=60
  
- name: Backup Before Deploy
  run: ./scripts/backup-and-reset.sh --backup --backup-dir=/deploy/backups
  
- name: Deploy and Verify
  run: |
    ./scripts/restart-environment.sh --force
    ./scripts/health-check.sh --timeout=120
```

## Support and Maintenance

### Script Updates
Scripts are versioned and can be updated independently:
- Check version: `grep "version=" scripts/backup-and-reset.sh`
- Update safely by backing up current versions
- Test updates in development environment first

### Monitoring
Regular maintenance tasks:
- Review log files weekly
- Clean old backups monthly  
- Verify backup integrity quarterly
- Update script documentation as needed

### Contributing
When modifying scripts:
1. Test all changes thoroughly
2. Update help text and documentation
3. Maintain backward compatibility
4. Follow existing error handling patterns
5. Update version numbers appropriately

## References
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Willow CMS Developer Guide](../DEVELOPER_GUIDE.md)
- [CakePHP 5.x Documentation](https://book.cakephp.org/5/en/index.html)
- [MySQL Backup Best Practices](https://dev.mysql.com/doc/refman/8.0/en/backup-and-recovery.html)
