# Redis Hardening & Operational Guide

This document explains the Redis hardening implemented in WillowCMS and provides operational procedures for maintaining Redis reliability across version upgrades.

## Overview

Redis is critical to WillowCMS performance, providing caching, session storage, and queue management. However, Redis database files can become incompatible between versions, causing startup failures and data loss. Our hardening approach automatically quarantines incompatible files while maintaining seamless operation.

## Architecture

### Components

1. **Bootguard Script** (`tools/redis/bootguard.sh`)
   - Integrity checks for RDB and AOF files before Redis starts
   - Automatic quarantine of corrupted/incompatible files
   - Failsafe startup with clean state if needed

2. **Custom Redis Container** (`docker/redis/Dockerfile`)
   - Built from official Redis image with version pinning
   - Includes bootguard and secure configuration
   - Zero-downtime startup resilience

3. **Environment-Driven Configuration**
   - All secrets and settings via `.env` variables
   - Support for both Docker volumes and host mounts
   - Version pinning and healthcheck customization

## Why Redis Can Fail on Version Mismatch

When upgrading Redis versions:
- **RDB format changes**: Newer Redis may not read older persistence files
- **AOF format evolution**: Append-only file structure can be incompatible
- **Configuration breaking changes**: Command syntax or default behavior changes
- **Memory structure differences**: Internal data representation changes

**Example error that bootguard prevents:**
```
Fatal error loading the DB, check server logs. Exiting.
Can't handle RDB format version 12
```

## Bootguard Protection Mechanism

### Startup Flow

1. **Environment Setup**
   ```
   DATA_DIR="${REDIS_DATA_DIR:-/data}"
   mkdir -p "$DATA_DIR/corrupted"
   ```

2. **RDB Integrity Check**
   ```bash
   if [ -f "$DATA_DIR/dump.rdb" ]; then
     if ! redis-check-rdb "$DATA_DIR/dump.rdb"; then
       # Quarantine with timestamp: dump.rdb.20241002-143052
       mv "$DATA_DIR/dump.rdb" "$DATA_DIR/corrupted/dump.rdb.$(date +%Y%m%d-%H%M%S)"
     fi
   fi
   ```

3. **AOF Integrity Check** (if enabled)
   ```bash
   if [ -f "$DATA_DIR/appendonly.aof" ]; then
     if ! redis-check-aof --fix "$DATA_DIR/appendonly.aof"; then
       # Quarantine with timestamp
       mv "$DATA_DIR/appendonly.aof" "$DATA_DIR/corrupted/appendonly.aof.$(date +%Y%m%d-%H%M%S)"
     fi
   fi
   ```

4. **Clean Redis Startup**
   - Starts with verified data or clean slate
   - All configuration from environment variables
   - Full logging of quarantine actions

## Environment Configuration

### Core Variables (.env)

```bash
# Version Management
REDIS_TAG=7.2-alpine                          # Pin specific version
REDIS_PASSWORD=secure_redis_2024              # Authentication password

# Persistence Settings
REDIS_APPENDONLY=yes                          # Enable AOF for durability
REDIS_SAVE="900 1 300 10 60 10000"           # RDB save intervals

# Volume Management
REDIS_DATA_VOLUME=redis-data:/data            # Named volume
# REDIS_DATA_VOLUME=/host/path/redis:/data    # Host mount alternative

# Health Monitoring
REDIS_HEALTHCHECK_INTERVAL=10s                # Health check frequency
REDIS_HEALTHCHECK_TIMEOUT=3s                  # Health check timeout
REDIS_HEALTHCHECK_RETRIES=5                   # Max retries before unhealthy
```

### Switching Volume Types

**Docker Managed Volume (Default):**
```bash
REDIS_DATA_VOLUME=redis-data:/data
```

**Host Mount (for easier access/backup):**
```bash
REDIS_DATA_VOLUME=/absolute/host/path/redis:/data
```

## Operational Procedures

### Safe Redis Upgrades

1. **Pre-upgrade Backup**
   ```bash
   # Backup current Redis data
   docker run --rm -v redis-data:/data -v $PWD/backups:/backups \
     alpine tar czf /backups/redis_pre_upgrade_$(date +%Y%m%d-%H%M%S).tgz /data
   
   # Generate checksum
   cd backups && sha256sum redis_pre_upgrade_*.tgz > redis_pre_upgrade.sha256
   ```

2. **Update Version**
   ```bash
   # Edit .env file
   REDIS_TAG=7.4-alpine  # New version
   
   # Rebuild with new version
   docker compose build redis
   ```

3. **Deploy with Protection**
   ```bash
   # Start with bootguard protection
   docker compose up -d redis
   
   # Monitor logs for quarantine actions
   docker compose logs -f redis
   ```

4. **Verify Operation**
   ```bash
   # Test connectivity
   docker compose exec redis redis-cli -a "$REDIS_PASSWORD" ping
   
   # Check quarantine directory for any moved files
   docker compose exec redis ls -la /data/corrupted/
   ```

### Troubleshooting

**Check quarantined files:**
```bash
docker compose exec redis ls -la /data/corrupted/
```

**View bootguard logs:**
```bash
docker compose logs redis | grep redis-guard
```

**Manual integrity check:**
```bash
docker compose exec redis redis-check-rdb /data/dump.rdb
docker compose exec redis redis-check-aof /data/appendonly.aof
```

**Recovery from backup:**
```bash
# Stop Redis
docker compose down redis

# Restore from backup
docker run --rm -v redis-data:/data -v $PWD/backups:/backups \
  alpine tar xzf /backups/redis_backup_TIMESTAMP.tgz -C /

# Verify checksum
cd backups && sha256sum -c redis_backup_TIMESTAMP.sha256

# Restart Redis
docker compose up -d redis
```

## Backup Integration

The backup system integrates with existing `tools/backup/backup.sh` with:

- **Numbered backups** for easy identification
- **Checksum verification** for integrity
- **Metadata preservation** for restore selection
- **Volume-aware backup** (named vs host mount detection)

## Monitoring & Alerting

### Health Indicators

- **Container Health**: `docker compose ps` shows health status
- **Redis Connectivity**: Healthcheck pings Redis with authentication
- **Startup Logs**: Bootguard logs all quarantine actions
- **Performance**: Standard Redis INFO commands available

### Critical Alerts

Monitor for:
- Files moved to `/data/corrupted/` (incompatible data detected)
- Health checks failing after upgrade
- Persistent connection errors from application

## Security Features

- **No hardcoded secrets** in configuration files
- **Password authentication** required for all connections
- **Protected mode** enabled to prevent unauthorized access
- **Environment isolation** with Docker networking
- **Secure defaults** with minimal attack surface

## Performance Tuning

Current settings optimized for WillowCMS:
- **Memory policy**: `allkeys-lru` for cache eviction
- **Persistence**: Both RDB and AOF for durability + performance
- **Save intervals**: Balanced for data safety and performance
- **Connection timeout**: Optimized for web application usage

## Development vs Production

**Development (.env)**:
```bash
REDIS_PASSWORD=secure_redis_2024
REDIS_DATA_VOLUME=redis-data:/data
```

**Production (.env)**:
```bash
REDIS_PASSWORD=production_secure_password_2024
REDIS_DATA_VOLUME=/production/redis/data:/data
REDIS_HEALTHCHECK_INTERVAL=30s
```

## Maintenance Checklist

**Monthly:**
- [ ] Review quarantine directory for patterns
- [ ] Verify backup integrity
- [ ] Check Redis memory usage and performance
- [ ] Update REDIS_TAG if security updates available

**Before Major Updates:**
- [ ] Full backup with checksum verification
- [ ] Test upgrade in staging environment
- [ ] Document any custom configuration changes
- [ ] Plan rollback procedure

This hardening ensures Redis reliability across version changes while maintaining the flexibility and security required for production WillowCMS deployments.