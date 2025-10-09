# Portainer Deployment Guide - WillowCMS

This guide ensures complete compliance with [Portainer's Environment Variables documentation](https://docs.portainer.io/user/docker/stacks/add#environment-variables) and provides step-by-step instructions for all deployment methods.

## 📋 Portainer Requirements Compliance

Our implementation meets all Portainer documentation requirements:

- ✅ **Environment Variables**: Supported via Portainer UI and `.env` file uploads
- ✅ **stack.env File**: Automatic environment variable loading 
- ✅ **Git Repository Deployment**: Full support for GitHub-based deployments
- ✅ **Variable Substitution**: `${VARIABLE_NAME}` syntax throughout compose file
- ✅ **Security**: No hardcoded passwords or secrets in compose files
- ✅ **Relative Path Volumes**: Portainer Business Edition ready

## 🚀 Deployment Methods

### Method 1: Git Repository (Recommended)

Deploy directly from GitHub repository with automatic updates.

**1. In Portainer:**
- Navigate to **Stacks** → **Add stack**
- Provide stack name: `willow-cms`
- Select **Git repository**
- Repository URL: `https://github.com/garzarobm/willow.git`
- Repository reference: `main-clean`
- Compose file path: `portainer-stacks/docker-compose.yml`

**2. Environment Variables:**
Portainer will automatically load variables from `stack.env`. Override sensitive values:

```bash
# Critical variables to set in Portainer UI:
SECURITY_SALT=your-random-32-char-string
MYSQL_ROOT_PASSWORD=your-secure-root-password
MYSQL_PASSWORD=your-secure-user-password
REDIS_PASSWORD=your-secure-redis-password
WILLOW_ADMIN_PASSWORD=your-admin-password

# Optional API keys:
OPENAI_API_KEY=your-openai-key
YOUTUBE_API_KEY=your-youtube-key
TRANSLATE_API_KEY=your-translate-key
```

**3. Deploy:**
- Click **Deploy the stack**
- Monitor deployment progress
- Check stack status and container health

### Method 2: Upload Compose File

Upload the compose file directly to Portainer.

**1. Prepare Files:**
```bash
# Download compose file and stack.env
curl -O https://raw.githubusercontent.com/garzarobm/willow/main-clean/portainer-stacks/docker-compose.yml
curl -O https://raw.githubusercontent.com/garzarobm/willow/main-clean/stack.env
```

**2. In Portainer:**
- Navigate to **Stacks** → **Add stack**
- Provide stack name: `willow-cms`
- Select **Upload**
- Choose `docker-compose.yml` file

**3. Environment Variables:**
- Use **Load variables from .env file** to upload `stack.env`
- Override sensitive variables in Portainer UI
- Set production-specific values as needed

### Method 3: Web Editor

Paste the compose file content directly into Portainer.

**1. In Portainer:**
- Navigate to **Stacks** → **Add stack**
- Provide stack name: `willow-cms`
- Select **Web editor**
- Paste the compose file content

**2. Configure Variables:**
- Set all environment variables manually in Portainer UI
- Use the variable list from `stack.env` as reference
- Ensure all sensitive variables are properly configured

## 🔧 Environment Variables Configuration

### Portainer UI Configuration

According to Portainer documentation, you can set environment variables in two ways:

**Option 1: Individual Variables**
Set each variable manually in the Portainer Environment Variables section:

| Variable | Example Value | Description |
|----------|---------------|-------------|
| `SECURITY_SALT` | `abcd1234efgh5678ijkl9012mnop3456` | App security key |
| `MYSQL_ROOT_PASSWORD` | `SecureRootP@ss123` | MySQL root password |
| `MYSQL_PASSWORD` | `WillowUserP@ss456` | MySQL user password |
| `REDIS_PASSWORD` | `RedisP@ss789` | Redis password |
| `APP_FULL_BASE_URL` | `https://cms.yourdomain.com` | Production URL |

**Option 2: Upload .env File**
- Create a local `.env` file with your values
- Use **Load variables from .env file** in Portainer
- Portainer will import all variables from the file

### Variable Substitution in Compose File

Our compose file uses Portainer-compliant variable substitution:

```yaml
# Environment variables with defaults
environment:
  - APP_NAME=${APP_NAME:-WillowCMS}
  - DEBUG=${DEBUG:-false}
  - MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}

# Build arguments
build:
  args:
    - UID=${DOCKER_UID:-1000}
    - GID=${DOCKER_GID:-1000}

# Port mappings
ports:
  - "${WILLOW_HTTP_PORT:-8080}:80"
```

### Stack.env File Integration

As per Portainer documentation, we include `env_file: - stack.env` in all services:

```yaml
services:
  willowcms:
    # ... other configuration
    env_file:
      - stack.env
    environment:
      # Variables from stack.env are available here
      - APP_NAME=${APP_NAME}
      - MYSQL_PASSWORD=${MYSQL_PASSWORD}
```

## 🔒 Security Best Practices

Following Portainer security recommendations:

### 1. Environment Variable Security
- ✅ No hardcoded passwords in compose files
- ✅ All secrets use `${VARIABLE_NAME}` substitution
- ✅ Default values only for non-sensitive configuration
- ✅ Critical variables must be overridden in Portainer UI

### 2. Network Security
```yaml
# Parameterized network configuration
networks:
  ${NETWORK_NAME:-willow_network}:
    driver: bridge
```

### 3. Volume Security
```yaml
# Named volumes with driver configuration
volumes:
  willow_mysql_data:
    driver: ${VOLUME_DRIVER:-local}
```

## 📊 Deployment Verification

### 1. Stack Health Check
```bash
# Check stack deployment status in Portainer:
# Stacks → willow-cms → Container list
# All containers should show "running" status
```

### 2. Service Connectivity
```bash
# Test application access:
curl -I http://your-domain:8080

# Test database access via phpMyAdmin:
curl -I http://your-domain:8082

# Test email service:
curl -I http://your-domain:8025
```

### 3. Environment Variable Validation
In Portainer container console or via local tools:
```bash
# Check environment variables in running container
./tools/env/print-env.sh

# Validate GitHub repository access  
./tools/github/test-url.sh

# Test health endpoints
./tools/health/health-check.sh
```

## 🔄 Stack Management

### Auto-Updates from Git
Enable automatic updates for Git-based deployments:

1. In Portainer stack settings
2. Toggle **Enable automatic updates**
3. Set update interval (e.g., 5 minutes)
4. Enable **Re-pull image and redeploy**
5. **Save configuration**

### Manual Stack Updates
```bash
# In Portainer:
# 1. Navigate to Stacks → willow-cms
# 2. Click "Update the stack"
# 3. Confirm update source (Git/Upload/Editor)
# 4. Click "Update"
```

### Stack Rollback
```bash
# Using local tools (if deployment fails):
./tools/env/switch-env.sh remote
./tools/cleanup/rollback.sh

# Or use Portainer:
# 1. Stop current stack
# 2. Deploy previous working configuration
# 3. Monitor deployment status
```

## 🐛 Troubleshooting

### Common Portainer Issues

**1. Environment Variables Not Loading**
- ✅ Verify `stack.env` exists in repository root
- ✅ Check `env_file: - stack.env` is present in all services
- ✅ Confirm variable syntax: `${VARIABLE_NAME}`

**2. Git Repository Access Issues**
```bash
# Test repository access:
./tools/github/test-url.sh -u https://github.com/garzarobm/willow.git -r main-clean

# Verify repository URL and branch/tag
git ls-remote https://github.com/garzarobm/willow.git
```

**3. Build Context Issues**
```yaml
# Ensure build context points to valid Git reference:
build:
  context: ${GIT_URL}#${GIT_REF}
  dockerfile: infrastructure/docker/willowcms/Dockerfile
```

**4. Volume Mount Issues**
- ✅ Use named volumes (not bind mounts) for Portainer
- ✅ Ensure volume drivers are compatible with deployment environment
- ✅ Check volume permissions in container logs

### Debug Commands

```bash
# Local debugging:
./tools/deploy/compose-lint.sh -v
./tools/health/health-check.sh
./tools/logs/collect.sh

# Check Portainer API (if accessible):
./tools/portainer/api-client.sh GET /api/stacks
./tools/portainer/stack-status.sh
```

## 📈 Advanced Configuration

### Portainer Business Edition Features

**Relative Path Volumes**
Enable in stack configuration for automatic directory creation:
```yaml
volumes:
  - ./data:/var/www/html/data
  - ./logs:/var/www/html/logs
```

**Stack Webhooks**
Enable webhooks for CI/CD integration:
1. Enable webhook in stack settings
2. Copy webhook URL
3. Configure in GitHub Actions or CI system

**Registry Authentication**
Configure for private Docker registries:
1. Navigate to Registries in Portainer
2. Add registry credentials
3. Select specific registries in stack deployment

## ✅ Compliance Checklist

- [ ] Compose file includes `env_file: - stack.env` for all services
- [ ] All sensitive data uses `${VARIABLE_NAME}` substitution
- [ ] `stack.env` file exists in repository root
- [ ] Environment variables documented and examples provided
- [ ] No hardcoded passwords, IPs, or secrets in compose file
- [ ] Git repository deployment tested and working
- [ ] Stack webhook configuration (optional)
- [ ] Registry authentication configured (if needed)
- [ ] Auto-update settings configured
- [ ] Health checks and monitoring configured

This implementation fully complies with Portainer's environment variable requirements and provides a robust, secure deployment platform for WillowCMS.