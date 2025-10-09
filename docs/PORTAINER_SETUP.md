# Portainer Setup and URL Configuration Guide

This guide covers setting up WillowCMS with Portainer, ensuring all URL options and configurations work correctly for both local and remote deployments.

## Overview

The WillowCMS deployment system supports:
- **Local Docker Compose**: Direct deployment using `docker compose`
- **Local Portainer**: Stack deployment to local Portainer instance  
- **Remote Portainer**: Stack deployment to remote Portainer server

## Portainer URL Configuration Options

### 1. Local Portainer Setup

**Default Local URLs:**
```bash
# Portainer Server (local)
PORTAINER_URL=http://localhost:9000
PORTAINER_URL=https://localhost:9443  # SSL version

# Common alternative ports
PORTAINER_URL=http://localhost:9001
PORTAINER_URL=http://127.0.0.1:9000
```

**Docker Desktop Extension:**
```bash
# If using Portainer Docker Desktop extension
PORTAINER_URL=http://localhost:49000
PORTAINER_URL=https://localhost:49443
```

### 2. Remote Portainer Setup

**Production Server URLs:**
```bash
# Standard production setup
PORTAINER_URL=https://portainer.your-domain.com
PORTAINER_URL=https://your-server.com:9443

# Alternative port configurations
PORTAINER_URL=https://your-server.com:8443
PORTAINER_URL=https://portainer-server.example.com
```

## Environment Configuration

### Local Environment (`env/local.env`)

```bash
# =============================================================================
# LOCAL PORTAINER CONFIGURATION
# =============================================================================
PORTAINER_URL=http://localhost:9000
PORTAINER_ENDPOINT_ID=1
PORTAINER_TLS_VERIFY=false
PORTAINER_API_TOKEN_FILE=.portainer-token-local

# Application URLs (local)
APP_FULL_BASE_URL=http://localhost:8080
HEALTHCHECK_URLS=http://localhost:8080,http://localhost:8082

# Git Configuration (for local testing)
GIT_REMOTE_NAME=garzarobm
GIT_URL=https://github.com/garzarobm/willow.git
GIT_REF=main-clean
STACK_FILE_PATH=portainer-stacks/docker-compose.yml
```

### Remote Environment (`env/remote.env`)

```bash
# =============================================================================
# REMOTE PORTAINER CONFIGURATION  
# =============================================================================
PORTAINER_URL=https://portainer.your-domain.com
PORTAINER_ENDPOINT_ID=2
PORTAINER_TLS_VERIFY=true
PORTAINER_API_TOKEN_FILE=.portainer-token-remote

# Application URLs (production)
APP_FULL_BASE_URL=https://your-app-domain.com
HEALTHCHECK_URLS=https://your-app-domain.com,https://admin.your-app-domain.com

# Git Configuration (for production)
GIT_REMOTE_NAME=garzarobm
GIT_URL=https://github.com/garzarobm/willow.git
GIT_REF=main-clean
STACK_FILE_PATH=portainer-stacks/docker-compose.yml
```

## URL Testing and Validation

### 1. Test GitHub URLs

```bash
# Test current environment GitHub configuration
./tools/github/test-url.sh

# Test specific URL and reference
./tools/github/test-url.sh -u https://github.com/garzarobm/willow.git -r main-clean

# JSON output for automation
./tools/github/test-url.sh --json > github-test-results.json
```

### 2. Test Portainer Connectivity

```bash
# Test Portainer API connectivity
./tools/portainer/api-client.sh GET /api/version

# List available endpoints
./tools/portainer/api-client.sh GET /api/endpoints
```

### 3. Health Check URLs

```bash
# Test all configured health check URLs
./tools/health/health-check.sh

# Test specific URL
./tools/health/http-check.sh http://localhost:8080
```

## Stack Deployment URLs

### Portainer Stack Configuration

The system uses these URL patterns for Portainer stack deployments:

**Git Repository URLs (tested and validated):**
- `https://github.com/garzarobm/willow.git` (Production fork)
- `https://github.com/matthewdeaves/willow.git` (Original maintainer)
- `https://github.com/Robjects-Community/WhatIsMyAdaptor.git` (Community)

**Git References:**
- `main-clean` (garzarobm - recommended for production)
- `main` (standard main branch)
- `develop` (development branch)
- Any valid branch or tag name

**Stack File Paths:**
- `portainer-stacks/docker-compose.yml` (default)
- `docker-compose.yml` (root level)
- `compose/production.yml` (custom path)

## Portainer API Token Setup

### Generate API Token

1. **Local Portainer:**
   ```bash
   # Open Portainer in browser
   open http://localhost:9000
   
   # Navigate to: User Settings > Access tokens > Add access token
   # Copy token and save to file
   echo "your-token-here" > .portainer-token-local
   ```

2. **Remote Portainer:**
   ```bash
   # For production server
   echo "your-production-token" > .portainer-token-remote
   ```

### Using Login Script (Alternative)

```bash
# Login and save token automatically
PORTAINER_USERNAME=admin \
PORTAINER_PASSWORD=yourpassword \
./tools/portainer/login.sh
```

## URL Validation Checklist

### Before Deployment

- [ ] GitHub URL is accessible: `./tools/github/test-url.sh`
- [ ] Portainer URL is reachable: `curl -k $PORTAINER_URL/api/version`
- [ ] API token is valid: `./tools/portainer/api-client.sh GET /api/users/me`
- [ ] Endpoint ID exists: `./tools/portainer/api-client.sh GET /api/endpoints`
- [ ] Stack file path is correct in repository

### After Deployment

- [ ] Application health checks pass: `./tools/health/health-check.sh`
- [ ] Database connectivity works: `./tools/health/db-check.sh`
- [ ] All configured URLs are accessible
- [ ] SSL certificates are valid (for HTTPS URLs)

## Troubleshooting URL Issues

### Common Problems and Solutions

**1. Portainer Connection Failed**
```bash
# Check if Portainer is running
docker ps | grep portainer

# Test basic connectivity
curl -k ${PORTAINER_URL}/api/version

# Check firewall/port accessibility
nc -zv localhost 9000
```

**2. GitHub URL Not Accessible**
```bash
# Test with verbose output
./tools/github/test-url.sh -v

# Manual verification
git ls-remote https://github.com/garzarobm/willow.git

# Check network connectivity
curl -I https://github.com/garzarobm/willow.git
```

**3. Health Check URLs Failing**
```bash
# Test individual URLs
curl -I http://localhost:8080
curl -I http://localhost:8082

# Check if containers are running
docker compose ps
```

**4. SSL Certificate Issues**
```bash
# Disable SSL verification (for testing only)
PORTAINER_TLS_VERIFY=false

# Check certificate validity
openssl s_client -connect your-server.com:9443 -servername your-server.com
```

## Environment Switching Workflow

### Complete Workflow Example

```bash
# 1. Switch to local environment
./tools/env/switch-env.sh local

# 2. Select GitHub remote
./tools/github/select-remote.sh garzarobm

# 3. Validate all configurations
./tools/github/test-url.sh
./tools/deploy/compose-lint.sh

# 4. Deploy locally
./tools/deploy/compose-local.sh up

# 5. Check health
./tools/health/health-check.sh

# 6. Switch to remote for production
./tools/env/switch-env.sh remote

# 7. Deploy to Portainer
./tools/portainer/deploy-stack.sh

# 8. Monitor deployment
./tools/portainer/stack-status.sh
```

## URL Environment Variables Reference

| Variable | Local Example | Remote Example | Description |
|----------|---------------|----------------|-------------|
| `PORTAINER_URL` | `http://localhost:9000` | `https://portainer.domain.com` | Portainer server URL |
| `APP_FULL_BASE_URL` | `http://localhost:8080` | `https://app.domain.com` | Application base URL |
| `HEALTHCHECK_URLS` | `http://localhost:8080,http://localhost:8082` | `https://app.domain.com,https://admin.domain.com` | Health check endpoints |
| `GIT_URL` | `https://github.com/garzarobm/willow.git` | `https://github.com/garzarobm/willow.git` | Git repository URL |
| `MYSQL_PORT` | `3310` | `3306` | MySQL port mapping |
| `WILLOW_HTTP_PORT` | `8080` | `80` or `443` | Application HTTP port |

This configuration ensures all URL options work correctly across different deployment scenarios while maintaining security and flexibility.