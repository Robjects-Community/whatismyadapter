# Deploy WhatIsMyAdaptor to Online Portainer Instance

This guide walks you through deploying WhatIsMyAdaptor to your online Portainer instance using Git repository import.

## 🎯 Quick Summary

- **Repository**: `https://github.com/Robjects-Community/WhatIsMyAdaptor.git`  
- **Branch**: `dev_portainer_swarm`
- **Stack File**: `deploy/portainer-stack.yml`
- **Pre-built Images**: Available on Docker Hub (`robjects/whatismyadapter_*`)

## 📋 Step 1: Prerequisites Check

Before starting, ensure you have:

- ✅ Access to your online Portainer instance with permissions to create Stacks
- ✅ Docker Swarm environment (recommended) or Standalone Docker
- ✅ Domain/DNS configured (optional, can use IP:PORT)
- ✅ Required ports available (8080, 8081, 8082, 8025, 8084, 1125, 6379, 3310)

## 🔐 Step 2: Generate Secure Values

**CRITICAL**: Generate secure values for production deployment.

### Security Salt (CakePHP requirement)
```bash
# Generate 64-character hex string
python3 -c "import secrets; print(secrets.token_hex(32))"
# Example output: 3daeca8552323b66c7fb7239217265812b6fe00ace7ea53b8f185fd13e25c41d
```

### Strong Passwords (24+ characters each)
```bash
# Generate secure passwords
python3 -c "import secrets, string; chars = string.ascii_letters + string.digits; print('MySQL Root:', ''.join(secrets.choice(chars) for _ in range(24))); print('MySQL User:', ''.join(secrets.choice(chars) for _ in range(24))); print('Redis Pass:', ''.join(secrets.choice(chars) for _ in range(24)))"

# Example output:
# MySQL Root: zR5IK4iqzW1qezo04TKyAqzH
# MySQL User: OMhcISg9OlHkMDxIOU0kIfLm  
# Redis Pass: 2qdwTv1vbsBfmWGsA0TNHomB
```

**Save these values securely** - you'll need them in the next step.

## 🚀 Step 3: Deploy in Portainer

### 3.1 Access Portainer
1. Log into your online Portainer instance
2. Navigate to **Stacks** section
3. Click **Add stack**

### 3.2 Configure Repository Import
1. **Stack name**: `willowcms` (or your preferred name)
2. **Build method**: Select **Repository**
3. **Repository settings**:
   ```
   Repository URL: https://github.com/Robjects-Community/WhatIsMyAdaptor.git
   Repository reference: dev_portainer_swarm  
   Compose path: docker-compose.yml
   ```
4. **Authentication**: Leave blank (public repository)

### 3.3 Environment Variables
Click **Add environment variable** for each of the following. **Replace the example values with your secure values from Step 2**:

#### 🔴 CRITICAL SECURITY VARIABLES (MUST CHANGE!)
```env
SECURITY_SALT=3daeca8552323b66c7fb7239217265812b6fe00ace7ea53b8f185fd13e25c41d
MYSQL_ROOT_PASSWORD=zR5IK4iqzW1qezo04TKyAqzH
MYSQL_PASSWORD=OMhcISg9OlHkMDxIOU0kIfLm
REDIS_PASSWORD=2qdwTv1vbsBfmWGsA0TNHomB
WILLOW_ADMIN_PASSWORD=YourSecureAdminPassword123
REDIS_COMMANDER_PASSWORD=YourRedisAdminPassword123
```

#### 🌐 APPLICATION CONFIGURATION
```env
APP_NAME=WillowCMS
DEBUG=false
APP_FULL_BASE_URL=http://YOUR_DOMAIN_OR_IP:8080
APP_HTTP_PORT=8080
```

#### 🗄️ DATABASE CONFIGURATION  
```env
MYSQL_DATABASE=cms
MYSQL_USER=cms_user
MYSQL_TEST_DATABASE=cms_test
MYSQL_TEST_USER=cms_user_test
MYSQL_PORT=3310
```

#### 👤 ADMIN USER
```env
WILLOW_ADMIN_USERNAME=admin
WILLOW_ADMIN_EMAIL=admin@yourdomain.com
```

#### 🔧 SERVICE PORTS (adjust if conflicts exist)
```env
PHPMYADMIN_PORT=8082
JENKINS_PORT=8081
MAILPIT_SMTP_PORT=1125
MAILPIT_UI_PORT=8025
REDIS_PORT=6379
REDIS_COMMANDER_PORT=8084
```

#### 📧 EMAIL SETTINGS
```env
EMAIL_REPLY=hello@willowcms.app
EMAIL_NOREPLY=noreply@willowcms.app
```

#### 🐳 DOCKER IMAGES (pre-built and ready)
```env
WILLOW_IMAGE=robjects/whatismyadapter_cms:portainer-swarm-build
JENKINS_IMAGE=robjects/whatismyadapter_jenkins:portainer-swarm-build
```

### 3.4 Deploy the Stack
1. Review all environment variables
2. Click **Deploy the stack**
3. Wait 2-3 minutes for all services to start

## ✅ Step 4: Initialize Application

### 4.1 Check Service Status
In Portainer → Stacks → your stack:
- All services should show **green/healthy** status
- Look for any error logs if services are failing

### 4.2 Initialize CakePHP 5.x Application
1. In Portainer → Stacks → your stack → **willowcms** container
2. Click **Console** → **/bin/sh**
3. Execute these **CakePHP 5.x commands** (following project dev_aliases.txt):

```bash
# Navigate to application directory
cd /var/www/html

# Check CakePHP version and status
bin/cake --version

# Run database migrations (CakePHP 5.x)
bin/cake migrations migrate

# Check migration status
bin/cake migrations status

# Clear all application caches (CakePHP 5.x)
bin/cake cache clear_all

# Verify plugin architecture (DefaultTheme for frontend, AdminTheme for admin)
ls -la plugins/
# Should show: DefaultTheme/ and AdminTheme/ directories

# Check if composer dependencies are current
composer --version
composer check-platform-reqs
```

### 4.3 Verify Plugin Theme Architecture
This project uses a **CakePHP 5.x plugin-based theme system**:

```bash
# Verify plugin structure
ls -la plugins/DefaultTheme/    # Frontend theme
ls -la plugins/AdminTheme/      # Admin interface theme

# Check plugin configuration
bin/cake plugin list

# Verify routes are properly configured for admin prefix
bin/cake routes show | grep admin
```

## 🌐 Step 5: Access Your Application

### Service URLs (replace YOUR_DOMAIN_OR_IP with your actual domain/IP)

| Service | URL | Purpose | Login |
|---------|-----|---------|-------|
| **WillowCMS** | http://YOUR_DOMAIN_OR_IP:8080 | Main application | - |
| **Admin Panel** | http://YOUR_DOMAIN_OR_IP:8080/admin | CMS administration | admin / (your WILLOW_ADMIN_PASSWORD) |
| **phpMyAdmin** | http://YOUR_DOMAIN_OR_IP:8082 | Database management | root / (your MYSQL_ROOT_PASSWORD) |
| **Jenkins** | http://YOUR_DOMAIN_OR_IP:8081 | CI/CD pipeline | admin / admin |
| **Mailpit** | http://YOUR_DOMAIN_OR_IP:8025 | Email testing | - |
| **Redis Commander** | http://YOUR_DOMAIN_OR_IP:8084 | Redis management | admin / (your REDIS_COMMANDER_PASSWORD) |

### 5.1 First Login Test
1. Visit http://YOUR_DOMAIN_OR_IP:8080/admin
2. Login with:
   - Email: admin@yourdomain.com (or your WILLOW_ADMIN_EMAIL)
   - Password: (your WILLOW_ADMIN_PASSWORD)
3. Verify the admin dashboard loads

## 🌍 Step 6: CakePHP 5.x Development Commands (Post-Deployment)

This project follows **CakePHP 5.x conventions** and includes custom development aliases (see `dev_aliases.txt`):

### 6.1 Theme Plugin Architecture
- **Frontend Theme**: `plugins/DefaultTheme/` - Public-facing site templates and assets
- **Admin Theme**: `plugins/AdminTheme/` - Administrative interface templates and assets
- **Route Structure**: Admin routes use `/admin` prefix, frontend uses plugin themes

### 6.2 Essential CakePHP 5.x Commands (in willowcms container)

```bash
# Baking/Code Generation (CakePHP 5.x)
bin/cake bake model ModelName         # Generate model
bin/cake bake controller ControllerName --prefix Admin  # Admin controller
bin/cake bake template ControllerName index  # Template for DefaultTheme

# Database Operations
bin/cake migrations status            # Check migration status
bin/cake migrations migrate           # Run pending migrations  
bin/cake migrations rollback          # Rollback last migration
bin/cake bake migration CreateTableName  # Generate migration

# Cache Management (follows dev_aliases.txt)
bin/cake cache clear_all             # Clear all caches
bin/cake cache clear _cake_model_    # Clear specific cache

# Plugin Management
bin/cake plugin list                 # List loaded plugins
bin/cake plugin load PluginName      # Load plugin

# Internationalization (i18n) - Project Custom Commands
bin/cake i18n extract --paths src,plugins/DefaultTheme,plugins/AdminTheme
bin/cake translate_i18n              # AI-powered translation (custom)
bin/cake generate_po_files           # Generate PO files (custom)

# Code Quality (following composer.json scripts)
vendor/bin/phpunit --colors=always   # Run PHPUnit tests
vendor/bin/phpcs --colors -p         # Code style check
vendor/bin/phpcbf --colors -p        # Fix code style
vendor/bin/phpstan analyze           # Static analysis

# Queue Management (if using queues)
bin/cake queue worker                # Start queue worker
bin/cake queue worker --verbose      # Verbose queue worker
```

### 6.3 Project-Specific Features

```bash
# AI Integration Commands (custom to this project)
bin/cake investigate_article         # Article analysis using AI
bin/cake default_data_export         # Export default data

# Check AI service integration
bin/cake --help | grep -i translate # Show translation commands
bin/cake --help | grep -i ai        # Show AI-related commands
```

## 📊 Step 7: Verify Log Integrity

From the willowcms container console in Portainer:

```bash
# Generate checksums for all current log files
mkdir -p logs/checksums
shasum -a 256 logs/*.log > logs/checksums/latest.sha256

# Verify checksums (run this command periodically)
cd logs
shasum -a 256 --check checksums/latest.sha256
```

## 🚨 Troubleshooting

### "no VNI provided" Network Error
This error indicates Docker Swarm networking issues. **Two solutions**:

**Option A: Use Standalone Docker Stack (Recommended)**
```
Compose path: docker-compose.yml
```
This version uses bridge networking and works without Docker Swarm.

**Option B: Initialize Docker Swarm (Advanced)**
If you need Swarm features, initialize Swarm on your server:
```bash
# On your Docker host
docker swarm init

# Then use the Swarm stack file:
# Compose path: docker-swarm-stack.yml
```

### Services Won't Start
```bash
# Check service logs in Portainer Console
# Look for common issues:
# - Port conflicts
# - Environment variable errors  
# - Memory/CPU constraints
# - Network driver issues (see VNI error above)
```

### Database Connection Issues
```bash
# Test from willowcms container
mysql -h mysql -u cms_user -p -e "SHOW DATABASES;"
# Enter your MYSQL_PASSWORD when prompted
```

### Memory/Resource Issues
- Reduce replica counts in environment variables
- Lower CPU/memory limits if needed
- Check server resource usage

## 🔄 Updates & Maintenance

### Update Application
1. In Portainer → Stacks → your stack
2. Click **Editor** 
3. Click **Pull and redeploy**
4. Wait for rolling updates to complete
5. Re-run migrations if needed:
   ```bash
   bin/cake migrations migrate
   bin/cake cache clear_all
   ```

### Backup Database
```bash
# From mysql container console in Portainer
mysqldump -u cms_user -p cms > /var/lib/mysql/backup_$(date +%F).sql
# Copy backup file via Portainer file browser
```

## 🎉 Success!

Your WhatIsMyAdaptor application is now running on your online Portainer instance with:

- ✅ **High Availability**: Multiple WillowCMS replicas
- ✅ **Health Monitoring**: Built-in health checks
- ✅ **Persistent Storage**: Data survives restarts
- ✅ **Security**: Strong passwords and encryption
- ✅ **Zero Downtime Updates**: Rolling deployment capability
- ✅ **Log Integrity**: Checksum verification system

## 📞 Support

- **Issues**: https://github.com/Robjects-Community/WhatIsMyAdaptor/issues
- **Documentation**: Repository README files
- **Docker Images**: https://hub.docker.com/u/robjects

---

**🔐 Security Reminder**: Immediately change all default passwords and use strong, unique values in production!
