# 🚀 WillowCMS Portainer Stack

## 📚 Documentation Index

Choose your deployment method:

This directory contains everything needed to deploy WillowCMS using Portainer with Git-based stack deployment.

## Quick Start

### 1. Deploy via Portainer Web Interface

1. **Login to Portainer** and navigate to **Stacks**
2. **Click "Add stack"**
3. **Choose "Repository" as the build method**
4. **Configure the repository:**
   - **Repository URL**: `https://github.com/garzarobm/willow.git`
   - **Repository reference**: `droplet-deploy`
   - **Compose path**: `portainer-stacks/docker-compose.yml`
5. **Set environment variables** (see Environment Variables section below)
6. **Deploy the stack**

### 2. Deploy via Portainer CLI/API

```bash
# Create stack via Portainer API
curl -X POST \
  http://your-portainer-url:9000/api/stacks \
  -H 'Authorization: Bearer YOUR-API-TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "name": "willowcms",
    "repositoryURL": "https://github.com/garzarobm/willow.git",
    "repositoryReferenceName": "droplet-deploy",
    "composeFilePathInRepository": "portainer-stacks/docker-compose.yml",
    "repositoryAuthentication": false,
    "env": [
      {"name": "MYSQL_ROOT_PASSWORD", "value": "your-secure-password"},
      {"name": "SECURITY_SALT", "value": "your-32-character-salt"}
    ]
  }'
```

## Environment Variables

### Required Variables
You **MUST** set these environment variables in Portainer:

```bash
# Security (CRITICAL - CHANGE THESE!)
SECURITY_SALT=your-secure-32-character-salt-here
MYSQL_ROOT_PASSWORD=secure-root-password
MYSQL_PASSWORD=secure-db-password
REDIS_PASSWORD=secure-redis-password
WILLOW_ADMIN_PASSWORD=secure-admin-password

# Application URL
APP_FULL_BASE_URL=https://your-domain.com

# Admin User
WILLOW_ADMIN_EMAIL=admin@your-domain.com
```

### Optional Variables (with defaults)
```bash
# Application
APP_NAME=WillowCMS
DEBUG=false
APP_DEFAULT_TIMEZONE=America/Chicago

# Database
MYSQL_DATABASE=willow_cms
MYSQL_USER=willow_user

# Services
WILLOW_HTTP_PORT=8080
MYSQL_PORT=3310
PMA_HTTP_PORT=8082

# API Keys (if using these features)
OPENAI_API_KEY=your-openai-key
YOUTUBE_API_KEY=your-youtube-key
TRANSLATE_API_KEY=your-translate-key
```

## Service Access

After deployment, access your services:

- **WillowCMS Application**: `http://your-server:8080`
- **phpMyAdmin**: `http://your-server:8082`
- **Mailpit (Email Testing)**: `http://your-server:8025`
- **Redis Commander**: `http://your-server:8084`

## Stack Services

### Core Application Stack
- **willowcms**: Main PHP/CakePHP application
- **mysql**: MySQL 8.0 database
- **redis**: Redis cache and session storage
- **phpmyadmin**: Database administration interface
- **mailpit**: SMTP testing and email preview
- **redis-commander**: Redis management interface

### Volumes
- `mysql_data`: Persistent MySQL database storage
- `redis_data`: Persistent Redis data
- `mailpit_data`: Email storage
- `willow_app_data`: Application data
- `willow_logs`: Application logs
- `willow_nginx_logs`: Web server logs

### Networks
- `willow_network`: Internal bridge network for service communication

## Security Considerations

### 🔐 Critical Security Settings

1. **Change Default Passwords**: Never use default passwords in production
2. **Use Strong Passwords**: Generate secure random passwords for all services
3. **Security Salt**: Generate a unique 32+ character salt for CakePHP
4. **Environment Variables**: Store sensitive data as environment variables, not in files
5. **Network Security**: Use proper firewall rules and network policies

### 🚨 Required Password Changes

```bash
# Generate secure passwords for:
SECURITY_SALT=               # 32+ character random string
MYSQL_ROOT_PASSWORD=         # Strong MySQL root password
MYSQL_PASSWORD=              # Strong MySQL user password  
REDIS_PASSWORD=              # Strong Redis password
WILLOW_ADMIN_PASSWORD=       # Strong admin password
REDIS_COMMANDER_PASSWORD=    # Strong Redis Commander password
```

### 🔧 Production Recommendations

1. **Use HTTPS**: Configure reverse proxy with SSL/TLS
2. **Restrict Ports**: Only expose necessary ports publicly
3. **Regular Updates**: Keep images updated with latest security patches
4. **Backup Strategy**: Regular database and volume backups
5. **Monitoring**: Implement logging and monitoring solutions

## Deployment Variations

### Development Stack
For development, you can use relaxed security settings:
```bash
DEBUG=true
APP_FULL_BASE_URL=http://localhost:8080
```

### Production Stack
For production, ensure:
```bash
DEBUG=false
APP_FULL_BASE_URL=https://your-domain.com
# Use strong passwords for all services
# Configure proper SSL/TLS termination
```

## Troubleshooting

### Common Issues

1. **Build Failures**
   ```bash
   # Check if the GitHub repository is accessible
   # Verify the branch name (droplet-deploy)
   # Check Docker build context permissions
   ```

2. **Database Connection Issues**
   ```bash
   # Verify MySQL credentials
   # Check if MySQL service is healthy
   # Validate network connectivity
   ```

3. **Redis Connection Issues**
   ```bash
   # Verify Redis password
   # Check Redis health status
   # Validate Redis URL format
   ```

4. **Application Startup Issues**
   ```bash
   # Check application logs in Portainer
   # Verify all required environment variables are set
   # Ensure security salt is configured
   ```

### Log Access

View logs in Portainer:
1. Navigate to **Containers**
2. Click on the problematic container
3. Click **Logs** tab
4. Check for error messages

### Health Checks

The stack includes health checks for critical services:
- Redis: Ping test with authentication
- Application dependencies are properly configured

## Backup and Restoration

### Database Backup
```bash
# Via phpMyAdmin: Use export functionality
# Via CLI: docker exec mysql-container mysqldump...
```

### Volume Backup
```bash
# Backup named volumes using Portainer or Docker commands
docker run --rm -v willow_mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql-backup.tar.gz -C /data .
```

## Updates and Maintenance

### Stack Updates
1. **Update the Git repository** with new changes
2. **Pull latest changes** in Portainer
3. **Redeploy the stack**
4. **Verify services** are running correctly

### Image Updates
- Images will be pulled automatically when stack is updated
- For manual updates, redeploy the stack in Portainer

## Support

For issues and questions:
- **Repository Issues**: GitHub Issues
- **CakePHP Documentation**: https://book.cakephp.org/
- **Portainer Documentation**: https://documentation.portainer.io/

---

**Note**: This stack is designed for Portainer deployment with Git repository integration. Ensure your Portainer instance has access to the GitHub repository and proper permissions to pull the source code.
---

## 📝 **Important: File Location**

### **Primary Deployment File:**
The main cloud deployment file is located at the **repository root**:
- **Location:** `docker-compose-cloud.yml` (root directory)
- **Reason:** Simpler path for Portainer, better Docker Compose context resolution

### **Portainer Path:**
```
Compose path: docker-compose-cloud.yml
```

### **Backup Copy:**
A copy is maintained in this directory (`portainer-stacks/docker-compose-cloud.yml`) for:
- Documentation purposes
- Keeping Portainer-related files organized
- Reference and backup

**Note:** If updating the deployment configuration, update both locations.

