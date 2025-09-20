# 🐳 Docker Compose Override Guide for Willow CMS

> **Complete guide for customizing your local Docker environment using docker-compose.override.yml**

## 📋 Overview

Docker Compose override files allow you to customize your development environment without modifying the main `docker-compose.yml` file. This is essential for:

- **Individual developer preferences**
- **Environment-specific configurations** 
- **Testing different setups**
- **Local customizations**

---

## 🚀 Quick Start

### Step 1: Create Override File

```bash
# Copy the example template
cp docker-compose.override.yml.example docker-compose.override.yml

# Edit to your needs
nano docker-compose.override.yml
```

### Step 2: Apply Changes

```bash
# Restart services to apply changes
docker compose down && docker compose up -d

# Or use the management tool
./manage.sh  # Option 17: Restart Docker Environment (Standard)
```

---

## 🔧 Common Use Cases

### 1. **Theme Development**

Mount your custom theme directory:

```yaml
services:
  willowcms:
    volumes:
      - ./my-custom-theme:/var/www/html/plugins/MyCustomTheme:cached
```

### 2. **Database Access**

Expose MySQL port for external tools:

```yaml
services:
  mysql:
    ports:
      - "3310:3306"  # Access via localhost:3310
```

### 3. **Port Conflicts**

Change default ports if they conflict:

```yaml
services:
  willowcms:
    ports:
      - "8081:80"  # Use port 8081 instead of 8080
  
  phpmyadmin:
    ports:
      - "8083:80"  # Use port 8083 instead of 8082
```

### 4. **API Development**

Add custom environment variables:

```yaml
services:
  willowcms:
    environment:
      - EXTERNAL_API_URL=http://localhost:3000
      - API_DEBUG_MODE=true
      - CUSTOM_SETTING=development_value
```

### 5. **Performance Testing**

Limit resources to simulate production:

```yaml
services:
  willowcms:
    deploy:
      resources:
        limits:
          cpus: '1.0'
          memory: 1G
        reservations:
          memory: 256M
```

---

## 🛡️ Best Practices

### Security

- ✅ Never commit `docker-compose.override.yml` to version control
- ✅ Add it to your `.gitignore` file
- ✅ Use strong passwords for any exposed services
- ✅ Only expose ports you actually need
- ❌ Never expose database ports in production

### Performance

- ✅ Use `cached` volume mounts for better performance on macOS/Windows
- ✅ Set appropriate resource limits
- ✅ Use specific image tags instead of `latest`
- ❌ Don't mount unnecessary volumes

### Maintenance

- ✅ Document your customizations
- ✅ Keep override file simple and focused
- ✅ Test changes before committing other code
- ✅ Share common patterns with your team via templates

---

## 🔍 Validation and Troubleshooting

### Validate Your Override

```bash
# Check if override is valid
docker compose config

# View final merged configuration
docker compose config --services

# Test without starting
docker compose config --dry-run
```

### Common Issues

#### **Issue: Port Already in Use**
```bash
# Find process using the port
sudo lsof -i :8080

# Kill the process or change your override port
```

#### **Issue: Volume Mount Permissions**
```bash
# Fix file permissions
sudo chown -R $(whoami):$(whoami) ./local-directory

# Or use specific user in override
services:
  willowcms:
    user: "1000:1000"  # Your user ID
```

#### **Issue: Environment Variables Not Applied**
```bash
# Recreate containers to pick up new environment
docker compose down
docker compose up -d

# Check if variables are set
docker compose exec willowcms env | grep YOUR_VARIABLE
```

---

## 📋 Example Scenarios

### Scenario 1: Multi-Developer Team

**Problem**: Multiple developers with port conflicts

**Solution**: Each developer uses different ports

```yaml
# Developer A uses ports 8080, 8082
services:
  willowcms:
    ports:
      - "8080:80"
  phpmyadmin:
    ports:
      - "8082:80"

# Developer B uses ports 8090, 8092  
services:
  willowcms:
    ports:
      - "8090:80"
  phpmyadmin:
    ports:
      - "8092:80"
```

### Scenario 2: Plugin Development

**Problem**: Need to develop and test custom plugins

**Solution**: Mount plugin directory

```yaml
services:
  willowcms:
    volumes:
      # Mount your plugin for development
      - ./src/MyPlugin:/var/www/html/plugins/MyPlugin:cached
      # Mount logs for debugging
      - ./plugin-logs:/var/www/html/logs:cached
    environment:
      - DEBUG=true
      - LOG_LEVEL=debug
```

### Scenario 3: Database Migration Testing

**Problem**: Need to test migrations with different data

**Solution**: Use separate database volume

```yaml
services:
  mysql:
    volumes:
      - migration_test_db:/var/lib/mysql
    environment:
      - MYSQL_DATABASE=migration_test

volumes:
  migration_test_db:
    driver: local
```

---

## 🧪 Testing Your Setup

### Basic Functionality Test

```bash
# 1. Check all services are running
docker compose ps

# 2. Test main application
curl -I http://localhost:8080

# 3. Test admin panel
curl -I http://localhost:8080/admin

# 4. Check logs for errors
docker compose logs --tail=50
```

### Performance Test

```bash
# Monitor resource usage
docker stats --no-stream

# Test response times
time curl -s http://localhost:8080 > /dev/null
```

### Environment Test

```bash
# Verify environment variables
docker compose exec willowcms env | grep -i debug

# Check volume mounts
docker compose exec willowcms ls -la /var/www/html/plugins/
```

---

## 📚 Advanced Configuration

### Custom Networks

```yaml
networks:
  frontend:
    driver: bridge
    ipam:
      config:
        - subnet: 172.20.0.0/16
  backend:
    driver: bridge

services:
  willowcms:
    networks:
      - frontend
      - backend
```

### Health Checks

```yaml
services:
  willowcms:
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s
```

### Secrets Management

```yaml
services:
  willowcms:
    environment:
      - ANTHROPIC_API_KEY_FILE=/run/secrets/anthropic_key
    secrets:
      - anthropic_key

secrets:
  anthropic_key:
    file: ./secrets/anthropic_key.txt
```

---

## 🚨 Troubleshooting Guide

### Quick Diagnostics

```bash
# Full environment restart
./manage.sh  # Option 17: Standard restart

# Check service status
docker compose ps

# View recent logs
docker compose logs --tail=100 --follow

# Validate configuration
docker compose config --quiet
```

### When Things Go Wrong

1. **Reset to default**: Remove override file temporarily
2. **Check syntax**: Validate YAML syntax online
3. **Incremental testing**: Add one change at a time
4. **Check logs**: Look for specific error messages
5. **Community help**: Share your override (without secrets) for help

### Getting Help

- Check the [Docker Restart Guide](docker-restart-guide.md)
- Review the [Developer Guide](../DeveloperGuide.md)
- Consult [Docker Compose Documentation](https://docs.docker.com/compose/)

---

## 📝 Template Checklist

Before using your override file:

- [ ] Copy from `.example` template
- [ ] Add `docker-compose.override.yml` to `.gitignore`
- [ ] Remove unused sections
- [ ] Test with `docker compose config`
- [ ] Document your changes
- [ ] Test functionality after applying
- [ ] Share patterns with team (via new templates)

---

\u003cdiv align=\"center\"\u003e
  \u003cstrong\u003e🐳 Happy Docker customization with Willow CMS!\u003c/strong\u003e
\u003c/div\u003e
