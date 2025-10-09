# Portainer Deployment Troubleshooting Guide

## Error: "empty compose file"

### Problem
```
Failed to interpolate config for stack whatismyadapter. 
Error: compose config operation failed: failed to create compose project: 
failed to load the compose file : empty compose file error
```

### Root Cause
This error occurs when using Portainer's **Repository method** with a compose file that contains a `build:` directive with a remote Git URL context.

### Why It Happens
1. Portainer clones your Git repository to access the compose file
2. The compose file tries to build from another Git URL
3. Portainer cannot resolve nested Git repositories
4. Results in "empty compose file" error

### ❌ Problematic Configuration

```yaml
services:
  willowcms:
    build:
      context: https://github.com/user/repo.git#branch
      dockerfile: path/to/Dockerfile
```

### ✅ Solution 1: Use Pre-Built Images (Recommended)

**File:** `docker-compose-portainer-deploy.yml`

```yaml
services:
  willowcms:
    image: ${WILLOWCMS_IMAGE:-whatismyadapter/willowcms:latest}
    # Remove build: section entirely
```

**Steps:**
1. Build your image locally or in CI/CD
2. Push to Docker Hub: `docker push whatismyadapter/willowcms:latest`
3. Update compose file to use `image:` instead of `build:`
4. Deploy in Portainer

### ✅ Solution 2: Build from Repository Root

If you need to build from source in Portainer:

```yaml
services:
  willowcms:
    build:
      context: .  # Repository root
      dockerfile: infrastructure/docker/willowcms/Dockerfile
      args:
        - UID=${DOCKER_UID:-1034}
        - GID=${DOCKER_GID:-100}
```

**Requirements:**
- Use Repository method in Portainer
- `context: .` points to repo root
- Dockerfile path is relative to repo root

### ✅ Solution 3: Use Web Editor Method

If you must use remote build context:

1. **In Portainer:**
   - Select **"Web editor"** method (not Repository)
   - Paste your compose file content
   - Add environment variables

2. **The remote build will work** because Portainer isn't trying to clone the repo

**Note:** You lose Git integration (no automatic updates)

---

## Error: "services.willowcms.image must be a string"

### Problem
```
services.willowcms.image must be a string
```

### Root Cause
Empty or incorrectly formatted `image:` field

### Solution
Ensure `image:` is properly defined:

```yaml
# ✅ CORRECT
image: ${WILLOWCMS_IMAGE:-whatismyadapter/willowcms:latest}

# ❌ WRONG - Empty
image:

# ❌ WRONG - Object
image:
  name: whatismyadapter/willowcms
```

---

## Error: "no configuration file provided"

### Problem
```
no configuration file provided: not found
```

### Root Cause
Incorrect compose path in Portainer configuration

### Solutions

#### For Repository Method:
```
Repository URL: https://github.com/user/repo.git
Repository reference: main-clean
Compose path: docker-compose-portainer-deploy.yml
```

**Important:** Path is **relative to repository root**, no leading `/`

#### Common Mistakes:
```
❌ /docker-compose.yml                    # Leading slash
❌ ./docker-compose.yml                   # Leading dot-slash
❌ portainer-stacks/docker-compose.yml   # File doesn't exist in repo
✅ docker-compose-portainer-deploy.yml   # Correct - relative path
```

---

## Error: "error looking up service: no such service"

### Problem
```
error looking up service 'mysql': no such service
```

### Root Cause
Service name mismatch between environment variables and compose file

### Solution
Verify service names match:

```yaml
# In docker-compose.yml
services:
  mysql:  # Service name
    image: mysql:8.0

# In environment variables
DB_HOST=mysql  # Must match service name
WILLOW_DB_SERVICE=mysql  # Must match service name
```

**Common Mismatches:**
- Service named `db` but env uses `mysql`
- Service named `mariadb` but env uses `mysql`
- Service named `database` but env uses `db`

---

## Error: "build: context: unsupported value"

### Problem
```
services.willowcms.build.context contains an unsupported value
```

### Root Cause
Portainer doesn't support all build context types

### Unsupported Contexts:
```yaml
# ❌ Remote Git URL
build:
  context: https://github.com/user/repo.git#branch

# ❌ SSH Git URL
build:
  context: git@github.com:user/repo.git

# ❌ HTTP URL
build:
  context: https://example.com/build-context.tar.gz
```

### Supported Contexts:
```yaml
# ✅ Local relative path (Repository method)
build:
  context: .
  dockerfile: Dockerfile

# ✅ Subdirectory (Repository method)
build:
  context: ./infrastructure/docker/willowcms
  dockerfile: Dockerfile
```

---

## Error: "required variable ... is not set"

### Problem
```
required variable 'SECURITY_SALT' is not set: required
```

### Root Cause
Missing or empty required environment variables

### Solution

#### Check Environment Variables:
1. Navigate to Stack → whatismyadapter → Editor
2. Scroll to "Environment variables"
3. Verify all required variables are set

#### Required Variables:
```bash
SECURITY_SALT=your-64-char-string
MYSQL_ROOT_PASSWORD=your-password
MYSQL_PASSWORD=your-password
REDIS_PASSWORD=your-password
WILLOW_ADMIN_PASSWORD=your-password
REDIS_COMMANDER_PASSWORD=your-password
```

#### Load from File:
1. Prepare `stack.env` file
2. In Portainer: "Load variables from .env file"
3. Select your `stack.env` file
4. Verify variables loaded correctly

---

## Error: "network ... not found"

### Problem
```
network willowcms_network not found
```

### Root Cause
Network defined in compose but Portainer can't create it

### Solution

#### Option 1: Let Docker Create Network
```yaml
networks:
  willowcms_network:
    driver: bridge
    name: ${NETWORK_NAME:-willowcms_production_network}
```

#### Option 2: Use External Network
```yaml
networks:
  willowcms_network:
    external: true
    name: existing_network_name
```

Create network first:
```bash
docker network create willowcms_network
```

---

## Error: "volume ... not found"

### Problem
```
volume willowcms_mysql_data not found
```

### Solutions

#### Option 1: Use Named Volumes (Recommended)
```yaml
volumes:
  willowcms_mysql_data:
    driver: local
    name: willowcms_production_mysql_data
```

Portainer creates automatically.

#### Option 2: Use Host Paths
```yaml
services:
  mysql:
    volumes:
      - /volume1/docker/whatismyadapter/mysql:/var/lib/mysql
```

**Ensure directory exists on host:**
```bash
sudo mkdir -p /volume1/docker/whatismyadapter/mysql
sudo chown -R 999:999 /volume1/docker/whatismyadapter/mysql
```

---

## Error: "permission denied"

### Problem
```
mkdir /var/www/html/logs: permission denied
```

### Root Cause
Container user UID/GID doesn't match volume ownership

### Solution

#### For Host Volumes:
```bash
# Set correct ownership
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/app
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/logs
sudo chmod -R 755 /volume1/docker/whatismyadapter
```

#### For Docker Volumes:
Portainer handles permissions automatically, but verify:

```yaml
services:
  willowcms:
    user: "1034:100"  # Must match server user
```

#### Verify User:
```bash
# In container
docker exec -it willowcms bash
id
# Should show: uid=1034 gid=100
```

---

## Portainer Deployment Checklist

### Before Deploying:

- [ ] Remove `build:` sections with remote Git URLs
- [ ] Replace with `image:` references
- [ ] Verify compose path is correct (no leading `/` or `./`)
- [ ] Check all required environment variables are set
- [ ] Ensure service names match between compose and env vars
- [ ] Verify network names are consistent
- [ ] Check volume paths exist (for host mounts)
- [ ] Set correct UID/GID (1034:100)
- [ ] Generate all secure passwords

### After Deploying:

- [ ] Check stack status shows "Active"
- [ ] Verify all services are "Running"
- [ ] Check container logs for errors
- [ ] Test database connection
- [ ] Test Redis connection
- [ ] Access application URL
- [ ] Login to admin panel
- [ ] Verify phpMyAdmin access
- [ ] Check Mailpit interface

---

## Quick Fixes

### Fix 1: Switch to Pre-Built Images
```bash
# Build and push image
docker build -t whatismyadapter/willowcms:latest .
docker push whatismyadapter/willowcms:latest

# Update compose file
sed -i 's/build:/# build:/' docker-compose.yml
sed -i '/# build:/a \    image: whatismyadapter/willowcms:latest' docker-compose.yml
```

### Fix 2: Use Correct Compose File
```
# In Portainer Repository settings:
Compose path: docker-compose-portainer-deploy.yml
```

### Fix 3: Regenerate Environment Variables
```bash
# Copy template
cp portainer-stacks/stack.env.template portainer-stacks/stack.env

# Generate passwords
echo "SECURITY_SALT=$(openssl rand -base64 32)" >> stack.env
echo "MYSQL_ROOT_PASSWORD=$(openssl rand -base64 24)" >> stack.env
echo "MYSQL_PASSWORD=$(openssl rand -base64 24)" >> stack.env
echo "REDIS_PASSWORD=$(openssl rand -base64 24)" >> stack.env
```

---

## Getting Help

### View Portainer Logs:
1. Portainer UI → Containers
2. Find your container
3. Click "Logs"
4. Look for errors in red

### View Docker Logs:
```bash
# Stack logs
docker compose logs -f

# Specific service
docker compose logs -f willowcms

# Last 100 lines
docker compose logs --tail=100 willowcms
```

### Validate Compose File:
```bash
# Local validation
docker compose -f docker-compose-portainer-deploy.yml config

# Check for errors
docker compose -f docker-compose-portainer-deploy.yml config 2>&1 | grep -i error
```

---

## Related Files

- `docker-compose-portainer-deploy.yml` - Portainer-ready compose (no build)
- `stack.env.template` - Environment variables template
- `PORTAINER_UI_DEPLOYMENT_GUIDE.md` - Complete deployment guide
- `DEPLOYMENT_UPDATES_SUMMARY.md` - Recent changes

---

**Last Updated:** 2025-01-07  
**For:** WillowCMS / WhatIsMyAdapter Portainer Deployment
