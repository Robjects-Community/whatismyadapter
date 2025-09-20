# WillowCMS Verification Checklist

This comprehensive checklist ensures all WillowCMS services are running correctly and provides troubleshooting steps for common issues.

## 🔍 Service Health Checks

### Core Application Services

#### WillowCMS Application
- **HTTP Check**: `curl http://localhost:8080`
- **Browser Check**: Navigate to `http://localhost:8080`
- **Expected Response**: WillowCMS homepage should load successfully
- **Troubleshooting**: If not accessible, check container status and logs

#### Admin Panel Access
- **URL**: `http://localhost:8080/admin`
- **Credentials**: `admin@test.com / password`
- **Expected Response**: Admin login page or dashboard if already logged in
- **Verification Steps**:
  1. Navigate to admin URL
  2. Enter credentials if prompted
  3. Verify dashboard loads with navigation menu
  4. Test basic admin functions (view articles, settings, etc.)

### Database Services

#### MySQL Database
```bash
# Direct database connection test
docker-compose exec mysql mysql -u cms_user -ppassword -e "SELECT 1"

# Alternative connection methods
docker-compose exec mysql mysql -u cms_user -ppassword -e "SHOW DATABASES;"
docker-compose exec mysql mysql -u cms_user -ppassword -e "USE willow_cms; SHOW TABLES;"

# Test CMS database connectivity
docker-compose exec mysql mysql -u cms_user -ppassword -e "SELECT COUNT(*) FROM willow_cms.settings;"
```
- **Expected Response**: Query results without connection errors
- **Troubleshooting**: Check MySQL container logs if connection fails

#### phpMyAdmin (Database Management)
- **URL**: `http://localhost:8082`
- **Credentials**: `root / password`
- **Alternative Login**: `cms_user / password`
- **Verification Steps**:
  1. Navigate to phpMyAdmin URL
  2. Login with root credentials
  3. Verify `willow_cms` database is visible
  4. Browse a few tables to ensure data integrity
  5. Test query execution in SQL tab

### Cache & Session Services

#### Redis Cache
```bash
# Basic Redis connectivity test
docker-compose exec redis redis-cli ping

# Test Redis functionality
docker-compose exec redis redis-cli set test_key "test_value"
docker-compose exec redis redis-cli get test_key
docker-compose exec redis redis-cli del test_key

# Check Redis info
docker-compose exec redis redis-cli info server
```
- **Expected Response**: `PONG` for ping command
- **Troubleshooting**: Check Redis container status and configuration

#### Redis Commander (Redis Management)
- **URL**: `http://localhost:8084`
- **Credentials**: `root / password`
- **Verification Steps**:
  1. Navigate to Redis Commander URL
  2. Login with credentials
  3. Browse Redis databases and keys
  4. Verify cache entries from WillowCMS operations

### Development & Communication Services

#### Mailpit (Email Testing)
- **URL**: `http://localhost:8025`
- **Purpose**: Catch all outgoing emails during development
- **Verification Steps**:
  1. Navigate to Mailpit interface
  2. Trigger an email from WillowCMS (password reset, contact form)
  3. Verify email appears in Mailpit inbox
  4. Test email viewing and search functionality

#### Jenkins (CI/CD - Optional)
- **URL**: `http://localhost:8081`
- **Note**: Only available if Jenkins service is enabled in docker-compose
- **Verification Steps**:
  1. Navigate to Jenkins URL
  2. Complete initial setup if first time
  3. Verify build jobs can be created and executed
  4. Test integration with WillowCMS repository

## 📊 Container Status Verification

### Container Health Overview
```bash
# Check all service status
docker-compose ps

# Check container health with detailed formatting
docker-compose ps --format "table {{.Name}}\t{{.State}}\t{{.Status}}\t{{.Ports}}"

# Filter for WillowCMS related containers only
docker ps -a | grep willow

# Check container resource usage
docker stats --no-stream
```

### Individual Container Status
```bash
# Check specific container status
docker-compose ps willowcms
docker-compose ps mysql
docker-compose ps redis
docker-compose ps phpmyadmin
docker-compose ps mailpit
docker-compose ps redis-commander

# Alternative approach using docker ps
docker ps --filter "name=willow"
docker ps --filter "name=mysql"
docker ps --filter "name=redis"
```

### Expected Container States
- **State**: All containers should be "Up" 
- **Status**: Should show healthy status and uptime
- **Ports**: Verify correct port mappings are active

## 📝 Log Verification

### Application Logs
```bash
# WillowCMS application logs
docker-compose logs willowcms

# Follow live logs (useful for troubleshooting)
docker-compose logs -f willowcms

# Show only recent logs (last 100 lines)
docker-compose logs --tail=100 willowcms

# Show logs with timestamps
docker-compose logs -t willowcms
```

### Database Logs
```bash
# MySQL database logs
docker-compose logs mysql

# Check for connection errors or warnings
docker-compose logs mysql | grep -i error
docker-compose logs mysql | grep -i warning

# Monitor MySQL slow queries
docker-compose logs mysql | grep -i slow
```

### Cache Service Logs
```bash
# Redis cache logs
docker-compose logs redis

# Check Redis for any connection issues
docker-compose logs redis | grep -i error

# Monitor Redis memory usage warnings
docker-compose logs redis | grep -i memory
```

### Combined Service Logs
```bash
# View logs from multiple services simultaneously
docker-compose logs willowcms mysql redis

# Follow all service logs in real-time
docker-compose logs -f

# Search for errors across all services
docker-compose logs | grep -i error
```

## 🌐 Network Connectivity

### Network Inspection
```bash
# List Docker networks
docker network ls | grep cms_default

# Inspect the CMS network
docker network inspect cms_default

# Check network connectivity between containers
docker-compose exec willowcms ping mysql
docker-compose exec willowcms ping redis
```

### Network Configuration Verification
```bash
# Check network settings in detail
docker network inspect cms_default --format='{{json .IPAM.Config}}'

# List all containers connected to the CMS network
docker network inspect cms_default --format='{{range .Containers}}{{.Name}} {{.IPv4Address}}{{end}}'

# Test internal DNS resolution
docker-compose exec willowcms nslookup mysql
docker-compose exec willowcms nslookup redis
```

### Port Accessibility Test
```bash
# Test port accessibility from host
nc -zv localhost 8080  # WillowCMS
nc -zv localhost 3306  # MySQL
nc -zv localhost 6379  # Redis
nc -zv localhost 8082  # phpMyAdmin
nc -zv localhost 8025  # Mailpit
nc -zv localhost 8084  # Redis Commander

# Alternative using telnet
telnet localhost 8080
telnet localhost 3306
```

## 🚀 Functional Verification Tests

### WillowCMS Application Tests
```bash
# Test WillowCMS CLI functionality
docker-compose exec willowcms bin/cake

# Run WillowCMS health check commands
docker-compose exec willowcms bin/cake migrations status
docker-compose exec willowcms bin/cake cache clear_all

# Test queue functionality (if implemented)
docker-compose exec willowcms bin/cake queue stats
```

### Database Functionality Tests
```bash
# Test database operations
docker-compose exec willowcms bin/cake migrations migrate
docker-compose exec willowcms bin/cake schema_cache clear

# Test data integrity
docker-compose exec mysql mysql -u cms_user -ppassword -e "SELECT COUNT(*) FROM willow_cms.users;"
docker-compose exec mysql mysql -u cms_user -ppassword -e "SELECT COUNT(*) FROM willow_cms.articles;"
```

### Cache Functionality Tests
```bash
# Test cache operations from WillowCMS
docker-compose exec willowcms bin/cake cache clear model
docker-compose exec willowcms bin/cake cache clear core

# Verify cache is working by checking Redis
docker-compose exec redis redis-cli keys "*"
```

## ⚠️ Troubleshooting Guide

### Common Issues and Solutions

#### WillowCMS Not Accessible
```bash
# Check if container is running
docker-compose ps willowcms

# If not running, start it
docker-compose up -d willowcms

# Check logs for errors
docker-compose logs willowcms

# Restart if necessary
docker-compose restart willowcms
```

#### Database Connection Issues
```bash
# Verify MySQL is running
docker-compose ps mysql

# Check MySQL logs for errors
docker-compose logs mysql

# Test connection manually
docker-compose exec willowcms ping mysql

# Restart MySQL if needed
docker-compose restart mysql
```

#### Performance Issues
```bash
# Check resource usage
docker stats --no-stream

# Check disk space
df -h

# Monitor container performance
docker-compose top

# Check for memory leaks
docker system df
```

#### Network Issues
```bash
# Recreate network if needed
docker-compose down
docker-compose up -d

# Prune unused networks
docker network prune

# Check firewall settings (Linux/Mac)
sudo netstat -tlnp | grep :8080
```

## ✅ Success Criteria

### All Systems Operational When:
- [ ] WillowCMS homepage loads at `http://localhost:8080`
- [ ] Admin panel accessible at `http://localhost:8080/admin`
- [ ] MySQL responds to connection tests
- [ ] Redis responds to PING commands
- [ ] phpMyAdmin interface loads and connects to database
- [ ] Mailpit interface captures and displays emails
- [ ] All containers show "Up" status in `docker-compose ps`
- [ ] No critical errors in any service logs
- [ ] Network connectivity verified between all services
- [ ] Basic CMS functionality tests pass

### Performance Benchmarks:
- Page load times under 2 seconds for homepage
- Database queries execute in under 100ms for simple operations
- Cache operations complete in under 10ms
- Container memory usage within expected limits

## 🔧 Maintenance Commands

### Regular Maintenance
```bash
# Update all containers
docker-compose pull
docker-compose up -d

# Clean up unused resources
docker system prune -f

# Backup database
docker-compose exec mysql mysqldump -u root -ppassword willow_cms > backup.sql

# Update WillowCMS
docker-compose exec willowcms composer update
docker-compose exec willowcms bin/cake migrations migrate
```

### Emergency Recovery
```bash
# Stop all services
docker-compose down

# Remove containers (keeps data)
docker-compose rm -f

# Rebuild and restart
docker-compose build --no-cache
docker-compose up -d

# Restore from backup if needed
docker-compose exec mysql mysql -u root -ppassword willow_cms < backup.sql
```

---

**Note**: This checklist should be run after any deployment, update, or when troubleshooting issues. Keep this document updated as new services are added or configurations change.

**Last Updated**: $(date)
**Environment**: Development/Docker Compose
**WillowCMS Version**: Latest
