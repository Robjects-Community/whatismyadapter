# WillowCMS Portainer Deployment

Deploy WillowCMS stack using Portainer's repository import feature on Docker Swarm.

## 🎯 Quick Start

### 1. Repository Import in Portainer

```
Repository URL: https://github.com/your-username/WhatIsMyAdaptor
Branch: feat/dev_portainer  
Compose File: docker-compose.portainer.yml
```

### 2. Required Environment Variables

Copy from `.env.portainer.template` and update:
- `SECURITY_SALT` - Your secure salt (32+ chars)
- `MYSQL_ROOT_PASSWORD` - MySQL root password
- `MYSQL_PASSWORD` - Database password
- `REDIS_PASSWORD` - Redis password  
- `WILLOW_ADMIN_PASSWORD` - Admin password
- `APP_FULL_BASE_URL` - Your domain URL

### 3. Deploy Stack

Click "Deploy the stack" in Portainer.

## 🌐 Service Access

After deployment, access services at:

- **WillowCMS**: http://localhost:7870
- **Admin Panel**: http://localhost:7870/admin
- **phpMyAdmin**: http://localhost:7871  
- **Jenkins**: http://localhost:7872
- **Mailpit**: http://localhost:7873
- **Redis Commander**: http://localhost:7874

## 📋 What's Included

- **WillowCMS**: `robjects/whatismyadapter_cms:portainer-swarm-build`
- **Jenkins**: `robjects/whatismyadapter_jenkins:portainer-swarm-build`
- **MySQL 8.4.3**: Database
- **Redis 7**: Cache & queues
- **phpMyAdmin**: Database management
- **Mailpit**: Email testing
- **Redis Commander**: Redis management

## 🔧 Features

- ✅ **Docker Hub Images**: No local build required
- ✅ **Health Checks**: Built-in service monitoring
- ✅ **Resource Limits**: Optimized for production
- ✅ **Persistent Storage**: Data survives restarts
- ✅ **Zero Downtime**: Rolling updates supported
- ✅ **Secure**: Isolated overlay network

## 📚 Documentation

- **Full Guide**: See `PORTAINER_DEPLOYMENT.md`
- **Environment**: See `.env.portainer.template`
- **Local Development**: See `README.md`

## 🚨 Important Notes

1. **Change Default Passwords**: Update all password placeholders
2. **Resource Requirements**: Ensure sufficient CPU/RAM available
3. **Port Conflicts**: Check ports 7810-7876 are available
4. **Docker Swarm**: Must be initialized before deployment

## 🆘 Quick Troubleshooting

- **Services won't start**: Check resource limits and environment variables
- **Connection errors**: Verify Docker Swarm is active
- **Port conflicts**: Update port mappings in environment variables

For detailed troubleshooting, see `PORTAINER_DEPLOYMENT.md`.
