# Dockerfile Variants for WillowCMS

This directory contains different Dockerfile variants optimized for specific deployment scenarios.

## 📁 Available Dockerfiles

### `Dockerfile.local-dev`
**Purpose:** Local development environment with debugging tools and hot-reload support

**Features:**
- Minimal setup for quick development iteration
- Debugging tools enabled (Xdebug)
- Non-root user `nobody` (UID 1000:1000)
- Redis included for caching
- Single-stage build for faster rebuilds

**Use Cases:**
- VSCode development workflow
- Feature development and testing
- Local Docker Compose development
- Quick prototyping

**Build Command:**
```bash
docker build -f deployment/dockerfiles/Dockerfile.local-dev -t willowcms:dev .
```

**Docker Compose:**
This is the default Dockerfile referenced in `docker-compose.yml`:
```bash
docker compose up -d --build
```

---

### `Dockerfile.synology-nas`
**Purpose:** Production deployment on Synology NAS with hardened security

**Features:**
- **Multi-stage build** for optimized image size (3 stages)
- Non-root user `willowcms` (UID 1034:100 for Synology compatibility)
- Comprehensive security hardening
- No hardcoded secrets (uses environment variables)
- Optimized Composer autoloader
- Detailed startup logging with system information
- Redis configuration with proper directory creation
- Both PHP 8.3 installations configured (Alpine + base image)

**Security Features:**
- Non-root execution
- No hardcoded passwords or secrets
- Minimal attack surface
- Security options: `no-new-privileges:true`
- Proper file permissions and ownership

**Build Command:**
```bash
# Build for Synology NAS (ARM64 or AMD64)
docker build -f deployment/dockerfiles/Dockerfile.synology-nas \
  --build-arg UID=1034 \
  --build-arg GID=100 \
  -t willowcms:synology .
```

**Multi-Platform Build:**
```bash
docker buildx build --platform linux/amd64,linux/arm64 \
  -f deployment/dockerfiles/Dockerfile.synology-nas \
  -t willowcms:synology .
```

---

### `Dockerfile.experimental`
**Purpose:** Testing multi-stage builds and experimental optimizations

**Features:**
- 5-stage build process: composer → node-assets → builder → deps → production
- Node.js asset building stage (currently commented out)
- Separate build and dependency stages
- Production-optimized autoloader
- Non-root user `nobody` (UID 1000:1000)

**Use Cases:**
- Testing new build optimizations
- Evaluating multi-stage performance
- Frontend asset compilation experiments
- **NOT FOR PRODUCTION USE**

**Build Command:**
```bash
docker build -f deployment/dockerfiles/Dockerfile.experimental \
  --build-arg UID=1000 \
  --build-arg GID=1000 \
  -t willowcms:experimental .
```

---

## 🔧 Build Arguments

All Dockerfiles support the following build arguments:

| Argument | Default | Description |
|----------|---------|-------------|
| `UID` | 1000 (dev/experimental)<br>1034 (synology) | User ID for container user |
| `GID` | 1000 (dev/experimental)<br>100 (synology) | Group ID for container user |

### Setting Custom UID/GID

```bash
# Example: Build with your Mac user permissions
docker build -f deployment/dockerfiles/Dockerfile.synology-nas \
  --build-arg UID=501 \
  --build-arg GID=20 \
  -t willowcms:custom .
```

---

## 📊 Comparison Matrix

| Feature | local-dev | synology-nas | experimental |
|---------|-----------|--------------|--------------|
| **Stages** | 1 (single) | 3 (optimized) | 5 (complex) |
| **User** | nobody | willowcms | nobody |
| **Default UID:GID** | 1000:1000 | 1034:100 | 1000:1000 |
| **Image Size** | ~450 MB | ~400 MB | ~420 MB |
| **Build Time** | Fast | Medium | Slower |
| **Security** | Basic | Hardened | Good |
| **Documentation** | Minimal | Extensive (405 lines) | Moderate |
| **Redis Config** | Basic | Comprehensive | Basic |
| **PHP Extensions** | 1 location | 2 locations | 1 location |
| **Startup Script** | Minimal | Detailed logging | Minimal |
| **Best For** | Development | Production NAS | Testing |

---

## 🚀 Quick Start Guide

### For Local Development
```bash
# Use the main docker-compose.yml
docker compose up -d --build
```

### For Synology NAS Deployment
```bash
# Build the image
docker build -f deployment/dockerfiles/Dockerfile.synology-nas \
  --build-arg UID=1034 \
  --build-arg GID=100 \
  -t willowcms:synology .

# Deploy using Portainer stack
# (See deployment/portainer/ for stack configurations)
```

### For Testing/Experimentation
```bash
# Build experimental version
docker build -f deployment/dockerfiles/Dockerfile.experimental \
  -t willowcms:experimental .

# Run standalone
docker run -d -p 8080:80 willowcms:experimental
```

---

## 📝 Configuration Files

All Dockerfiles use shared configuration files from:
```
infrastructure/docker/willowcms/config/
├── nginx/
│   ├── nginx.conf           # Main Nginx configuration
│   └── nginx-cms.conf       # Site-specific configuration
└── php/
    ├── fpm-pool.conf        # PHP-FPM pool configuration
    └── php.ini              # PHP runtime settings
```

---

## 🔄 Switching Between Dockerfiles

### Method 1: Edit docker-compose.yml
Change the `dockerfile` path in `docker-compose.yml`:
```yaml
services:
  willowcms:
    build:
      dockerfile: deployment/dockerfiles/Dockerfile.synology-nas  # Change this line
```

### Method 2: Use docker-compose override
Create `docker-compose.override.yml`:
```yaml
services:
  willowcms:
    build:
      dockerfile: deployment/dockerfiles/Dockerfile.synology-nas
      args:
        - UID=1034
        - GID=100
```

---

## 🛠️ Maintenance

### Updating Dockerfiles
When modifying Dockerfiles:

1. **Test locally first** with `Dockerfile.local-dev`
2. **Test the build** without cache: `docker build --no-cache`
3. **Verify all services start**: `docker compose ps`
4. **Check logs**: `docker compose logs willowcms`
5. **Run tests**: `docker compose exec willowcms php vendor/bin/phpunit`

### Adding New Variants
To create a new Dockerfile variant:

1. Copy the most appropriate existing Dockerfile
2. Name it descriptively: `Dockerfile.[purpose]`
3. Update this README with the new variant's details
4. Test thoroughly before using in production

---

## 📚 Additional Documentation

- [Docker Architecture Overview](../../docs/docker-architecture.md)
- [Deployment Scripts](../scripts/README.md)
- [Portainer Stack Configurations](../portainer/README.md)
- [Infrastructure Configuration](../../infrastructure/docker/README.md)

---

## ⚡ Performance Tips

1. **Use multi-stage builds** for production (synology-nas variant)
2. **Layer caching**: Copy composer files before application code
3. **Minimize layers**: Combine RUN commands where logical
4. **Clean up in same layer**: `apk add` + `rm -rf /var/cache/apk/*`
5. **Optimize Composer**: Use `--no-dev --optimize-autoloader`

---

## 🐛 Troubleshooting

### Build Fails with Permission Errors
```bash
# Ensure you have correct UID/GID
docker build --build-arg UID=$(id -u) --build-arg GID=$(id -g) ...
```

### Configuration Files Not Found
```bash
# Verify config paths exist
ls -la infrastructure/docker/willowcms/config/nginx/
ls -la infrastructure/docker/willowcms/config/php/
```

### Container Starts but App Doesn't Load
```bash
# Check container logs
docker compose logs willowcms

# Verify file permissions
docker compose exec willowcms ls -la /var/www/html/
```

---

**Last Updated:** October 6, 2025  
**Maintained by:** WillowCMS DevOps Team
