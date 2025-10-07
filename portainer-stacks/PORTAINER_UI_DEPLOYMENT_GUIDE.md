# 🚀 Complete Portainer UI Deployment Guide for WillowCMS
## Step-by-Step Instructions with All Available Options

---

## 📋 Table of Contents

1. [Pre-Deployment Preparation](#1-pre-deployment-preparation)
2. [Access Portainer Web Interface](#2-access-portainer-web-interface)
3. [Deployment Method 1: Repository-Based (Recommended)](#3-deployment-method-1-repository-based-recommended)
4. [Deployment Method 2: Web Editor](#4-deployment-method-2-web-editor)
5. [Deployment Method 3: File Upload](#5-deployment-method-3-file-upload)
6. [Environment Variables Configuration](#6-environment-variables-configuration)
7. [Stack Options and Access Control](#7-stack-options-and-access-control)
8. [Deploy and Monitor](#8-deploy-and-monitor)
9. [Post-Deployment Verification](#9-post-deployment-verification)
10. [Stack Management Operations](#10-stack-management-operations)
11. [Troubleshooting Guide](#11-troubleshooting-guide)
12. [Security Best Practices](#12-security-best-practices)

---

## 1. Pre-Deployment Preparation

### 1.1 Server Setup Requirements

#### SSH into Your Cloud Server
```bash
ssh whatismyadapter@your-server-ip
```

#### Create System User (UID:1034, GID:100)
```bash
# Create group if not exists
sudo groupadd -g 100 users 2>/dev/null || true

# Create user with specific UID/GID
sudo useradd -u 1034 -g 100 -m -s /bin/bash whatismyadapter

# Add user to docker group
sudo usermod -aG docker whatismyadapter

# Verify user creation
id whatismyadapter
# Expected output: uid=1034(whatismyadapter) gid=100(users)
```

#### Create Directory Structure
```bash
# Create all required directories
sudo mkdir -p /volume1/docker/whatismyadapter/{app,logs,nginx-logs,tmp,mysql,redis,mailpit}

# Set ownership to whatismyadapter user
sudo chown -R 1034:100 /volume1/docker/whatismyadapter

# Set proper permissions
sudo chmod -R 755 /volume1/docker/whatismyadapter

# Verify ownership
ls -ln /volume1/docker/whatismyadapter
# All directories should show: 1034 100
```

#### Directory Structure Overview
```
/volume1/docker/whatismyadapter/
├── app/              # Application code (Owner: 1034:100)
├── logs/             # Application logs (Owner: 1034:100)
├── nginx-logs/       # Nginx logs (Owner: 1034:100)
├── tmp/              # Cache/temp files (Owner: 1034:100)
├── mysql/            # MySQL database (Owner: 999:999 - set by MySQL)
├── redis/            # Redis persistence (Owner: 1034:100)
└── mailpit/          # Email storage (Owner: 1034:100)
```

### 1.2 Generate Secure Passwords

Generate all required passwords **before** starting deployment:

```bash
# Generate SECURITY_SALT (32+ characters)
openssl rand -base64 32

# Generate MySQL root password
openssl rand -base64 24

# Generate MySQL user password
openssl rand -base64 24

# Generate Redis password
openssl rand -base64 24

# Generate admin password
openssl rand -base64 24

# Generate Redis Commander password
openssl rand -base64 24
```

**Store these securely** - you'll need them in Step 6!

### 1.3 Prepare Environment File (Optional)

Create a local environment file for reference:

```bash
# Create stack.env on your server
cat > /volume1/docker/whatismyadapter/stack.env << 'EOF'
# Security
SECURITY_SALT=your-generated-salt-here
MYSQL_ROOT_PASSWORD=your-mysql-root-password
MYSQL_PASSWORD=your-mysql-user-password
REDIS_PASSWORD=your-redis-password
WILLOW_ADMIN_PASSWORD=your-admin-password
REDIS_COMMANDER_PASSWORD=your-commander-password

# Application
APP_NAME=WhatIsMyAdapter
APP_FULL_BASE_URL=https://whatismyadapter.me
DEBUG=false
APP_DEFAULT_TIMEZONE=America/Chicago

# Database
MYSQL_DATABASE=whatismyadapter_db
MYSQL_USER=adapter_user

# User Permissions
DOCKER_UID=1034
DOCKER_GID=100

# Ports
WILLOW_HTTP_PORT=8080
MYSQL_PORT=3310
PMA_HTTP_PORT=8082
MAILPIT_HTTP_PORT=8025
MAILPIT_SMTP_PORT=1125
REDIS_COMMANDER_HTTP_PORT=8084

# Admin
WILLOW_ADMIN_USERNAME=admin
WILLOW_ADMIN_EMAIL=admin@whatismyadapter.me

# Redis
REDIS_USERNAME=default

# Email
EMAIL_REPLY=hello@whatismyadapter.me
EMAIL_NOREPLY=noreply@whatismyadapter.me
EOF

# Secure the file
chmod 600 /volume1/docker/whatismyadapter/stack.env
```

---

## 2. Access Portainer Web Interface

### 2.1 Login to Portainer

1. **Navigate to Portainer URL**
   ```
   https://your-portainer-url:9443
   ```
   Or:
   ```
   http://your-portainer-url:9000
   ```

2. **Enter Credentials**
   - Username: `admin` (or your configured username)
   - Password: Your Portainer admin password

3. **Select Docker Environment**
   - On the Home page, click on your Docker environment
   - Usually named "local" or your custom environment name

### 2.2 Navigate to Stacks

1. **Access Stacks Section**
   - Click **"Stacks"** in the left sidebar menu
   - You'll see a list of existing stacks (if any)

2. **Create New Stack**
   - Click **"+ Add stack"** button (blue button in top right)
   - You'll see the stack creation interface

3. **Name Your Stack**
   - Stack name: `whatismyadapter` (lowercase, no spaces)
   - This name will be used for Docker resources

---

## 3. Deployment Method 1: Repository-Based (Recommended)

### 3.1 Select Repository Build Method

At the top of the stack creation page, you'll see three options:
- ✅ **Repository** ← Select this
- Web editor
- Upload

### 3.2 Configure Repository Settings

#### Repository URL
```
https://github.com/Robjects-Community/WhatIsMyAdaptor.git
```
Or use your fork:
```
https://github.com/YOUR-USERNAME/willow.git
```

#### Repository Reference
Choose your branch:
- **Production:** `main-clean`
- **Development:** `portainer-stack`
- **Testing:** `droplet-deploy`

Example:
```
main-clean
```

#### Compose Path
```
docker-compose-port-cloud.yml
```

**Important:** This is the path **within** your repository, not an absolute path.

**Alternative Paths:**
- `docker-compose-port-cloud.yml` - Production cloud deployment (recommended)
- `docker-compose-stack.yml` - Docker Swarm deployment
- `portainer-stacks/docker-compose-cloud.yml` - Legacy path

### 3.3 Authentication (For Private Repositories)

If your repository is private:

1. **Toggle Authentication:** ON
2. **Username:** Your GitHub username
3. **Personal Access Token:** Your GitHub PAT
   - Generate at: https://github.com/settings/tokens
   - Required scopes: `repo` (full control)

For public repositories: Leave authentication OFF.

### 3.4 GitOps Automatic Updates (Optional)

Enable automatic updates when you push to your repository:

1. **Toggle "GitOps Updates":** ON
2. **Polling Interval:** `5m` (checks every 5 minutes)
3. **Additional Options:**
   - ✅ **Pull latest image:** Updates container images
   - ✅ **Re-pull image:** Forces image refresh
   - ✅ **Force redeployment:** Recreates all containers

**Use Case:** Ideal for continuous deployment workflows.

### 3.5 Repository Method Screenshot Guide

```
┌─────────────────────────────────────────┐
│ Build method                            │
│ ○ Web editor                            │
│ ● Repository  ← Select                  │
│ ○ Upload                                │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ Repository                              │
│ [URL input field]                       │
│ https://github.com/user/repo.git        │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ Repository reference                    │
│ [Branch input field]                    │
│ main-clean                              │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ Compose path                            │
│ [Path input field]                      │
│ portainer-stacks/docker-compose-cloud.yml│
└─────────────────────────────────────────┘
```

---

## 4. Deployment Method 2: Web Editor

### 4.1 Select Web Editor Build Method

At the top of the stack creation page:
- Web editor ← Select this
- ○ Repository
- ○ Upload

### 4.2 Use the Web Editor

1. **Open Editor**
   - A large text editor area appears
   - Has YAML syntax highlighting

2. **Copy Compose File Content**
   - Open `docker-compose-port-cloud.yml` on your local machine
   - Copy entire contents
   - Or use `portainer-stacks/docker-compose-cloud.yml` for legacy support

3. **Paste into Editor**
   ```yaml
   # Paste the entire docker-compose-cloud.yml content here
   services:
     willowcms:
       build:
         context: .
         dockerfile: infrastructure/docker/willowcms/Dockerfile
         args:
           - UID=${DOCKER_UID:-1034}
           - GID=${DOCKER_GID:-100}
       # ... rest of your compose file
   ```

### 4.3 Editor Features

#### Syntax Validation
- **Red underlines:** Syntax errors
- **Yellow warnings:** Potential issues
- **Green checks:** Valid syntax

#### Format/Prettify
- Click **"Prettify"** button to auto-format YAML
- Fixes indentation and spacing

#### Search and Replace
- Press `Ctrl+F` (Windows/Linux) or `Cmd+F` (Mac)
- Search for specific services or variables

### 4.4 Important Notes for Web Editor Method

⚠️ **Critical:** When using Web Editor with `build:` directives:
- Portainer **cannot build** from build context
- You must either:
  1. Replace `build:` with pre-built `image:` references
  2. Or use Repository method instead

**Recommended Change for Web Editor:**
```yaml
# Change FROM:
build:
  context: .
  dockerfile: infrastructure/docker/willowcms/Dockerfile

# TO:
image: whatismyadapter/willowcms:latest
```

---

## 5. Deployment Method 3: File Upload

### 5.1 Select Upload Build Method

At the top of the stack creation page:
- ○ Web editor
- ○ Repository
- ✅ **Upload** ← Select this

### 5.2 Upload Compose File

1. **Click "Select file" Button**
   - A file browser opens

2. **Navigate to Your Compose File**
   - Go to: `docker-compose-port-cloud.yml`
   - Or: `portainer-stacks/docker-compose-cloud.yml` (legacy)
   - Select the file

3. **Verify Upload**
   - Filename appears: `docker-compose-cloud.yml`
   - File size shows: ~7-12 KB
   - Upload status: ✅ Uploaded

### 5.3 Review Uploaded Content

1. **Click "Load file in editor"** (optional)
   - Opens web editor with uploaded content
   - You can review and make quick edits

2. **Verify File Content**
   - Check all services are present:
     - willowcms
     - mysql
     - redis
     - phpmyadmin
     - mailpit
     - redis-commander

### 5.4 Upload Additional Files (Optional)

You can upload additional files:
- `.env` files
- Custom configuration files
- Scripts

**How to Upload Multiple Files:**
1. Create a `.tar.gz` archive with all files
2. Upload the archive
3. Portainer will extract automatically

---

## 6. Environment Variables Configuration

This is **THE MOST CRITICAL STEP** - all deployment methods require environment variables.

### 6.1 Environment Variables Overview

Scroll down to the **"Environment variables"** section.

You'll see three modes:
1. **Simple mode** (name/value pairs) - Easiest for beginners
2. **Advanced mode** (editor) - Best for bulk paste
3. **Load from .env file** - Upload pre-prepared file

### 6.2 Method A: Simple Mode (Recommended for First-Time Users)

#### How to Use Simple Mode:

1. **Click "+ add environment variable"** button
2. **Enter variable name** (e.g., `SECURITY_SALT`)
3. **Enter variable value** (paste your generated password)
4. **Repeat** for all variables

#### Complete Variable List with Descriptions:

##### 🔐 Security Variables (REQUIRED)
```
Name: SECURITY_SALT
Value: [Paste 32+ character string from Step 1.2]
Description: CakePHP security salt for encryption

Name: MYSQL_ROOT_PASSWORD
Value: [Paste generated password]
Description: MySQL root user password

Name: MYSQL_PASSWORD
Value: [Paste generated password]
Description: MySQL application user password

Name: REDIS_PASSWORD
Value: [Paste generated password]
Description: Redis authentication password

Name: WILLOW_ADMIN_PASSWORD
Value: [Paste generated password]
Description: WillowCMS admin panel password

Name: REDIS_COMMANDER_PASSWORD
Value: [Paste generated password]
Description: Redis Commander web interface password
```

##### 🌐 Application Settings (REQUIRED)
```
Name: APP_NAME
Value: WhatIsMyAdapter
Description: Application display name

Name: APP_FULL_BASE_URL
Value: https://whatismyadapter.me
Description: Public URL for your application
Note: Use https:// for production, http://localhost:8080 for testing

Name: DEBUG
Value: false
Description: Debug mode (use 'true' only for development)

Name: APP_DEFAULT_TIMEZONE
Value: America/Chicago
Description: Application timezone (see: https://www.php.net/manual/en/timezones.php)
```

##### 💾 Database Configuration (REQUIRED)
```
Name: MYSQL_DATABASE
Value: whatismyadapter_db
Description: MySQL database name

Name: MYSQL_USER
Value: adapter_user
Description: MySQL application username (not root)

Name: MYSQL_IMAGE_TAG
Value: 8.0
Description: MySQL version tag (or use 'mariadb:11.4-noble' for MariaDB LTS)

Name: MYSQL_INNODB_LOG_FILE_SIZE
Value: 512M
Description: InnoDB log file size

Name: MYSQL_INNODB_BUFFER_POOL_SIZE
Value: 1G
Description: InnoDB buffer pool size

Name: MYSQL_MAX_CONNECTIONS
Value: 200
Description: Maximum database connections
```

##### 👤 User Permissions (REQUIRED)
```
Name: DOCKER_UID
Value: 1034
Description: User ID for file ownership (must match server user)

Name: DOCKER_GID
Value: 100
Description: Group ID for file ownership (must match server group)
```

##### 🔌 Port Mappings (REQUIRED)
```
Name: WILLOW_HTTP_PORT
Value: 8080
Description: WillowCMS HTTP port (public or behind reverse proxy)

Name: MYSQL_PORT
Value: 3310
Description: MySQL external port (recommend: 3310 to avoid conflicts)

Name: PMA_HTTP_PORT
Value: 8082
Description: phpMyAdmin web interface port

Name: MAILPIT_HTTP_PORT
Value: 8025
Description: Mailpit web interface port

Name: MAILPIT_SMTP_PORT
Value: 1125
Description: Mailpit SMTP server port

Name: REDIS_COMMANDER_HTTP_PORT
Value: 8084
Description: Redis Commander web interface port
```

##### 🛠️ Admin Configuration (REQUIRED)
```
Name: WILLOW_ADMIN_USERNAME
Value: admin
Description: Admin username for WillowCMS

Name: WILLOW_ADMIN_EMAIL
Value: admin@whatismyadapter.me
Description: Admin email address
```

##### 🔄 Redis Configuration (REQUIRED)
```
Name: REDIS_USERNAME
Value: default
Description: Redis username (use 'default' for Redis 6+)

Name: REDIS_COMMANDER_USERNAME
Value: admin
Description: Redis Commander login username
```

##### 📧 Email Configuration (REQUIRED)
```
Name: EMAIL_REPLY
Value: hello@whatismyadapter.me
Description: Reply-to email address

Name: EMAIL_NOREPLY
Value: noreply@whatismyadapter.me
Description: No-reply email address
```

##### 🎨 Optional Variables
```
Name: OPENAI_API_KEY
Value: [Your OpenAI API key]
Description: Optional - for AI features

Name: YOUTUBE_API_KEY
Value: [Your YouTube API key]
Description: Optional - for YouTube integrations

Name: TRANSLATE_API_KEY
Value: [Your translation API key]
Description: Optional - for translation features

Name: EXPERIMENTAL_TESTS
Value: Off
Description: Enable/disable experimental features
```

##### 🚀 Production Configuration (RECOMMENDED)
```
Name: PRODUCTION_MODE
Value: true
Description: Enable production optimizations

Name: DEV_MODE
Value: false
Description: Disable development features in production

Name: SSH_ENABLE
Value: false
Description: Disable SSH access in production
```

##### 📊 Resource Limits (OPTIONAL but RECOMMENDED)
```
Name: WILLOWCMS_MEMORY_LIMIT
Value: 1G
Description: Maximum memory for WillowCMS container

Name: WILLOWCMS_CPU_LIMIT
Value: 1.0
Description: Maximum CPU cores for WillowCMS

Name: WILLOWCMS_MEMORY_RESERVATION
Value: 512M
Description: Reserved memory for WillowCMS

Name: REDIS_MEMORY_LIMIT
Value: 512M
Description: Maximum memory for Redis

Name: REDIS_MAX_MEMORY
Value: 256mb
Description: Redis internal max memory setting

Name: REDIS_CPU_LIMIT
Value: 0.5
Description: Maximum CPU cores for Redis

Name: MYSQL_MEMORY_LIMIT
Value: 2G
Description: Maximum memory for MySQL

Name: MYSQL_CPU_LIMIT
Value: 1.0
Description: Maximum CPU cores for MySQL

Name: MYSQL_MEMORY_RESERVATION
Value: 1G
Description: Reserved memory for MySQL
```

##### 🔹 Advanced Options (OPTIONAL)
```
Name: PROJECT_NAME
Value: willowcms
Description: Project name for volume and network naming

Name: VOLUME_DRIVER
Value: local
Description: Docker volume driver (local, nfs, etc.)

Name: NETWORK_NAME
Value: willowcms_production_network
Description: Custom network name

Name: MP_MAX_MESSAGES
Value: 10000
Description: Mailpit maximum message storage

Name: PMA_ARBITRARY
Value: 0
Description: Disable phpMyAdmin arbitrary server connections (security)
```

#### Simple Mode Screenshot Guide:
```
┌──────────────────────────────────────────────┐
│ Environment variables                        │
│                                              │
│ ○ Simple mode (selected)                     │
│ ○ Advanced mode                              │
│ ○ Load variables from .env file              │
│                                              │
│ ┌──────────────────────────────────────────┐ │
│ │ name          value                      │ │
│ │ SECURITY_SALT [••••••••••••••••••••••••] │ │
│ │                            [× remove]    │ │
│ └──────────────────────────────────────────┘ │
│                                              │
│ [+ add environment variable]                 │
└──────────────────────────────────────────────┘
```

### 6.3 Method B: Advanced Mode (Best for Bulk Configuration)

#### How to Use Advanced Mode:

1. **Click "Advanced mode"** toggle at the top
2. **Editor appears** for bulk entry
3. **Paste all variables** in `KEY=VALUE` format
4. **One variable per line**

#### Complete Environment Variables Template:

Copy and paste this entire block into Advanced mode (replace values with your generated passwords):

```bash
# ============================================
# SECURITY (REQUIRED - CHANGE ALL VALUES)
# ============================================
SECURITY_SALT=YOUR-GENERATED-SECURITY-SALT-32-CHARS-OR-MORE
MYSQL_ROOT_PASSWORD=YOUR-GENERATED-MYSQL-ROOT-PASSWORD
MYSQL_PASSWORD=YOUR-GENERATED-MYSQL-USER-PASSWORD
REDIS_PASSWORD=YOUR-GENERATED-REDIS-PASSWORD
WILLOW_ADMIN_PASSWORD=YOUR-GENERATED-ADMIN-PASSWORD
REDIS_COMMANDER_PASSWORD=YOUR-GENERATED-COMMANDER-PASSWORD

# ============================================
# APPLICATION CONFIGURATION
# ============================================
APP_NAME=WhatIsMyAdapter
APP_FULL_BASE_URL=https://whatismyadapter.me
DEBUG=false
APP_ENCODING=UTF-8
APP_DEFAULT_LOCALE=en_US
APP_DEFAULT_TIMEZONE=America/Chicago

# ============================================
# DATABASE CONFIGURATION
# ============================================
MYSQL_DATABASE=whatismyadapter_db
MYSQL_USER=adapter_user
MYSQL_IMAGE_TAG=8.0
MYSQL_INNODB_LOG_FILE_SIZE=512M
MYSQL_INNODB_BUFFER_POOL_SIZE=1G
MYSQL_MAX_CONNECTIONS=200
DB_PORT=3306

# ============================================
# USER PERMISSIONS (MUST MATCH SERVER USER)
# ============================================
DOCKER_UID=1034
DOCKER_GID=100

# ============================================
# PORT MAPPINGS
# ============================================
WILLOW_HTTP_PORT=8080
MYSQL_PORT=3310
PMA_HTTP_PORT=8082
MAILPIT_HTTP_PORT=8025
MAILPIT_SMTP_PORT=1125
REDIS_COMMANDER_HTTP_PORT=8084

# ============================================
# ADMIN CONFIGURATION
# ============================================
WILLOW_ADMIN_USERNAME=admin
WILLOW_ADMIN_EMAIL=admin@whatismyadapter.me

# ============================================
# REDIS CONFIGURATION
# ============================================
REDIS_USERNAME=default
REDIS_PORT=6379
REDIS_TAG=7.2-alpine
REDIS_COMMANDER_USERNAME=admin
REDIS_DATABASE=0
REDIS_MAX_MEMORY=256mb
REDIS_HEALTHCHECK_INTERVAL=10s
REDIS_HEALTHCHECK_TIMEOUT=3s
REDIS_HEALTHCHECK_RETRIES=5

# ============================================
# EMAIL CONFIGURATION
# ============================================
EMAIL_HOST=mailpit
EMAIL_PORT=1025
EMAIL_REPLY=hello@whatismyadapter.me
EMAIL_NOREPLY=noreply@whatismyadapter.me

# ============================================
# PHPMYADMIN CONFIGURATION
# ============================================
PMA_USER=root
PHPMYADMIN_IMAGE_TAG=latest
UPLOAD_LIMIT=300M

# ============================================
# MAILPIT CONFIGURATION
# ============================================
MP_MAX_MESSAGES=5000
MAILPIT_IMAGE_TAG=latest

# ============================================
# OPTIONAL API KEYS
# ============================================
OPENAI_API_KEY=
YOUTUBE_API_KEY=
TRANSLATE_API_KEY=

# ============================================
# FEATURE FLAGS
# ============================================
EXPERIMENTAL_TESTS=Off

# ============================================
# PRODUCTION SETTINGS
# ============================================
PRODUCTION_MODE=true
DEV_MODE=false
SSH_ENABLE=false

# ============================================
# RESOURCE LIMITS (OPTIONAL but RECOMMENDED)
# ============================================
# WillowCMS Resources
WILLOWCMS_MEMORY_LIMIT=1G
WILLOWCMS_CPU_LIMIT=1.0
WILLOWCMS_MEMORY_RESERVATION=512M

# Redis Resources
REDIS_MEMORY_LIMIT=512M
REDIS_CPU_LIMIT=0.5

# MySQL Resources
MYSQL_MEMORY_LIMIT=2G
MYSQL_CPU_LIMIT=1.0
MYSQL_MEMORY_RESERVATION=1G

# ============================================
# ADVANCED CONFIGURATION (OPTIONAL)
# ============================================
PROJECT_NAME=willowcms
VOLUME_DRIVER=local
NETWORK_NAME=willowcms_production_network
WILLOW_DB_SERVICE=mysql
WILLOW_REDIS_SERVICE=redis
PMA_ARBITRARY=0

# ============================================
# IMAGE TAGS
# ============================================
WILLOWCMS_IMAGE=willowcms:production-hardened
TAG=latest
```

### 6.4 Method C: Load from .env File

#### How to Use File Upload:

1. **Click "Load variables from .env file"** button
2. **Select your stack.env file** from Step 1.3
3. **File uploads** and variables populate automatically
4. **Review variables** in Simple or Advanced mode

#### .env File Format:
```bash
# Must be in KEY=VALUE format
# No spaces around =
# Comments allowed with #
# Environment variables are loaded in Portainer

SECURITY_SALT=your-value
MYSQL_ROOT_PASSWORD=your-value
MYSQL_PASSWORD=your-value
# etc.
```

**Important Notes:**
- Portainer supports loading `.env` files directly
- Variables are NOT committed to Git (security best practice)
- Use `stack.env` for production deployments
- All sensitive data should use environment variables
- Never hardcode passwords or secrets in compose files

### 6.5 Verify Environment Variables

Before proceeding, **verify all variables are set**:

#### Checklist:
- ✅ All REQUIRED variables have values (no empty fields)
- ✅ Passwords are strong and unique
- ✅ `APP_FULL_BASE_URL` matches your domain
- ✅ `DOCKER_UID` and `DOCKER_GID` are 1034 and 100
- ✅ Port mappings don't conflict with existing services
- ✅ Email addresses are correct

---

## 7. Stack Options and Access Control

### 7.1 Access Control Settings

Scroll down to **"Access control"** section.

#### Options:

**For Production:**
```
● Restricted (recommended)
○ Public
```

**Restricted Mode:**
- Add authorized users: Select from dropdown
- Add authorized teams: Select from dropdown
- Only specified users/teams can manage this stack

**Public Mode:**
- All Portainer users can view
- Only admins can modify
- Use for development/testing only

### 7.2 Deployment Options

These options control deployment behavior:

#### Pull Latest Images
```
☑ Pull latest images
```
- Forces pull of latest image versions
- Recommended: ON for production
- Use case: Ensures you have security updates

#### Prune Services
```
☑ Prune services
```
- Removes old/orphaned services
- Recommended: ON
- Use case: Keeps stack clean

#### Prune Volumes
```
☐ Prune volumes (CAUTION)
```
- ⚠️ **WARNING:** Can delete data!
- Recommended: OFF (unless you know what you're doing)
- Use case: Fresh deployments only

#### Webhook
```
☐ Enable webhook
```
- Generates webhook URL for external triggers
- Recommended: OFF (unless needed for CI/CD)
- Use case: Automated deployments from GitHub Actions

### 7.3 Resource Limits (Optional but Recommended)

Click **"Resource limits"** to expand.

#### Set Resource Limits:

**Memory Limit:**
```
Memory limit: 4 GB
Memory reservation: 2 GB
```

**CPU Limit:**
```
CPU limit: 2.0 (2 cores)
```

**Why Set Limits:**
- Prevents stack from consuming all server resources
- Improves stability
- Allows other services to run

**Recommended Values:**
- **Small VPS (2GB RAM):** Memory limit: 1.5GB
- **Medium VPS (4GB RAM):** Memory limit: 3GB
- **Large VPS (8GB RAM):** Memory limit: 6GB

### 7.4 Labels and Annotations

Click **"Labels"** to add custom labels.

#### Useful Labels:

```
environment=production
project=whatismyadapter
backup=daily
managed-by=portainer
deployment-date=2025-01-07
```

**Use Case:** Organization and filtering in Portainer UI.

### 7.5 Additional Stack Options

#### Automatic Updates (Repository method only)
```
☑ Automatic updates
Interval: 5 minutes
```

#### Pre-pull Images
```
☑ Pre-pull images
```
- Downloads images before starting containers
- Faster deployments
- Recommended: ON

---

## 8. Deploy and Monitor

### 8.1 Final Pre-Deployment Checklist

Before clicking "Deploy", verify:

- ✅ Stack name is correct: `whatismyadapter`
- ✅ Build method selected and configured
- ✅ All required environment variables set
- ✅ Passwords are secure and stored safely
- ✅ Access control configured
- ✅ Resource limits set (if applicable)

### 8.2 Deploy the Stack

1. **Scroll to Bottom**
   - Find the blue **"Deploy the stack"** button

2. **Click "Deploy the stack"**
   - Deployment begins immediately
   - You'll see a progress indicator

3. **Deployment Status Messages**
   ```
   ⏳ Deployment in progress...
   📥 Pulling images...
   🔨 Building images... (if using build context)
   🚀 Starting services...
   ✅ Stack deployed successfully!
   ```

### 8.3 Monitor Deployment Progress

#### Real-Time Logs Window

Portainer shows real-time deployment logs:

```
Pulling mysql (mysql:8.0)...
8.0: Pulling from library/mysql
Digest: sha256:abc123...
Status: Downloaded newer image for mysql:8.0

Pulling redis (redis:7.2-alpine)...
7.2-alpine: Pulling from library/redis
Status: Image is up to date for redis:7.2-alpine

Creating network "whatismyadapter_whatismyadapter_network" ... done
Creating volume "whatismyadapter_willow_mysql_data" ... done
Creating volume "whatismyadapter_willow_redis_data" ... done

Creating whatismyadapter_mysql_1 ... done
Creating whatismyadapter_redis_1 ... done
Creating whatismyadapter_willowcms_1 ... done
Creating whatismyadapter_phpmyadmin_1 ... done
Creating whatismyadapter_mailpit_1 ... done
Creating whatismyadapter_redis-commander_1 ... done

✓ Stack deployed successfully
```

#### Expected Deployment Time:
- **With pre-pulled images:** 30-60 seconds
- **Pulling new images:** 2-5 minutes
- **Building from source:** 5-15 minutes

### 8.4 Common Deployment Messages

#### Success Messages:
```
✅ Stack deployed successfully
✅ All services started
✅ No errors detected
```

#### Warning Messages:
```
⚠️ Image pull took longer than expected
⚠️ Container restart policy applied
⚠️ Health check not configured
```

#### Error Messages (see Section 11 for solutions):
```
❌ Failed to pull image
❌ Container exited with code 1
❌ Network creation failed
❌ Volume mount permission denied
```

---

## 9. Post-Deployment Verification

### 9.1 Check Stack Status

1. **Navigate to Stacks**
   - Click **"Stacks"** in left sidebar
   - Find your stack: `whatismyadapter`

2. **Verify Stack Status**
   - Status: **Active** (green indicator)
   - Running time: Shows uptime
   - Services: Shows service count

```
┌────────────────────────────────────────┐
│ Name              Status     Services  │
│ whatismyadapter   ● Active   6         │
│                   3m 24s                │
└────────────────────────────────────────┘
```

### 9.2 View Service Details

Click on **"whatismyadapter"** stack name to see detailed view.

#### Services Status:

```
Service              Status    Published Ports
─────────────────────────────────────────────
willowcms            Running   8080:80
mysql                Running   3310:3306
redis                Running   -
phpmyadmin           Running   8082:80
mailpit              Running   8025:8025, 1125:1025
redis-commander      Running   8084:8081
```

#### What to Look For:
- ✅ **Status:** All services show "Running" (green)
- ✅ **Health:** Shows "healthy" if health checks configured
- ✅ **Ports:** All mapped ports show correctly
- ❌ **Warning:** If any service shows "Stopped" or "Error" (red)

### 9.3 Check Container Logs

For each service, verify logs are healthy:

#### WillowCMS Container:
```bash
# In Portainer: Select willowcms container → Logs
# Look for:
✅ [INFO] Application started successfully
✅ [INFO] Database connection established
✅ [INFO] Redis connection established
✅ [INFO] Server listening on port 80

# Avoid:
❌ [ERROR] Database connection failed
❌ [ERROR] Permission denied
❌ [CRITICAL] Application crashed
```

#### MySQL Container:
```bash
# Look for:
✅ mysqld: ready for connections. Version: '8.0.35'
✅ MySQL init process done. Ready for start up.

# Wait for this before testing application (60-90 seconds)
```

#### Redis Container:
```bash
# Look for:
✅ Ready to accept connections
✅ DB loaded from append only file

# Health check:
✅ PONG received from health check
```

### 9.4 Test Service Access

#### Test Application Access:

**Production (with reverse proxy):**
```
https://whatismyadapter.me
```

**Direct Access (testing):**
```
http://your-server-ip:8080
```

**Expected Response:**
- HTTP 200 OK
- WillowCMS homepage loads
- No error messages

#### Test Admin Panel:

**URL:**
```
https://whatismyadapter.me/admin
```

**Login:**
- Username: `admin` (or your configured username)
- Password: [Your WILLOW_ADMIN_PASSWORD]

**Expected:**
- Login page loads
- Credentials accepted
- Admin dashboard appears

#### Test phpMyAdmin:

**URL:**
```
http://your-server-ip:8082
```

**Login:**
- Server: `mysql` (should be pre-filled)
- Username: `root` or `adapter_user`
- Password: [Your MYSQL_ROOT_PASSWORD or MYSQL_PASSWORD]

**Expected:**
- Database list shows: `whatismyadapter_db`
- Tables visible (if migrations ran)

#### Test Mailpit:

**URL:**
```
http://your-server-ip:8025
```

**Expected:**
- Mailpit inbox loads
- Shows 0 messages (initially)
- Interface is responsive

#### Test Redis Commander:

**URL:**
```
http://your-server-ip:8084
```

**Login:**
- Username: `admin` (or your configured username)
- Password: [Your REDIS_COMMANDER_PASSWORD]

**Expected:**
- Redis Commander interface loads
- Shows connected to Redis
- Database 0 visible

### 9.5 Verify Volume Mounts

Check that data persists in volumes:

1. **Go to Volumes** (left sidebar)
2. **Find your stack's volumes:**
   ```
   whatismyadapter_willow_mysql_data
   whatismyadapter_willow_redis_data
   whatismyadapter_willow_mailpit_data
   whatismyadapter_willow_app_data
   whatismyadapter_willow_logs
   ```

3. **Verify sizes:**
   - `mysql_data`: Should be > 100MB after initialization
   - `redis_data`: Should be > 1MB
   - `app_data`: Should match application size

### 9.6 Network Verification

1. **Go to Networks** (left sidebar)
2. **Find your stack's network:**
   ```
   whatismyadapter_whatismyadapter_network
   ```

3. **Click on network** to see connected containers
4. **Verify all 6 containers are connected:**
   - willowcms
   - mysql
   - redis
   - phpmyadmin
   - mailpit
   - redis-commander

---

## 10. Stack Management Operations

### 10.1 Viewing Logs

#### Combined Stack Logs:
1. Go to **Stacks** → `whatismyadapter`
2. Click **"Logs"** tab
3. All services' logs appear in one view

#### Individual Container Logs:
1. Go to **Containers**
2. Click on container name (e.g., `whatismyadapter_willowcms_1`)
3. Click **"Logs"** tab

#### Log Filtering:
```
┌─────────────────────────────────────┐
│ Logs                                │
│                                     │
│ Filter: [_________]  🔍 Search      │
│                                     │
│ ○ All  ○ Error  ○ Warning  ○ Info  │
│                                     │
│ [Log entries...]                    │
│                                     │
│ [Download logs]  [Auto-refresh ☑]  │
└─────────────────────────────────────┘
```

**Useful Filters:**
- Search: `ERROR` - Shows only errors
- Search: `database` - Shows DB-related logs
- Search: `redis` - Shows Redis-related logs

#### Download Logs:
Click **"Download logs"** button to save logs locally for analysis.

### 10.2 Updating the Stack

#### Update Compose File:
1. **Go to Stacks** → `whatismyadapter`
2. **Click "Editor" tab**
3. **Modify compose file** (e.g., change image tag)
4. **Click "Update the stack"** button

#### Update Options:
```
☑ Pull latest images
☑ Re-pull images and redeploy
☐ Prune services
```

**Use Cases:**
- Change port mappings
- Update image versions
- Modify environment variables
- Add/remove services

#### Update from Repository (Git):
If using Repository method:
1. **Push changes** to your Git repository
2. **Go to Stacks** → `whatismyadapter`
3. **Click "Pull and redeploy"** button
4. Portainer fetches latest code and redeploys

### 10.3 Stop/Start Stack

#### Stop Stack (Pause Services):
1. **Go to Stacks** → `whatismyadapter`
2. **Click "Stop this stack"** button
3. All containers stop (data persists)

**Use Case:** Maintenance, server shutdown, resource conservation.

#### Start Stack (Resume Services):
1. **Go to Stacks** → `whatismyadapter`
2. **Click "Start this stack"** button
3. All containers restart with same configuration

#### Restart Stack (Full Restart):
1. **Stop stack** first
2. **Wait 10 seconds**
3. **Start stack** again

**Use Case:** Apply configuration changes, troubleshoot issues.

### 10.4 Accessing Container Console

#### Open Container Shell:
1. **Go to Containers**
2. **Click on container** (e.g., `whatismyadapter_willowcms_1`)
3. **Click "Console"** button
4. **Select shell:**
   - `/bin/bash` (if available)
   - `/bin/sh` (fallback)
5. **Click "Connect"**

#### Useful Commands:

**WillowCMS Container:**
```bash
# Check application status
ps aux

# View file permissions
ls -la /var/www/html

# Check PHP version
php -v

# Run CakePHP commands
cd /var/www/html
bin/cake --version

# Check logs
tail -f logs/error.log
```

**MySQL Container:**
```bash
# Connect to MySQL
mysql -u root -p

# Show databases
SHOW DATABASES;

# Use database
USE whatismyadapter_db;

# Show tables
SHOW TABLES;
```

**Redis Container:**
```bash
# Connect to Redis CLI
redis-cli -a YOUR_REDIS_PASSWORD

# Test connection
PING

# Check keys
KEYS *

# Get info
INFO
```

### 10.5 Scaling Services (Optional)

Some services can be scaled (run multiple instances):

1. **Go to Stacks** → `whatismyadapter`
2. **Click service** (e.g., `willowcms`)
3. **Click "Scale"** button
4. **Enter number of replicas:** `2`
5. **Click "Scale"**

**Note:** Not all services support scaling (e.g., databases).

### 10.6 Removing the Stack

#### Complete Stack Removal:

1. **Go to Stacks** → `whatismyadapter`
2. **Click "Delete this stack"** button (red)
3. **Choose deletion options:**
   ```
   ☑ Remove services
   ☐ Remove volumes (WARNING: Deletes all data!)
   ☑ Remove orphaned containers
   ```
4. **Click "Confirm"**

**⚠️ CRITICAL:**
- **Unchecking "Remove volumes"** keeps your data
- **Checking "Remove volumes"** **PERMANENTLY DELETES** database, uploads, logs
- **Always backup** before removing stack with volumes

#### Clean Removal (Keeps Data):
```
☑ Remove services
☐ Remove volumes  ← Leave UNCHECKED
☑ Remove orphaned containers
```

#### Full Removal (Deletes Everything):
```
☑ Remove services
☑ Remove volumes  ← DELETES ALL DATA
☑ Remove orphaned containers
```

---

## 11. Troubleshooting Guide

### 11.1 Container Won't Start

#### Symptoms:
- Container status: **Stopped** or **Exited**
- Error message in logs

#### Diagnosis:
```bash
# Check logs in Portainer:
Containers → [container name] → Logs

# Look for:
- Permission denied errors
- Port already in use
- Missing environment variables
- Image pull failures
```

#### Solutions:

**Permission Denied:**
```bash
# SSH into server
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/app
sudo chmod -R 755 /volume1/docker/whatismyadapter
```

**Port Conflict:**
```bash
# Check ports in use
netstat -tlnp | grep 8080

# Change port in environment variables:
WILLOW_HTTP_PORT=8090  # Use different port
```

**Missing Environment Variables:**
- Go to Stack → Editor
- Add missing variables
- Update stack

### 11.2 Database Connection Failed

#### Symptoms:
- Application shows "Database connection error"
- WillowCMS container logs show connection errors

#### Diagnosis:
```bash
# Check MySQL container logs:
Containers → whatismyadapter_mysql_1 → Logs

# Look for:
✅ mysqld: ready for connections
❌ Access denied for user
```

#### Solutions:

**MySQL Not Ready:**
```bash
# Wait 60-90 seconds for MySQL initialization
# Check logs until you see: "ready for connections"
```

**Wrong Credentials:**
```bash
# Verify environment variables match:
DB_HOST=mysql
DB_DATABASE=whatismyadapter_db
DB_USERNAME=adapter_user
DB_PASSWORD=[your password]

# Update stack if needed
```

**Network Issue:**
```bash
# Verify both containers on same network:
Networks → whatismyadapter_whatismyadapter_network
# Should show both willowcms and mysql
```

**Test Database Connection:**
```bash
# Access WillowCMS container console
docker exec -it whatismyadapter_willowcms_1 bash

# Test MySQL connection
mysql -h mysql -u adapter_user -p
# Enter password when prompted
```

### 11.3 Redis Connection Failed

#### Symptoms:
- Application logs show "Redis connection failed"
- Features requiring cache don't work

#### Diagnosis:
```bash
# Check Redis container:
Containers → whatismyadapter_redis_1 → Logs

# Look for:
✅ Ready to accept connections
❌ Authentication failed
```

#### Solutions:

**Redis Not Healthy:**
```bash
# Check health status:
Containers → whatismyadapter_redis_1 → Health

# Wait for health check to pass (10-30 seconds)
```

**Wrong Password:**
```bash
# Verify environment variables:
REDIS_PASSWORD=[your password]
REDIS_USERNAME=default

# Both willowcms and redis must have same REDIS_PASSWORD
```

**Test Redis Connection:**
```bash
# Access Redis container console
docker exec -it whatismyadapter_redis_1 sh

# Test connection
redis-cli -a YOUR_REDIS_PASSWORD ping
# Should respond: PONG
```

### 11.4 Image Pull Failures

#### Symptoms:
- Deployment fails with "image pull error"
- Logs show "manifest not found" or "unauthorized"

#### Diagnosis:
```bash
# Check deployment logs for:
❌ Error response from daemon: pull access denied
❌ manifest for image:tag not found
❌ toomanyrequests: Rate limit exceeded
```

#### Solutions:

**Image Doesn't Exist:**
```bash
# Verify image name and tag:
WILLOWCMS_IMAGE=whatismyadapter/willowcms:latest

# Check if image exists on Docker Hub
# or use pre-built image
```

**Docker Hub Rate Limit:**
```bash
# Login to Docker Hub in Portainer:
Settings → Registries → Add registry
Type: DockerHub
Username: [your username]
Password: [your password]
```

**Private Registry Authentication:**
```bash
# Add registry credentials:
Settings → Registries → Add registry
Type: Custom
URL: registry.example.com
Username: [username]
Password: [password]
```

### 11.5 Permission Errors in Volumes

#### Symptoms:
- Application can't write logs
- Upload features don't work
- "Permission denied" in logs

#### Diagnosis:
```bash
# Check volume ownership on server:
ls -ln /volume1/docker/whatismyadapter/

# Should show: 1034 100 for most directories
```

#### Solutions:

**Fix Ownership:**
```bash
# SSH into server
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/app
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/logs
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/tmp
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/nginx-logs
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/redis
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/mailpit

# MySQL directory has different ownership (by design):
sudo chown -R 999:999 /volume1/docker/whatismyadapter/mysql
```

**Fix Permissions:**
```bash
# Make directories writable
sudo chmod -R 755 /volume1/docker/whatismyadapter
```

**Verify in Container:**
```bash
# Access container console
docker exec -it whatismyadapter_willowcms_1 bash

# Check user
id
# Should show: uid=1034 gid=100

# Test write access
touch /var/www/html/logs/test.txt
# Should succeed without error
```

### 11.6 Application Shows 500 Error

#### Symptoms:
- Browser shows "Internal Server Error"
- HTTP 500 status code

#### Diagnosis:
```bash
# Check application logs:
Containers → whatismyadapter_willowcms_1 → Logs

# Check error logs:
# In container console:
tail -f /var/www/html/logs/error.log
```

#### Common Causes:

**Missing SECURITY_SALT:**
```bash
# Add in environment variables:
SECURITY_SALT=[64-character random string]

# Update stack
```

**Debug Mode Off (Can't See Errors):**
```bash
# Temporarily enable debug:
DEBUG=true

# Update stack
# Check detailed error messages
# Set back to false for production
```

**PHP Configuration:**
```bash
# Access container console
php -i | grep error

# Check PHP logs
tail -f /var/log/php-fpm/error.log
```

### 11.7 Port Already in Use

#### Symptoms:
- Container won't start
- Error: "port is already allocated"

#### Diagnosis:
```bash
# SSH into server
netstat -tlnp | grep :8080
# Shows which process is using port 8080
```

#### Solutions:

**Change Port Mapping:**
```bash
# In environment variables:
WILLOW_HTTP_PORT=8090  # Use different port

# Update stack
```

**Stop Conflicting Service:**
```bash
# Find process ID (PID) from netstat output
sudo kill [PID]

# Or stop other Docker container
docker stop [container-name]
```

### 11.8 Logs Show "Health Check Failed"

#### Symptoms:
- Container repeatedly restarts
- Health status shows "unhealthy"

#### Diagnosis:
```bash
# Check health check configuration in docker-compose.yml:
healthcheck:
  test: ["CMD-SHELL", "redis-cli -a \"$REDIS_PASSWORD\" ping | grep PONG"]
  interval: 10s
  timeout: 3s
  retries: 5
```

#### Solutions:

**Increase Timeout:**
```yaml
# Edit compose file in Portainer:
healthcheck:
  timeout: 10s  # Increase from 3s
  retries: 10    # Increase retries
```

**Verify Health Check Command:**
```bash
# Access container console
# Run health check command manually

# For Redis:
redis-cli -a YOUR_PASSWORD ping
# Should return: PONG

# For MySQL:
mysqladmin -u root -p ping
# Should return: mysqld is alive
```

### 11.9 Environment Variables Not Applied

#### Symptoms:
- Application uses default values
- Configuration doesn't match expectations

#### Diagnosis:
```bash
# Check environment in running container:
docker exec whatismyadapter_willowcms_1 env | sort

# Compare with expected values
```

#### Solutions:

**Variable Names Wrong:**
```bash
# Check spelling and capitalization:
✅ MYSQL_PASSWORD=...
❌ mysql_password=...  # Wrong case
❌ MYSQL_PASWORD=...   # Typo
```

**Variables Not Loaded:**
```bash
# Verify variables show in Stack Editor:
Stacks → whatismyadapter → Editor → Environment variables

# If missing, add them and update stack
```

**Compose File Override:**
```yaml
# Check if compose file hardcodes values:
❌ password: hardcoded-password  # Bad
✅ password: ${MYSQL_PASSWORD}   # Good
```

### 11.10 Build Fails (Repository Method)

#### Symptoms:
- Deployment fails during build step
- "build failed" error message

#### Diagnosis:
```bash
# Check deployment logs:
❌ ERROR: failed to solve: context canceled
❌ ERROR: dockerfile parse error
❌ ERROR: COPY failed
```

#### Solutions:

**Build Context Issue:**
```yaml
# Verify build context in compose file:
build:
  context: .  # Relative to repository root
  dockerfile: infrastructure/docker/willowcms/Dockerfile
```

**Dockerfile Not Found:**
```bash
# Check path in compose file matches repository structure
# Repository method: context is repository root
```

**Use Pre-Built Image Instead:**
```yaml
# If build fails, use pre-built image:
# Comment out build section:
# build:
#   context: .
#   dockerfile: ...

# Add image:
image: whatismyadapter/willowcms:latest
```

**Missing Build Arguments:**
```yaml
# Ensure args are passed:
build:
  context: .
  dockerfile: infrastructure/docker/willowcms/Dockerfile
  args:
    - UID=${DOCKER_UID:-1034}
    - GID=${DOCKER_GID:-100}
```

---

## 12. Security Best Practices

### 12.1 Password Security

#### Generate Strong Passwords:
```bash
# Use these commands to generate secure passwords:
openssl rand -base64 32  # For SECURITY_SALT (32 chars)
openssl rand -base64 24  # For other passwords (24 chars)

# Or use password manager to generate
```

#### Password Requirements:
- ✅ Minimum 20 characters
- ✅ Mix of uppercase, lowercase, numbers, symbols
- ✅ Unique for each service
- ✅ Not based on dictionary words
- ❌ Never reuse passwords
- ❌ Never commit to Git

#### Secure Storage:
1. **Use password manager** (1Password, Bitwarden, LastPass)
2. **Store in Portainer secrets** (see Section 12.2)
3. **Never store in plain text files**
4. **Never commit to version control**

### 12.2 Portainer Secrets (Advanced)

Portainer supports Docker secrets for sensitive data.

#### Create Secret:
1. **Go to Secrets** (left sidebar)
2. **Click "+ Add secret"**
3. **Name:** `mysql_root_password`
4. **Secret:** [paste your password]
5. **Click "Create secret"**

#### Use in Compose File:
```yaml
secrets:
  mysql_root_password:
    external: true

services:
  mysql:
    secrets:
      - mysql_root_password
    environment:
      MYSQL_ROOT_PASSWORD_FILE: /run/secrets/mysql_root_password
```

#### Benefits:
- Passwords not stored in environment variables
- More secure than .env files
- Encrypted at rest
- Access control per secret

### 12.3 Network Isolation

#### Create Custom Network:
1. **Go to Networks** (left sidebar)
2. **Click "+ Add network"**
3. **Name:** `whatismyadapter_internal`
4. **Driver:** `bridge`
5. **Internal network:** ☑ Yes
6. **Click "Create network"**

#### Isolate Database:
```yaml
services:
  mysql:
    networks:
      - internal  # Not exposed to outside
  
  willowcms:
    networks:
      - internal  # Access to database
      - external  # Access to internet

networks:
  internal:
    internal: true
  external:
    internal: false
```

### 12.4 SSL/TLS Configuration

#### Reverse Proxy Setup (Nginx):

**Install Nginx on Host:**
```bash
sudo apt install nginx certbot python3-certbot-nginx
```

**Configure Nginx:**
```nginx
# /etc/nginx/sites-available/whatismyadapter.me

server {
    listen 80;
    server_name whatismyadapter.me;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name whatismyadapter.me;

    # SSL certificates (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/whatismyadapter.me/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/whatismyadapter.me/privkey.pem;

    # SSL settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Proxy to Docker container
    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_redirect off;
    }
}
```

**Enable Site:**
```bash
sudo ln -s /etc/nginx/sites-available/whatismyadapter.me /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

**Get SSL Certificate:**
```bash
sudo certbot --nginx -d whatismyadapter.me
```

#### Update Application URL:
```bash
# In environment variables:
APP_FULL_BASE_URL=https://whatismyadapter.me

# Update stack
```

### 12.5 Firewall Configuration

#### UFW (Uncomplicated Firewall):

```bash
# Install UFW
sudo apt install ufw

# Allow SSH (IMPORTANT - do this first!)
sudo ufw allow 22/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow Portainer (if remote access needed)
sudo ufw allow 9443/tcp

# DENY all other ports (keeps container ports internal)
# Don't expose: 8080, 3310, 8082, 8025, 8084

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

#### iptables (Advanced):

```bash
# Allow established connections
iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT

# Allow SSH
iptables -A INPUT -p tcp --dport 22 -j ACCEPT

# Allow HTTP/HTTPS
iptables -A INPUT -p tcp --dport 80 -j ACCEPT
iptables -A INPUT -p tcp --dport 443 -j ACCEPT

# Allow Portainer
iptables -A INPUT -p tcp --dport 9443 -j ACCEPT

# Drop everything else
iptables -A INPUT -j DROP

# Save rules
iptables-save > /etc/iptables/rules.v4
```

### 12.6 Regular Updates

#### Update Stack Images:
```bash
# In Portainer:
Stacks → whatismyadapter → Click "Update the stack"
☑ Re-pull images and redeploy

# Or use CLI:
docker compose pull
docker compose up -d
```

#### Schedule Weekly Updates:
```bash
# Create cron job:
crontab -e

# Add line:
0 2 * * 0 cd /volume1/docker/whatismyadapter && docker compose pull && docker compose up -d

# Runs every Sunday at 2 AM
```

### 12.7 Backup Strategy

#### Automated Backups:

**Database Backup:**
```bash
#!/bin/bash
# /usr/local/bin/backup-whatismyadapter.sh

BACKUP_DIR="/backup/whatismyadapter"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup MySQL database
docker exec whatismyadapter_mysql_1 \
  mysqldump -u root -p$MYSQL_ROOT_PASSWORD whatismyadapter_db \
  > $BACKUP_DIR/db_$DATE.sql

# Backup volumes
docker run --rm \
  -v whatismyadapter_willow_app_data:/data \
  -v $BACKUP_DIR:/backup \
  alpine tar czf /backup/app_$DATE.tar.gz -C /data .

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

**Schedule Backups:**
```bash
# Make script executable
chmod +x /usr/local/bin/backup-whatismyadapter.sh

# Add to crontab:
crontab -e

# Daily backup at 3 AM:
0 3 * * * /usr/local/bin/backup-whatismyadapter.sh >> /var/log/whatismyadapter-backup.log 2>&1
```

### 12.8 Monitoring and Alerts

#### Enable Portainer Monitoring:
1. **Go to Settings** → **Notifications**
2. **Add webhook** for stack failures
3. **Configure email** alerts (if using Portainer Business)

#### External Monitoring (Uptime Kuma):

```yaml
# Add to your stack:
services:
  uptime-kuma:
    image: louislam/uptime-kuma:latest
    ports:
      - "3001:3001"
    volumes:
      - uptime-kuma-data:/app/data
    restart: unless-stopped
```

**Configure Monitors:**
- HTTP Monitor: `https://whatismyadapter.me`
- Port Monitor: Container ports
- Docker Monitor: Container status

### 12.9 Log Management

#### Limit Log Size:

```yaml
# In docker-compose file:
services:
  willowcms:
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"
```

#### External Log Aggregation:

```yaml
# Add log aggregation service:
services:
  loki:
    image: grafana/loki:latest
    ports:
      - "3100:3100"
    volumes:
      - loki-data:/loki
    command: -config.file=/etc/loki/local-config.yaml

  promtail:
    image: grafana/promtail:latest
    volumes:
      - /var/log:/var/log
      - /var/lib/docker/containers:/var/lib/docker/containers:ro
    command: -config.file=/etc/promtail/config.yml
```

### 12.10 Security Checklist

Before going live, verify:

- ✅ All passwords are strong and unique
- ✅ DEBUG mode is OFF (`DEBUG=false`)
- ✅ Firewall configured (only 80, 443, 22 open publicly)
- ✅ SSL/TLS enabled with valid certificate
- ✅ Reverse proxy configured
- ✅ Database not exposed publicly
- ✅ phpMyAdmin only accessible internally
- ✅ Regular backups scheduled
- ✅ Monitoring configured
- ✅ Log rotation enabled
- ✅ Security updates automated
- ✅ Access control restricted in Portainer
- ✅ No secrets in Git repository
- ✅ File permissions correct (1034:100)
- ✅ Network isolation configured

---

## 🎉 Congratulations!

You've successfully deployed WillowCMS to Portainer! 

### Next Steps:

1. **Configure Domain:** Point DNS to your server
2. **Set Up SSL:** Use Let's Encrypt for free certificates
3. **Enable Backups:** Schedule automated backups
4. **Monitor:** Set up uptime monitoring
5. **Customize:** Configure your WillowCMS application

### Support Resources:

- **Documentation:** Check `portainer-stacks/README.md`
- **Deployment Guide:** Review `DEPLOY_TO_CLOUD.md`
- **Issues:** File at GitHub repository
- **Community:** Join WillowCMS community forums

---

## 📝 Quick Reference Card

### Essential URLs:
```
Portainer:          https://server:9443
Application:        https://whatismyadapter.me
Admin Panel:        https://whatismyadapter.me/admin
phpMyAdmin:         http://server:8082
Mailpit:            http://server:8025
Redis Commander:    http://server:8084
```

### Essential Commands:
```bash
# View logs
docker logs whatismyadapter_willowcms_1 -f

# Restart stack
docker compose restart

# Check status
docker compose ps

# Backup database
docker exec whatismyadapter_mysql_1 mysqldump -u root -p whatismyadapter_db > backup.sql

# Access console
docker exec -it whatismyadapter_willowcms_1 bash
```

### Environment Variables:
See Section 6 for complete list.

---

**Document Version:** 1.0
**Last Updated:** 2025-01-07
**Deployment Method:** Portainer UI
**Stack:** WillowCMS / WhatIsMyAdapter
