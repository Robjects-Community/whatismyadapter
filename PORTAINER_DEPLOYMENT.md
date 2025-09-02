# Portainer Deployment Guide

This guide explains how to deploy the WillowCMS stack using Portainer's repository import feature.

## 🚀 Quick Deployment

### Method 1: Repository Import (Recommended)

1. **Access Portainer**:
   - Open your Portainer instance
   - Navigate to **Stacks** section

2. **Create New Stack**:
   - Click **"Add stack"**
   - Select **"Repository"** tab

3. **Repository Configuration**:
   ```
   Repository URL: https://github.com/your-username/your-repo-name
   Repository reference: feat/dev_portainer
   Compose path: docker-compose.portainer.yml
   ```

4. **Environment Variables**:
   - Copy from `.env.portainer.template`
   - Update all placeholder values with your production credentials
   - Add to Portainer's environment variables section

5. **Deploy**:
   - Click **"Deploy the stack"**
   - Wait for services to start

### Method 2: Direct Compose Import

1. **Copy Content**:
   - Copy the entire content of `docker-compose.portainer.yml`

2. **Create Stack**:
   - In Portainer, go to **Stacks** > **Add stack**
   - Select **"Web editor"** tab
   - Paste the compose content

3. **Configure Environment**:
   - Add environment variables in the **Environment variables** section
   - Use the template from `.env.portainer.template`

## 🔧 Configuration

### Required Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `SECURITY_SALT` | CakePHP security salt (32+ chars) | `your-production-salt-here` |
| `MYSQL_ROOT_PASSWORD` | MySQL root password | `SecureRootPass123!` |
| `MYSQL_PASSWORD` | CMS database password | `SecureCmsPass123!` |
| `REDIS_PASSWORD` | Redis authentication password | `SecureRedisPass123!` |
| `WILLOW_ADMIN_PASSWORD` | CMS admin password | `SecureAdminPass123!` |
| `APP_FULL_BASE_URL` | Full URL to your application | `https://cms.yourdomain.com` |

### Service Ports

The stack uses the following ports (configurable):

| Service | Port | Purpose |
|---------|------|---------|
| WillowCMS | 7870 | Main application |
| MySQL | 7810 | Database access |
| phpMyAdmin | 7871 | Database management |
| Jenkins | 7872 | CI/CD pipeline |
| Mailpit SMTP | 7825 | Email testing (SMTP) |
| Mailpit UI | 7873 | Email testing (Web UI) |
| Redis | 7876 | Cache/Queue service |
| Redis Commander | 7874 | Redis management |

## 🔐 Security Best Practices

### 1. Change Default Passwords
- Update all password placeholders in the environment variables
- Use strong, unique passwords for each service
- Consider using Docker secrets for sensitive data

### 2. Network Security
- The stack creates an isolated overlay network
- Services communicate internally via service names
- Only necessary ports are exposed to the host

### 3. Resource Limits
- Default resource limits are set for development
- Adjust CPU and memory limits based on your server capacity
- Monitor resource usage and scale as needed

## 🌐 DNS and Load Balancing

### Nginx Reverse Proxy Example
```nginx
server {
    listen 80;
    server_name cms.yourdomain.com;
    
    location / {
        proxy_pass http://localhost:7870;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Traefik Labels (Alternative)
Add these labels to the willowcms service for automatic Traefik routing:
```yaml
labels:
  - "traefik.enable=true"
  - "traefik.http.routers.willowcms.rule=Host(`cms.yourdomain.com`)"
  - "traefik.http.services.willowcms.loadbalancer.server.port=80"
```

## 📊 Monitoring & Health Checks

### Built-in Health Checks
- **WillowCMS**: HTTP health check on port 80
- **MySQL**: mysqladmin ping check
- **Redis**: Redis ping command

### Monitoring Stack Services
```bash
# Check service status
docker service ls

# View service logs
docker service logs willow_willowcms

# Scale services
docker service scale willow_willowcms=3
```

## 🔄 Updates & Rollbacks

### Rolling Updates
Portainer supports zero-downtime updates:
1. Update the Docker image tags in the compose file
2. Redeploy the stack
3. Services will update with rolling deployment

### Rollback Procedure
1. In Portainer, go to the stack
2. Click on a previous deployment
3. Click **"Redeploy"**

## 🗂️ Volume Management

### Persistent Data
The following volumes persist data:
- `mysql_data` - Database files
- `jenkins_home` - Jenkins configuration and jobs
- `mailpit_data` - Email storage
- `redis_data` - Redis persistence
- `willowcms_logs` - Application logs

### Backup Strategy
```bash
# Backup database
docker exec $(docker ps -q -f name=willow_mysql) \
  mysqldump -u root -p cms > backup_$(date +%Y%m%d).sql

# Backup Jenkins
docker run --rm -v jenkins_home:/data -v $(pwd):/backup \
  busybox tar czf /backup/jenkins_backup_$(date +%Y%m%d).tar.gz /data
```

## 🚨 Troubleshooting

### Common Issues

1. **Services won't start**:
   - Check resource limits vs available system resources
   - Verify all environment variables are set
   - Check Docker Swarm is initialized

2. **Database connection errors**:
   - Ensure MySQL service is running
   - Verify database credentials
   - Check network connectivity

3. **Port conflicts**:
   - Update port mappings in environment variables
   - Ensure no other services are using the same ports

### Debug Commands
```bash
# Check service status
docker service ps willow_willowcms --no-trunc

# View detailed service logs
docker service logs willow_willowcms --tail 50

# Inspect service configuration
docker service inspect willow_willowcms
```

## 📈 Scaling

### Horizontal Scaling
Update replica counts for services that support scaling:
- WillowCMS: Can scale to multiple replicas
- MySQL: Keep at 1 replica (single master)
- Redis: Keep at 1 replica (or configure cluster)

### Vertical Scaling
Adjust resource limits:
```yaml
resources:
  limits:
    cpus: '2.0'      # Increase CPU limit
    memory: 2G       # Increase memory limit
```

## 🔍 Monitoring URLs

After deployment, access your services:

- **WillowCMS**: http://your-server:7870
- **Admin Panel**: http://your-server:7870/admin
- **phpMyAdmin**: http://your-server:7871
- **Jenkins**: http://your-server:7872
- **Mailpit**: http://your-server:7873
- **Redis Commander**: http://your-server:7874

## 📋 Pre-deployment Checklist

- [ ] Docker Swarm is initialized
- [ ] Portainer is running and accessible
- [ ] All environment variables are configured with secure values
- [ ] Required ports are available on the host
- [ ] Sufficient system resources are available
- [ ] Backup strategy is in place
- [ ] DNS/Load balancer is configured (if needed)
- [ ] Monitoring solution is set up
- [ ] Firewall rules are configured

## 🆘 Support

For issues related to:
- **Portainer**: Check Portainer documentation
- **Docker Swarm**: Refer to Docker Swarm docs
- **WillowCMS**: Check application logs and configuration

### Useful Resources
- [Portainer Documentation](https://docs.portainer.io/)
- [Docker Swarm Documentation](https://docs.docker.com/engine/swarm/)
- [Docker Compose File Reference](https://docs.docker.com/compose/compose-file/)
