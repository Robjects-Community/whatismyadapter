# Docker Multi-Platform Configuration

## Overview

This document explains the multi-platform Docker configuration for the WillowCMS project, which enables seamless development on both **Apple Silicon (ARM64)** and **Intel/AMD (x86_64)** architectures.

## Architecture Support

### Platform Detection

The system automatically detects and uses the appropriate platform based on your hardware:

- **Apple Silicon (M1/M2/M3)**: `linux/arm64`
- **Intel/AMD**: `linux/amd64`

## Configuration

### Environment Variables

The `.env` file contains platform configuration:

```env
# Docker Platform Configuration
DOCKER_PLATFORM=linux/arm64

# Image Version Tags
MYSQL_IMAGE_TAG=8.0
PHPMYADMIN_IMAGE_TAG=latest
MAILPIT_IMAGE_TAG=latest
REDIS_COMMANDER_IMAGE_TAG=latest
REDIS_TAG=7.2-alpine
```

### Service Platform Assignments

All services in `docker-compose.yml` have explicit platform specifications:

| Service | Platform | Native Support | Notes |
|---------|----------|----------------|-------|
| **willowcms** | `${DOCKER_PLATFORM}` | ✅ ARM64 & AMD64 | Custom built, multi-platform |
| **redis** | `${DOCKER_PLATFORM}` | ✅ ARM64 & AMD64 | Custom built, multi-platform |
| **mysql** | `${DOCKER_PLATFORM}` | ✅ ARM64 & AMD64 | Official MySQL 8.0 ARM64 support |
| **phpmyadmin** | `${DOCKER_PLATFORM}` | ✅ ARM64 & AMD64 | Official image supports both |
| **mailpit** | `${DOCKER_PLATFORM}` | ✅ ARM64 & AMD64 | Official image supports both |
| **redis-commander** | `linux/amd64` | ⚠️ AMD64 only | Runs via Rosetta 2 on Apple Silicon |

## Performance Implications

### Native ARM64 (Apple Silicon)

**Benefits:**
- ⚡ **Native performance** - No emulation overhead
- 🔋 **Better battery life** - ARM64 is more power-efficient
- 🌡️ **Lower heat generation** - More efficient CPU usage
- 🚀 **Faster builds** - Optimized for the native architecture

**Expected improvements:**
- 30-50% faster container startup times
- 20-40% better overall performance
- Reduced memory footprint

### Emulated Services

Only **redis-commander** runs under emulation (Rosetta 2) on Apple Silicon:
- Minimal performance impact (mostly idle)
- Transparent to the user
- No functionality differences

## Switching Platforms

### Temporary Switch

To temporarily run containers on a different platform:

```bash
# Switch to AMD64 (Intel/AMD)
export DOCKER_PLATFORM=linux/amd64
docker compose up -d

# Switch back to ARM64 (Apple Silicon)
export DOCKER_PLATFORM=linux/arm64
docker compose up -d
```

### Permanent Switch

Edit `.env` file:

```env
# For Apple Silicon
DOCKER_PLATFORM=linux/arm64

# For Intel/AMD or forced emulation
DOCKER_PLATFORM=linux/amd64
```

Then rebuild:

```bash
docker compose down
docker compose build --no-cache
docker compose up -d
```

## Verification

### Check Platform Configuration

Verify the configuration is loaded correctly:

```bash
docker compose --env-file .env config | grep "platform:"
```

Expected output:
```yaml
    platform: linux/arm64  # Most services
    platform: linux/amd64  # redis-commander only
```

### Check Running Containers

Verify containers are running on the correct platform:

```bash
for container in $(docker compose ps -q); do
    echo "Container: $(docker inspect $container --format '{{.Name}}')"
    docker inspect $container --format 'Platform: {{.Platform}}'
    echo "---"
done
```

Expected output for Apple Silicon:
```
Container: /willow-willowcms-1
Platform: linux/arm64
---
Container: /willow-mysql-1
Platform: linux/arm64
---
Container: /willow-redis-commander-1
Platform: linux/amd64
---
```

### Check Image Architecture

Verify built images:

```bash
docker images --format "table {{.Repository}}\t{{.Tag}}\t{{.ID}}" | grep willow
docker inspect willow-willowcms:latest --format '{{.Architecture}}'
docker inspect willow-redis:7.2-alpine --format '{{.Architecture}}'
```

## Troubleshooting

### Platform Mismatch Warnings

**Symptom:**
```
WARNING: The requested image's platform (linux/amd64) does not match 
the detected host platform (linux/arm64/v8)
```

**Solution:**
1. Ensure `.env` has `DOCKER_PLATFORM=linux/arm64`
2. Rebuild images: `docker compose build --no-cache`
3. Restart services: `docker compose up -d`

### Redis Health Check Failures

**Symptom:**
```
dependency failed to start: container willow-redis-1 is unhealthy
```

**Possible causes:**
1. **Platform mismatch** - Rebuild with correct platform
2. **Data corruption** - Redis bootguard will quarantine corrupt files
3. **Password mismatch** - Check `REDIS_PASSWORD` in `.env`

**Solution:**
```bash
# Check Redis logs
docker compose logs redis

# If platform issue, rebuild
docker compose down
docker compose build --no-cache redis
docker compose up -d
```

### Build Failures

**Symptom:**
```
ERROR: failed to solve: base image not available for platform
```

**Solution:**

Check if the base image supports your platform:

```bash
# Check available platforms for an image
docker manifest inspect robjects/dhi-php-mirror-robjects:8.3-alpine3.22-dev | grep -A 5 "platform"
```

If the base image doesn't support ARM64, you may need to:
1. Use an alternative ARM64-compatible base image
2. Force AMD64 emulation (slower): `DOCKER_PLATFORM=linux/amd64`

### Performance Issues on Apple Silicon

**Symptom:** Slow performance despite running on Apple Silicon

**Checklist:**
1. Verify platform: `docker inspect <container> --format '{{.Platform}}'`
2. Should show `linux/arm64` for most containers
3. Rebuild if showing `linux/amd64`: `docker compose build --no-cache`
4. Ensure Rosetta 2 is installed: `/usr/sbin/softwareupdate --install-rosetta`

## Multi-Platform Build Details

### Dockerfile Changes

Both `willowcms` and `redis` Dockerfiles include multi-platform support:

```dockerfile
# Multi-platform build support
ARG TARGETPLATFORM
ARG BUILDPLATFORM

# Platform-aware FROM statement
FROM --platform=${TARGETPLATFORM:-linux/arm64} base:image
```

**Build-time variables:**
- `TARGETPLATFORM`: The platform to build for (e.g., `linux/arm64`)
- `BUILDPLATFORM`: The platform building the image (may differ from target)

### Automatic Platform Selection

Docker Compose automatically passes the correct platform during build:

```yaml
services:
  willowcms:
    platform: ${DOCKER_PLATFORM:-linux/arm64}
    build:
      context: .
      dockerfile: infrastructure/docker/willowcms/Dockerfile
```

## Cross-Platform Development

### Team Collaboration

For teams with mixed architectures:

1. **Commit `.env.example`** with platform variables commented
2. **Don't commit `.env`** - it's machine-specific
3. **Document platform setup** in README
4. **Test on both platforms** before merging

### CI/CD Considerations

For continuous integration:

```yaml
# Example GitHub Actions
strategy:
  matrix:
    platform: [linux/amd64, linux/arm64]
env:
  DOCKER_PLATFORM: ${{ matrix.platform }}
```

## Additional Resources

- [Docker Multi-Platform Images](https://docs.docker.com/build/building/multi-platform/)
- [Apple Silicon Docker Performance](https://docs.docker.com/desktop/mac/apple-silicon/)
- [MySQL ARM64 Support](https://dev.mysql.com/doc/refman/8.0/en/linux-installation-native.html)

## Change History

| Date | Version | Changes |
|------|---------|---------|
| 2025-10-04 | 1.0.0 | Initial multi-platform configuration |

---

**Last Updated:** October 4, 2025  
**Maintained By:** WillowCMS Development Team
