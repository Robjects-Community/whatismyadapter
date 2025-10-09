# Legacy Digital Ocean Droplet Deployment Scripts

⚠️ **DEPRECATED - DO NOT USE WITH NEW PORTAINER IMPLEMENTATION**

This directory contains legacy deployment scripts that were used for deploying WillowCMS to Digital Ocean droplets. These scripts are **deprecated** and should not be used with the new Portainer GitHub URL implementation branch.

## Scripts Overview

### Deployment Scripts
- **`deploy-final.sh`** - Final version of the Digital Ocean deployment script with full progress tracking
- **`deploy-fixed.sh`** - Fixed version addressing APP_PORT configuration issues
- **`deploy-streamlined.sh`** - Streamlined deployment with reduced complexity
- **`deploy-to-do.sh`** - Basic Digital Ocean deployment script

### Management Scripts
- **`manage-production.sh`** - Production management utilities for Digital Ocean droplets
- **`setup-ssh-agent.sh`** - SSH agent setup for password-free droplet deployment

### Environment Files
- **`.env.droplet.example`** - Example environment file for Digital Ocean droplet deployment
- **`.env.production`** - Production environment configuration for droplets
- **`.env.production.bak`** - Backup of production environment file

### Legacy Directories
- **`legacy-deploy-directory/`** - Old deployment directory that was in root

## Why These Are Legacy

These scripts were designed for:
- Manual Digital Ocean droplet deployment
- Direct SSH-based file transfers
- Traditional Docker Compose deployments on VPS infrastructure

## New Implementation

The new Portainer GitHub URL implementation provides:
- ✅ Automated deployments via GitHub integration
- ✅ Web-based stack management through Portainer
- ✅ Better CI/CD integration
- ✅ Improved security and monitoring
- ✅ Easier rollbacks and updates

## Migration Notes

If you were using these legacy scripts:
1. **Stop using these scripts** - they may conflict with the new implementation
2. **Use the new Portainer-based deployment** workflow instead
3. **Remove any cron jobs** or automation that uses these scripts
4. **Update your documentation** to reference the new deployment process

## Preservation Reason

These scripts are preserved for:
- **Reference purposes** - in case specific deployment logic needs to be reviewed
- **Emergency fallback** - if the new system has critical issues (use with extreme caution)
- **Historical context** - to understand the evolution of the deployment process

## Important Warnings

❌ **DO NOT** use these scripts with the new Portainer branch
❌ **DO NOT** run these on systems already using Portainer deployment
❌ **DO NOT** assume these scripts are up-to-date with current infrastructure

---

**For current deployment procedures, please refer to the main project documentation and the new Portainer GitHub integration workflow.**