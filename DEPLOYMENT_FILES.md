# 📁 Deployment Files Overview

This repository contains two main deployment files for different scenarios:

## 🐳 Docker Compose Files

### `docker-compose.yml` - **Main Deployment File** ✅
- **Use for**: Portainer (Standalone), Docker Compose, Local Development
- **Networking**: Bridge driver (works everywhere)
- **Features**: All services, health checks, environment variables
- **Recommended**: ✅ **Most users should use this file**

**Portainer Settings:**
```
Compose path: docker-compose.yml
```

### `docker-swarm-stack.yml` - **Advanced Swarm Deployment**
- **Use for**: Docker Swarm clusters, Production with HA requirements
- **Networking**: Overlay driver with encryption
- **Features**: Replicas, resource limits, rolling updates, placement constraints
- **Requirements**: Docker Swarm initialized (`docker swarm init`)
- **Recommended**: For advanced users with Swarm clusters

**Portainer Settings:**
```
Compose path: docker-swarm-stack.yml
```

## 📋 Environment Configuration

### `.env.portainer.example`
- Template for all required environment variables
- Security guidelines and password generation examples
- Copy values into Portainer's environment variable section

## 🚀 Quick Start

### For Most Users (Recommended)
```
Repository: https://github.com/Robjects-Community/WhatIsMyAdaptor.git
Branch: dev_portainer_swarm
Compose path: docker-compose.yml
```

### For Docker Swarm Users (Advanced)
```
Repository: https://github.com/Robjects-Community/WhatIsMyAdaptor.git  
Branch: dev_portainer_swarm
Compose path: docker-swarm-stack.yml
```

## 🔧 Services Included

Both files include the same services:
- **WillowCMS** (CakePHP 5.x application) - Port 8080
- **MySQL 8.4.3** (Database) - Port 3310
- **phpMyAdmin** (Database management) - Port 8082
- **Jenkins** (CI/CD) - Port 8081
- **Mailpit** (Email testing) - Port 8025
- **Redis** (Cache) - Port 6379
- **Redis Commander** (Cache management) - Port 8084

## 📖 Documentation

- **Complete Guide**: [PORTAINER_ONLINE_DEPLOYMENT.md](PORTAINER_ONLINE_DEPLOYMENT.md)
- **Quick Reference**: [DEPLOYMENT_READY.md](DEPLOYMENT_READY.md)
- **Environment Template**: [.env.portainer.example](.env.portainer.example)

---

**💡 Tip**: Start with `docker-compose.yml` - it works in all environments and is easier to troubleshoot.
