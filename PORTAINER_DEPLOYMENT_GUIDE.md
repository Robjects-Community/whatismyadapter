# Portainer Deployment Guide for WhatIsMyAdaptor

✅ **VERIFIED WORKING** - This repository can be successfully deployed via Portainer!

## 🎯 Quick Start

### Repository Information
- **Repository URL**: `https://github.com/Robjects-Community/WhatIsMyAdaptor.git`
- **Branch**: `dev_portainer_swarm`
- **Compose File**: `docker-compose.portainer.yml`

### Pre-built Images Available
- **WillowCMS**: `robjects/whatismyadapter_cms:portainer-swarm-build`
- **Jenkins**: `robjects/whatismyadapter_jenkins:portainer-swarm-build`

## 📋 Step-by-Step Portainer Deployment

### Step 1: Access Portainer
1. Open your Portainer instance
2. Navigate to **Stacks** section
3. Click **Add stack**

### Step 2: Repository Import
1. Select **Repository** as the build method
2. Fill in the repository details:
   ```
   Repository URL: https://github.com/Robjects-Community/WhatIsMyAdaptor.git
   Repository reference: dev_portainer_swarm
   Compose path: docker-compose.portainer.yml
   ```

### Step 3: Environment Configuration
Copy the template below and customize your values:

```env
# Security Configuration (REQUIRED - Change these!)
SECURITY_SALT=your-production-security-salt-here-minimum-32-characters-long
WILLOW_ADMIN_USERNAME=admin
WILLOW_ADMIN_PASSWORD=your-secure-admin-password

# Database Configuration (REQUIRED - Change these!)
MYSQL_ROOT_PASSWORD=your-mysql-root-password
MYSQL_PASSWORD=your-secure-mysql-password

# Redis Configuration (REQUIRED - Change this!)
REDIS_PASSWORD=your-redis-password

# Email Configuration (Optional)
EMAIL_REPLY=admin@yourdomain.com
EMAIL_NOREPLY=noreply@yourdomain.com
WILLOW_ADMIN_EMAIL=admin@yourdomain.com

# Application Configuration
APP_FULL_BASE_URL=http://your-domain.com:7870
DEBUG=false

# Port Configuration (Optional - adjust if needed)
WILLOWCMS_PORT=7870
MYSQL_PORT=7810
PHPMYADMIN_PORT=7871
JENKINS_PORT=7872
MAILPIT_SMTP_PORT=7825
MAILPIT_UI_PORT=7873
REDIS_PORT=7876
REDIS_COMMANDER_PORT=7874
```

### Step 4: Deploy
1. Paste your customized environment variables
2. Click **Deploy the stack**
3. Wait for all services to start (may take 2-3 minutes)

## 🌐 Service Access Points

After successful deployment, access these services:

| Service | URL | Purpose | Default Login |
|---------|-----|---------|---------------|
| **WillowCMS** | http://localhost:7870 | Main application | - |
| **Admin Panel** | http://localhost:7870/admin | CMS administration | admin@yourdomain.com |
| **phpMyAdmin** | http://localhost:7871 | Database management | root / your-mysql-root-password |
| **Jenkins** | http://localhost:7872 | CI/CD pipeline | admin / admin |
| **Mailpit** | http://localhost:7873 | Email testing | - |
| **Redis Commander** | http://localhost:7874 | Redis management | admin / your-admin-password |

## ✅ Verification Steps

### 1. Check Service Health
```bash
# Check all services are running
docker service ls

# Verify specific service health
docker service ps willow_willowcms
```

### 2. Test Application
```bash
# Check if main app responds
curl -I http://localhost:7870

# Should return HTTP 200 or 302 (redirect)
```

### 3. Access Admin Panel
1. Navigate to http://localhost:7870/admin
2. Login with your configured admin credentials
3. Verify the dashboard loads correctly

## 🔧 Architecture Features

### Docker Swarm Optimized
- **Replicas**: 2x WillowCMS instances for high availability
- **Health Checks**: All services include health monitoring
- **Rolling Updates**: Zero-downtime deployments
- **Resource Limits**: Optimized memory and CPU allocation
- **Persistent Storage**: Data survives container restarts

### Network Security
- **Isolated Network**: Services communicate via overlay network
- **External Ports**: Only necessary ports exposed externally
- **Environment Variables**: Sensitive data via environment variables

### Service Dependencies
```
willowcms → mysql, redis, mailpit
phpmyadmin → mysql
redis-commander → redis
jenkins → (independent)
```

## 🚨 Important Security Notes

1. **Change Default Passwords**: Never use the template passwords in production
2. **Security Salt**: Use a unique 32+ character security salt
3. **Database Passwords**: Use strong, unique passwords for MySQL
4. **Redis Password**: Set a strong Redis password
5. **Admin Credentials**: Use secure admin credentials

## 📊 Resource Requirements

### Minimum Requirements
- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 10GB free space
- **Network**: Docker Swarm initialized

### Recommended Production
- **CPU**: 4+ cores
- **RAM**: 8GB+
- **Storage**: 50GB+ SSD
- **Network**: High-speed internet connection

## 🔍 Troubleshooting

### Common Issues and Solutions

#### Services Won't Start
```bash
# Check service logs
docker service logs willow_willowcms

# Check service status
docker service ps willow_willowcms --no-trunc
```

#### Port Conflicts
```bash
# Check what's using the port
lsof -i:7870

# Update port in environment variables if needed
```

#### Database Connection Issues
```bash
# Check MySQL service
docker service logs willow_mysql

# Verify database credentials in environment variables
```

#### Memory Issues
```bash
# Check container resource usage
docker stats

# Adjust resource limits in docker-compose.portainer.yml if needed
```

### Log Analysis
```bash
# View all stack logs
docker service logs willow_willowcms
docker service logs willow_mysql
docker service logs willow_redis

# Follow logs in real-time
docker service logs -f willow_willowcms
```

## 🔄 Updates and Maintenance

### Updating the Application
1. In Portainer, go to your stack
2. Click **Editor**
3. Pull latest changes from repository
4. Click **Update the stack**
5. Swarm will perform rolling updates automatically

### Backup Procedures
```bash
# Backup database
docker exec -i $(docker ps -q -f name=willow_mysql) \
    mysqldump -u root -p[PASSWORD] cms > backup.sql

# Backup volumes
docker run --rm -v willow_mysql_data:/data -v $(pwd):/backup \
    alpine tar czf /backup/mysql_backup.tar.gz -C /data .
```

## 🌍 Production Considerations

### Domain Configuration
1. Update `APP_FULL_BASE_URL` with your actual domain
2. Configure reverse proxy (nginx/traefik) if needed
3. Set up SSL certificates for HTTPS

### Performance Tuning
1. Adjust resource limits based on usage
2. Configure database connection pooling
3. Enable Redis for session storage and caching
4. Set up log rotation and monitoring

### Monitoring and Alerting
1. Configure health check endpoints
2. Set up monitoring with Prometheus/Grafana
3. Configure log aggregation (ELK stack)
4. Set up alerting for service failures

## 📚 Additional Resources

- **Repository**: https://github.com/Robjects-Community/WhatIsMyAdaptor
- **Docker Images**: https://hub.docker.com/u/robjects
- **Issue Tracking**: GitHub Issues in the repository
- **Documentation**: See repository README.md files

---

## ✨ Success!

Your WhatIsMyAdaptor application is now running via Portainer with:
- ✅ High availability (2 replicas)
- ✅ Health monitoring
- ✅ Persistent data storage
- ✅ Zero-downtime updates
- ✅ Secure network isolation
- ✅ Resource optimization

**Next Steps**: Access your application at http://localhost:7870 and begin using WillowCMS!
