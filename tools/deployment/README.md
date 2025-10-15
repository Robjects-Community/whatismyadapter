# WillowCMS Deployment Guide

## Quick Start

The enhanced deployment script works from the repository root and uses rsync for efficient, incremental uploads.

### Prerequisites

✅ SSH key configured: `~/.ssh/willow_deploy_key`  
✅ Environment variables set in `tools/deployment/.env`  
✅ SSH access to droplet configured  
✅ rsync available (pre-installed on macOS)

### Basic Usage

```bash
# From repository root
cd /Volumes/1TB_DAVINCI/docker/willow

# Show what would be deployed (dry-run)
./tools/deployment/deploy-to-droplet-enhanced.sh --dry-run

# Deploy with confirmation prompt
./tools/deployment/deploy-to-droplet-enhanced.sh

# Deploy without confirmation
./tools/deployment/deploy-to-droplet-enhanced.sh --yes

# Deploy and remove remote files not present locally (careful!)
./tools/deployment/deploy-to-droplet-enhanced.sh --delete --yes
```

### Configuration

#### Environment Variables (`tools/deployment/.env`)

```bash
# Droplet connection
DROPLET_IP=your.droplet.ip
SSH_USER=deploy
SSH_PORT=22
SSH_KEY_PATH=~/.ssh/willow_deploy_key

# Remote deployment path
REMOTE_DST=/Volumes/1TB_DAVINCI/docker

# Application settings
APP_ENV=production
APP_DEBUG=false

# Database (production values)
DB_DATABASE=willow_prod
DB_USERNAME=willow_user
DB_PASSWORD=<secure_password>

# Security keys
APP_KEY=<generated_key>
JWT_SECRET=<generated_secret>
```

#### Excluded Files (`.deployignore`)

Created automatically at repository root. Excludes:
- VCS files (.git/, .github/)
- Development dependencies (node_modules/, vendor/)
- Build artifacts (coverage/, dist/)
- Local development files (docs/, scripts/, tests/)
- Runtime files (app/tmp/*, app/logs/*)
- Environment files (.env, stack.env)

## Features

### Smart Rsync Upload
- **Incremental**: Only changed files are transferred
- **Progress**: Real-time upload percentage and ETA
- **Resume**: Interrupted transfers can be resumed
- **Efficient**: Compression and delta transfer reduce bandwidth

### Dry Run Mode
```bash
./tools/deployment/deploy-to-droplet-enhanced.sh --dry-run
```
Shows:
- Files to create/update/delete
- Total transfer size
- Detailed change list
- No actual changes made

### Automatic Post-Deployment
After file sync, the script automatically:
1. Copies production Docker Compose configuration
2. Copies sanitized environment file
3. Sets correct file ownership (whatismyadapter:100 or 1034:100)
4. Sets proper permissions for CakePHP
5. Restarts Docker services
6. Installs Composer dependencies if needed
7. Clears application caches
8. Verifies deployment

### Deployment Logs
All deployments are logged to:
```
tools/deployment/logs/deploy-YYYYMMDD-HHMMSS.log
tools/deployment/logs/deploy-YYYYMMDD-HHMMSS.log.sha256
```

SHA-256 checksums ensure log integrity.

## Common Workflows

### Standard Deployment
```bash
# 1. Review changes
./tools/deployment/deploy-to-droplet-enhanced.sh --dry-run

# 2. Deploy
./tools/deployment/deploy-to-droplet-enhanced.sh
```

### Automated Deployment (CI/CD)
```bash
./tools/deployment/deploy-to-droplet-enhanced.sh --yes --force
```

### Emergency Sync
```bash
# Full sync with remote cleanup (removes extra files on server)
./tools/deployment/deploy-to-droplet-enhanced.sh --delete --yes
```

## Troubleshooting

### SSH Connection Failed

**Issue**: Cannot connect to droplet

**Solution**:
```bash
# Test SSH manually
ssh -p 22 -i ~/.ssh/willow_deploy_key deploy@<droplet-ip>

# If fails, ensure public key is installed on droplet
ssh-copy-id -i ~/.ssh/willow_deploy_key.pub -p 22 deploy@<droplet-ip>
```

### Permission Denied

**Issue**: Cannot write to remote directory

**Solution**: Ensure deploy user has sudo access and ownership:
```bash
ssh deploy@<droplet-ip>
sudo chown -R whatismyadapter:100 /Volumes/1TB_DAVINCI/docker
```

### Rsync Not Found

**Issue**: rsync command not available

**macOS**: Should be pre-installed
**Linux**: `sudo apt-get install rsync`

### Services Won't Start

**Issue**: Docker services fail after deployment

**Check logs**:
```bash
ssh deploy@<droplet-ip>
cd /Volumes/1TB_DAVINCI/docker
docker compose logs -f
```

**Common fixes**:
```bash
# Rebuild containers
docker compose down
docker compose up -d --build

# Check permissions
ls -la app/tmp app/logs
chmod -R 777 app/tmp app/logs
```

## Security Best Practices

1. **SSH Keys**: Never commit private keys to version control
2. **Environment Files**: Keep `.env` files out of git (use `.env.example` templates)
3. **Passwords**: Use strong, unique passwords for production
4. **Backups**: Always backup before deploying with `--delete`
5. **Verification**: Always run `--dry-run` first for critical deployments

## Remote Management

### View Logs
```bash
ssh deploy@<droplet-ip> 'cd /Volumes/1TB_DAVINCI/docker && docker compose logs -f'
```

### Check Status
```bash
ssh deploy@<droplet-ip> 'cd /Volumes/1TB_DAVINCI/docker && docker compose ps'
```

### Access Container
```bash
ssh deploy@<droplet-ip> 'cd /Volumes/1TB_DAVINCI/docker && docker compose exec willowcms bash'
```

### Restart Services
```bash
ssh deploy@<droplet-ip> 'cd /Volumes/1TB_DAVINCI/docker && docker compose restart'
```

## Advanced Options

### Custom Rsync Options

Edit the script to add custom rsync flags:
```bash
RSYNC_OPTS="-azP --stats --human-readable --bwlimit=5m"  # Limit to 5MB/s
```

### Selective Deployment

Temporarily edit `.deployignore` to include/exclude specific directories:
```bash
# Exclude infrastructure changes
infrastructure/
```

### Pre-Deployment Hooks

Add custom pre-deployment checks by modifying the script:
```bash
# Before sync, run tests
./scripts/run_tests.sh || exit 1
```

## Related Documentation

- [Docker Compose Configuration](../docs/development/DOCKER_COMPOSE_CUSTOMIZATION.md)
- [WillowCMS WARP Guide](../docs/WARP.md)
- [Project WARP Guide](../docs/project/WARP.md)

## Support

For issues or questions:
1. Check logs in `tools/deployment/logs/`
2. Review Docker logs on remote server
3. Verify SSH and network connectivity
4. Consult project documentation
