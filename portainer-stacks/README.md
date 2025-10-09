# Portainer Stacks Directory

Complete Portainer deployment configurations for WillowCMS (WhatIsMyAdapter) with multiple deployment options.

---

## 📁 Directory Contents

### 🐳 Docker Compose Files

| File | Purpose | Use Case |
|------|---------|----------|
| **`docker-compose.yml`** | Main Git-based deployment | Production deployment from GitHub repository |
| **`docker-compose-portainer.yml`** | Local Portainer testing | Test stack locally before production |
| **`docker-compose-syn.yml`** | Synology NAS deployment | Production deployment on Synology NAS with host-mounted volumes |

### ⚙️ Environment Files

| File | Purpose | Security |
|------|---------|----------|
| **`stack.env.template`** | General deployment template | Template - Safe to commit |
| **`stack.env`** | Active general environment | **NEVER commit** |
| **`stack.env.cloud`** | Cloud-specific settings | **NEVER commit** |
| **`stack-test.env`** | Testing environment | Safe test values |
| **`stack-syn.env.template`** | Synology NAS template | Template - Safe to commit |

### 🔧 Helper Scripts

| File | Purpose |
|------|---------|
| **`quick-deploy.sh`** | Generate secure passwords and deployment instructions |
| **`build-image.sh`** | Build WillowCMS Docker image for testing |

### 📄 Other Files

| File | Purpose |
|------|---------|
| **`.gitignore`** | Prevents committing sensitive `.env` files |
| **`.env.template`** | Backup environment template |

---

## 🚀 Quick Start Guide

### Option 1: Git-Based Deployment (Recommended)

**Best for:** Automatic deployments from GitHub

```bash
# 1. In Portainer, create a new stack
# 2. Choose "Repository" as build method
# 3. Configure:
#    - Repository URL: https://github.com/yourusername/willow.git
#    - Repository reference: main-clean
#    - Compose path: portainer-stacks/docker-compose.yml
# 4. Add environment variables from stack.env.template
# 5. Deploy
```

**Features:**
- ✅ Builds from Git automatically
- ✅ Docker-managed volumes (portable)
- ✅ Environment variable flexibility
- ✅ Security hardening enabled
- ✅ Supports multiple environments

---

### Option 2: Synology NAS Deployment

**Best for:** Production on Synology NAS with persistent host storage

```bash
# 1. Prepare Synology NAS
ssh admin@your-nas-ip
mkdir -p /volume1/docker/whatismyadapter/{app,logs,tmp,mysql,redis,mailpit}
chown -R 1034:100 /volume1/docker/whatismyadapter

# 2. Copy template and configure
cp stack-syn.env.template stack-syn.env
nano stack-syn.env  # Fill in all CHANGE-ME values

# 3. Deploy in Portainer
# - Upload docker-compose-syn.yml
# - Load stack-syn.env as environment variables
# - Deploy stack
```

**Features:**
- ✅ Host-mounted volumes (data persists on NAS)
- ✅ Synology user permissions (UID:1034, GID:100)
- ✅ All services namespaced with `-syn` suffix
- ✅ No hardcoded paths (all via environment variables)
- ✅ Optimized for Synology NAS performance

---

### Option 3: Local Portainer Testing

**Best for:** Testing stack locally before production deployment

```bash
# 1. Build test image
./build-image.sh

# 2. Deploy in local Portainer
# - Use docker-compose-portainer.yml
# - Load stack-test.env
# - Deploy on port 9080 (avoids conflicts)
```

**Features:**
- ✅ Pre-built image (no build step)
- ✅ Test ports (9000 series - no conflicts)
- ✅ Isolated network and volumes
- ✅ Safe test credentials
- ✅ Quick iteration

---

## 📊 Comparison Matrix

| Feature | docker-compose.yml | docker-compose-portainer.yml | docker-compose-syn.yml |
|---------|-------------------|----------------------------|----------------------|
| **Deployment Source** | Git repository | Pre-built image | Local build |
| **Volume Type** | Docker-managed | Docker-managed | Host-mounted |
| **Volume Names** | `willow_*` | `willow_portainer_*` | `/volume1/docker/*` |
| **Network Name** | `willow_network` | `willow_portainer_network` | `whatismyadapter-syn-network` |
| **Service Suffix** | None | None | `-syn` |
| **Default Port** | 8080 | 9080 | 8080 |
| **MySQL Port** | 3310 | 9310 | 3310 |
| **Use Case** | Production | Testing | Synology NAS |
| **env_file** | `stack.env` | None (Portainer UI) | None (Portainer UI) |
| **Security** | Hardened | Basic | Hardened |
| **User Permissions** | Container default | Container default | UID:1034, GID:100 |

---

## 🔐 Security Best Practices

### Environment Variables

**✅ DO:**
- Use templates to create actual `.env` files
- Generate secure passwords with `openssl rand -base64 32`
- Store credentials in a password manager
- Use different passwords for each environment

**❌ DON'T:**
- Commit `stack.env` or `stack-syn.env` to Git
- Hardcode passwords in compose files
- Share environment files via insecure channels
- Reuse passwords across environments

### File Permissions (Synology NAS)

```bash
# Correct ownership for Synology
chown -R 1034:100 /volume1/docker/whatismyadapter

# Correct permissions
chmod 755 /volume1/docker/whatismyadapter
chmod 644 /volume1/docker/whatismyadapter/stack-syn.env
```

---

## 🛠️ Service Access URLs

After deployment, access services at:

| Service | URL | Credentials |
|---------|-----|-------------|
| **WillowCMS** | `http://your-server:8080` | admin / `${WILLOW_ADMIN_PASSWORD}` |
| **phpMyAdmin** | `http://your-server:8082` | root / `${MYSQL_ROOT_PASSWORD}` |
| **Mailpit** | `http://your-server:8025` | No auth required |
| **Redis Commander** | `http://your-server:8084` | admin / `${REDIS_COMMANDER_PASSWORD}` |

### Synology NAS Specific Ports

For `docker-compose-syn.yml`, replace `your-server` with your Synology NAS IP address.

---

## 📝 Environment Variable Reference

### Required Variables (Must Set)

```bash
# Security (Critical)
SECURITY_SALT=<64-char-random-string>
MYSQL_ROOT_PASSWORD=<secure-password>
MYSQL_PASSWORD=<secure-password>
REDIS_PASSWORD=<secure-password>
WILLOW_ADMIN_PASSWORD=<secure-password>

# Application
APP_FULL_BASE_URL=https://yourdomain.com
WILLOW_ADMIN_EMAIL=admin@yourdomain.com
```

### Optional Variables (Have Defaults)

```bash
# Application
APP_NAME=WillowCMS (or WhatIsMyAdapter)
DEBUG=false
APP_DEFAULT_TIMEZONE=America/Chicago

# Database
MYSQL_DATABASE=willow_cms
MYSQL_USER=willow_user
MYSQL_PORT=3310

# Redis
REDIS_PORT=6379
REDIS_USERNAME=default

# Ports
WILLOW_HTTP_PORT=8080
PMA_HTTP_PORT=8082
MAILPIT_HTTP_PORT=8025
REDIS_COMMANDER_HTTP_PORT=8084
```

### Synology-Specific Variables

```bash
# User/Group IDs
PUID=1034
PGID=100

# Volume Paths
WILLOWCMS_APP_PATH=/volume1/docker/whatismyadapter/app
MYSQL_DATA_PATH=/volume1/docker/whatismyadapter/mysql
REDIS_DATA_PATH=/volume1/docker/whatismyadapter/redis
# ... etc
```

---

## 🔄 Migration Guide

### From `docker-compose-cloud.yml` to `docker-compose-syn.yml`

The old `docker-compose-cloud.yml` has been refactored to `docker-compose-syn.yml` with improvements:

**What Changed:**
- ✅ All services now have `-syn` suffix
- ✅ Hardcoded paths replaced with environment variables
- ✅ User IDs now configurable via `PUID`/`PGID`
- ✅ Added security hardening options
- ✅ Added restart policies
- ✅ Improved documentation

**Migration Steps:**
1. Stop old stack: `docker compose -f docker-compose-cloud.yml down`
2. Create `stack-syn.env` from template
3. Update volume paths in `stack-syn.env` (if different)
4. Deploy new stack: `docker compose -f docker-compose-syn.yml up -d`
5. Verify all services are running

---

## 🐛 Troubleshooting

### Ports Already in Use

```bash
# Check what's using a port
lsof -i :8080

# Change port in stack.env
WILLOW_HTTP_PORT=8081
```

### Permission Denied (Synology)

```bash
# Fix ownership
chown -R 1034:100 /volume1/docker/whatismyadapter

# Fix permissions
chmod -R 755 /volume1/docker/whatismyadapter
```

### Services Won't Start

```bash
# Check logs
docker compose logs willowcms
docker compose logs mysql

# Verify environment variables are set
docker compose config

# Check disk space
df -h
```

### Database Connection Errors

```bash
# Verify MySQL is running
docker compose ps mysql

# Check MySQL logs
docker compose logs mysql

# Test connection
docker compose exec willowcms php bin/cake.php migrations status
```

---

## 📚 Additional Resources

- [Portainer Documentation](https://docs.portainer.io/)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file/)
- [WillowCMS Documentation](../docs/)
- [Security Best Practices](../docs/security.md)

---

## 🗂️ File History

| Date | Event |
|------|-------|
| 2025-01 | Created portainer-stacks directory |
| 2025-01 | Added `docker-compose.yml` for Git deployment |
| 2025-01 | Added `docker-compose-portainer.yml` for testing |
| 2025-01 | Created `quick-deploy.sh` helper script |
| 2025-10-09 | Created `docker-compose-syn.yml` (replaced docker-compose-cloud.yml) |
| 2025-10-09 | Added `stack-syn.env.template` for Synology deployments |
| 2025-10-09 | Deprecated `docker-compose-cloud.yml` |

---

## 📞 Support

For issues or questions:
1. Check the troubleshooting section above
2. Review the main project documentation
3. Check Portainer logs in the UI
4. Open an issue in the repository

---

**Last Updated:** October 9, 2025
