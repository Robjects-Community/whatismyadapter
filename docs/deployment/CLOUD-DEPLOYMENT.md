# WillowCMS Cloud Deployment Guide

## Quick Setup for Portainer

Your cloud user has **UID=1034, GID=100**. Use the optimized configuration:

### 🚀 **Deploy Options:**

#### Option 1: Use `portainer-cloud-deploy.yml`
- **File**: `portainer-cloud-deploy.yml` 
- **Optimized for**: Cloud user UID=1034, GID=100
- **Features**: Explicit user mapping, hardened security

#### Option 2: Use original with updated variables
- **File**: `portainer-stacks/docker-compose.yml`
- **Updated**: UID/GID in `stack.env` 

### 📋 **Portainer Configuration:**

1. **Repository Method:**
   - Repository URL: `https://github.com/garzarobm/willow.git`
   - Reference: `refs/heads/portainer-stack`
   - Compose path: `portainer-cloud-deploy.yml`

2. **Environment Variables from stack.env:**
   ```bash
   # Key cloud-specific settings
   DOCKER_UID=1034
   DOCKER_GID=100
   WILLOWCMS_IMAGE=willowcms:cloud-hardened
   
   # Update these in Portainer UI
   SECURITY_SALT=your-32-character-random-salt
   MYSQL_ROOT_PASSWORD=your-secure-root-password
   MYSQL_PASSWORD=your-secure-user-password
   REDIS_PASSWORD=your-secure-redis-password
   ```

### 🔐 **Security Notes:**

- ✅ **User Mapping**: `user: "1034:100"` explicitly set
- ✅ **Security Options**: `no-new-privileges:true`
- ✅ **Hardened Base**: Uses Docker Hardened Images
- ✅ **Environment Isolation**: All secrets in env variables

### 🛠️ **Key Features:**

- **Direct Git Build**: Builds from `main-clean` branch automatically
- **Volume Flexibility**: Supports both Docker volumes and host mounts
- **Health Checks**: Built-in container health monitoring
- **MySQL Client**: Available for database operations
- **CakePHP 5.x**: Latest framework support

### 📁 **Files in this deployment:**

```
/Volumes/1TB_DAVINCI/docker/willow/
├── portainer-cloud-deploy.yml     # ⭐ RECOMMENDED for cloud
├── portainer-stack-deploy.yml     # Alternative option
├── stack.env                      # Environment variables (UID=1034, GID=100)
├── portainer-stacks/
│   └── docker-compose.yml        # Original (also updated)
└── CLOUD-DEPLOYMENT.md           # This guide
```

### 🚁 **Quick Commands:**

```bash
# Validate configuration
./tools/compose/validate.sh

# Create backup before deployment
./tools/backup/backup_portainer_stack.sh

# Check current settings
grep "DOCKER_UID\|DOCKER_GID" stack.env
```

### 🎯 **Deployment Steps:**

1. In Portainer: **Create Stack** → **Repository**
2. Set Git URL: `https://github.com/garzarobm/willow.git`
3. Set Reference: `refs/heads/portainer-stack` 
4. Set Compose path: `portainer-cloud-deploy.yml`
5. Load environment variables from `stack.env`
6. **Override sensitive variables** in Portainer UI
7. Deploy with **"Force redeployment"** enabled

---

**✅ Ready to deploy!** The configuration is optimized for your cloud user UID=1034, GID=100.