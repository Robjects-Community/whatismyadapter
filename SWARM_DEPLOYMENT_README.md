# Docker Swarm Stack Deployment Guide

## 📋 Overview

This guide explains how to deploy WillowCMS using Docker Swarm with the `docker-compose-stack.yml` file.

## 📁 Files Created

1. **`docker-compose-stack.yml`** - Docker Swarm stack configuration file (14KB)
2. **`validate-stack.sh`** - Validation script to test the stack before deployment
3. **`SWARM_DEPLOYMENT_README.md`** - This documentation file

## ✅ Validation Results

The validation script has confirmed:
- ✅ YAML syntax is valid
- ✅ All 6 services configured (willowcms, mysql, redis, phpmyadmin, mailpit, redis-commander)
- ✅ All services have deploy configurations for Swarm
- ✅ Overlay network properly configured
- ✅ All required volumes defined
- ✅ No build directives (Swarm compatible)
- ✅ No hardcoded secrets
- ⚠️  Note: `MYSQL_ROOT_PASSWORD` should be added to `stack.env`

## 🚀 Quick Start

### Step 1: Validate the Stack

```bash
# Run the validation script
./validate-stack.sh
```

### Step 2: Initialize Docker Swarm (if not already done)

```bash
# Initialize Swarm mode
docker swarm init

# Verify Swarm is active
docker info | grep Swarm
```

### Step 3: Deploy the Stack

```bash
# Deploy to Docker Swarm
docker stack deploy -c docker-compose-stack.yml willowcms-swarm-test
```

### Step 4: Monitor Deployment

```bash
# Check stack services
docker stack services willowcms-swarm-test

# View service status
docker stack ps willowcms-swarm-test

# Monitor logs for specific service
docker service logs willowcms-swarm-test_willowcms
docker service logs willowcms-swarm-test_mysql
docker service logs willowcms-swarm-test_redis
```

## 🔧 Management Commands

### Scaling Services

```bash
# Scale the willowcms service (if needed)
docker service scale willowcms-swarm-test_willowcms=2

# Scale redis-commander
docker service scale willowcms-swarm-test_redis-commander=2
```

### Updating Services

```bash
# Update a service image
docker service update --image willowcms:new-tag willowcms-swarm-test_willowcms

# Force update (pull latest image)
docker service update --force willowcms-swarm-test_willowcms
```

### Inspecting Services

```bash
# Inspect service details
docker service inspect willowcms-swarm-test_willowcms

# View service logs (last 100 lines)
docker service logs --tail=100 willowcms-swarm-test_willowcms

# Follow logs in real-time
docker service logs -f willowcms-swarm-test_willowcms
```

## 🛑 Stopping and Removing

### Remove the Stack

```bash
# Remove the entire stack
docker stack rm willowcms-swarm-test

# Verify removal
docker stack ls
```

### Leave Swarm Mode (if needed)

```bash
# Leave swarm mode (WARNING: removes all swarm services)
docker swarm leave --force
```

## 📊 Service Configuration

### Services Included

| Service | Port | Purpose |
|---------|------|---------|
| willowcms | 8080 | Main WillowCMS application |
| mysql | 3310 | Database server |
| redis | - | Cache and session storage |
| phpmyadmin | 8082 | Database management interface |
| mailpit | 1125 (SMTP), 8025 (HTTP) | Email testing server |
| redis-commander | 8084 | Redis management interface |

### Resource Limits

Each service has been configured with resource limits:

- **willowcms**: 2 CPU / 2GB RAM (max), 0.5 CPU / 512MB RAM (reserved)
- **mysql**: 2 CPU / 2GB RAM (max), 0.5 CPU / 512MB RAM (reserved)
- **redis**: 1 CPU / 512MB RAM (max), 0.25 CPU / 128MB RAM (reserved)
- **phpmyadmin**: 0.5 CPU / 512MB RAM (max), 0.1 CPU / 128MB RAM (reserved)
- **mailpit**: 0.5 CPU / 256MB RAM (max), 0.1 CPU / 64MB RAM (reserved)
- **redis-commander**: 0.5 CPU / 256MB RAM (max), 0.1 CPU / 64MB RAM (reserved)

## 🌐 Network Configuration

- **Driver**: overlay (encrypted)
- **Name**: `${NETWORK_NAME:-willowcms_network}`
- **Attachable**: true (allows external containers to connect)

## 💾 Volumes

All volumes use local driver with configurable names:

- `willowcms_mysql_data` - MySQL database data
- `willowcms_mysql_logs` - MySQL logs
- `willowcms_redis_data` - Redis persistence data
- `willowcms_mailpit_data` - Mailpit email storage
- `willowcms_mailpit_logs` - Mailpit logs
- `willowcms_app_data` - Application code
- `willowcms_logs` - Application logs
- `willowcms_nginx_logs` - Nginx web server logs
- `willowcms_storage` - Temporary storage

## 🔐 Environment Variables

All environment variables should be configured in `stack.env`. Critical variables include:

- `MYSQL_ROOT_PASSWORD`
- `MYSQL_PASSWORD`
- `MYSQL_USER`
- `MYSQL_DATABASE`
- `REDIS_PASSWORD`
- `REDIS_USERNAME`
- `SECURITY_SALT`
- `WILLOW_ADMIN_PASSWORD`

## 🐝 Docker Swarm Specifics

### Key Differences from Docker Compose

1. **No `build` directives** - Must use pre-built images
2. **No `depends_on` conditions** - Services start in parallel
3. **`deploy` sections** - Replicas, resources, and update strategies
4. **Overlay network** - Required for inter-service communication
5. **Placement constraints** - Stateful services (MySQL, Redis) run on manager nodes

### Deploy Configuration Features

- **Replicas**: Number of service instances
- **Update config**: Rolling update strategy
- **Restart policy**: Automatic restart on failure
- **Resource reservations**: Guaranteed resources
- **Resource limits**: Maximum resource usage
- **Placement constraints**: Node requirements

## 🔍 Troubleshooting

### Service Not Starting

```bash
# Check service status
docker service ps willowcms-swarm-test_willowcms --no-trunc

# View error logs
docker service logs willowcms-swarm-test_willowcms | tail -50
```

### Network Issues

```bash
# Inspect overlay network
docker network inspect willowcms_network

# Check if service is connected to network
docker service inspect willowcms-swarm-test_willowcms | grep Networks -A 5
```

### Volume Issues

```bash
# List volumes
docker volume ls | grep willowcms

# Inspect specific volume
docker volume inspect willowcms_mysql_data
```

### Rolling Back Updates

```bash
# Rollback service to previous version
docker service rollback willowcms-swarm-test_willowcms
```

## 📝 Best Practices

1. **Always validate** before deploying with `./validate-stack.sh`
2. **Test in staging** environment first
3. **Use specific image tags** instead of `latest` in production
4. **Monitor resource usage** regularly
5. **Backup volumes** before major updates
6. **Keep `stack.env` secure** and never commit to version control
7. **Review logs** after deployment to catch issues early

## 🔄 Continuous Deployment with Portainer

### Using Portainer Git Repository Option

1. **Connect repository** in Portainer Stacks section
2. **Set Git reference** (branch/tag)
3. **Configure environment variables** from `stack.env`
4. **Enable automatic updates** (optional)
5. **Set webhook** for CI/CD triggers

### Portainer Stack Configuration

- **Repository**: `https://github.com/Robjects-Community/WhatIsMyAdaptor.git`
- **Reference**: `main-clean` or `portainer-stack`
- **Compose file**: `docker-compose-stack.yml`
- **Environment variables**: Load from `stack.env`

## 📚 Additional Resources

- [Docker Swarm Documentation](https://docs.docker.com/engine/swarm/)
- [Docker Stack Deploy Reference](https://docs.docker.com/engine/reference/commandline/stack_deploy/)
- [Portainer Documentation](https://docs.portainer.io/)
- [WillowCMS Documentation](../README.md)

## 🆘 Support

If you encounter issues:

1. Run `./validate-stack.sh` to check configuration
2. Check service logs with `docker service logs`
3. Verify environment variables in `stack.env`
4. Consult the main `WARP.md` development guide
5. Review `VERIFICATION_CHECKLIST.md` for health checks

---

**Created**: October 6, 2025  
**Last Updated**: October 6, 2025  
**WillowCMS Version**: Latest  
**Docker Compose Version**: 3.8 (Swarm compatible)
