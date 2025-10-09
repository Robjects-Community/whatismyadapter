# 🔑 Environment Variables for WhatIsMyAdapter Cloud Deployment

## ✅ Complete Environment Variables Checklist

Copy and paste this into Portainer's "Environment variables" section, replacing placeholder values with your actual secrets.

---

## 🔴 **REQUIRED Variables** (Must be set)

```bash
# ===== CRITICAL SECURITY =====
SECURITY_SALT=YOUR-32-CHARACTER-RANDOM-STRING-HERE

# ===== DATABASE CREDENTIALS =====
MYSQL_ROOT_PASSWORD=your-secure-root-password
MYSQL_DATABASE=whatismyadapter_db
MYSQL_USER=adapter_user
MYSQL_PASSWORD=your-secure-db-password

# ===== REDIS CREDENTIALS =====
REDIS_PASSWORD=your-secure-redis-password
REDIS_USERNAME=default

# ===== APPLICATION ADMIN =====
WILLOW_ADMIN_PASSWORD=your-secure-admin-password
WILLOW_ADMIN_EMAIL=admin@whatismyadapter.me

# ===== APPLICATION URL =====
APP_FULL_BASE_URL=https://whatismyadapter.me

# ===== DOCKER USER PERMISSIONS =====
DOCKER_UID=1034
DOCKER_GID=100

# ===== REDIS COMMANDER =====
REDIS_COMMANDER_PASSWORD=your-redis-commander-password
```

---

## 🟢 **OPTIONAL Variables** (Have defaults, can override)

```bash
# ===== APPLICATION SETTINGS =====
APP_NAME=WhatIsMyAdapter
DEBUG=false
APP_DEFAULT_TIMEZONE=America/Chicago
EXPERIMENTAL_TESTS=Off

# ===== PORTS =====
WILLOW_HTTP_PORT=8080
MYSQL_PORT=3310
PMA_HTTP_PORT=8082
MAILPIT_HTTP_PORT=8025
MAILPIT_SMTP_PORT=1125
REDIS_COMMANDER_HTTP_PORT=8084

# ===== EMAIL ADDRESSES =====
EMAIL_REPLY=hello@whatismyadapter.me
EMAIL_NOREPLY=noreply@whatismyadapter.me

# ===== USER ACCOUNTS =====
WILLOW_ADMIN_USERNAME=admin
REDIS_COMMANDER_USERNAME=admin
PMA_USER=root

# ===== DOCKER IMAGES =====
WILLOWCMS_IMAGE=whatismyadapter:latest
MYSQL_IMAGE_TAG=8.0
REDIS_TAG=7.2-alpine
PHPMYADMIN_IMAGE_TAG=latest
MAILPIT_IMAGE_TAG=latest
REDIS_COMMANDER_IMAGE_TAG=latest

# ===== API KEYS (if using these features) =====
OPENAI_API_KEY=
YOUTUBE_API_KEY=
TRANSLATE_API_KEY=
```

---

## 📋 **Variable Reference**

### Required Variables Details

| Variable | Description | Example |
|----------|-------------|---------|
| **SECURITY_SALT** | CakePHP security salt (32+ chars) | `openssl rand -base64 32` |
| **MYSQL_ROOT_PASSWORD** | MySQL root password | Strong random password |
| **MYSQL_DATABASE** | Database name | `whatismyadapter_db` |
| **MYSQL_USER** | Database user | `adapter_user` |
| **MYSQL_PASSWORD** | Database user password | Strong random password |
| **REDIS_PASSWORD** | Redis password | Strong random password |
| **REDIS_USERNAME** | Redis username | `default` |
| **WILLOW_ADMIN_PASSWORD** | Admin panel password | Strong random password |
| **WILLOW_ADMIN_EMAIL** | Admin email | `admin@whatismyadapter.me` |
| **APP_FULL_BASE_URL** | Public URL | `https://whatismyadapter.me` |
| **DOCKER_UID** | User ID for file permissions | `1034` |
| **DOCKER_GID** | Group ID for file permissions | `100` |
| **REDIS_COMMANDER_PASSWORD** | Redis Commander password | Strong random password |

---

## 🔐 **Generate Secure Values**

Use these commands to generate secure passwords:

```bash
# Generate SECURITY_SALT (32+ characters)
openssl rand -base64 32

# Generate strong passwords
openssl rand -base64 24

# Or use pwgen
pwgen -s 32 1
```

---

## ⚠️ **Important Notes**

### 1. **Never Use These Placeholder Values in Production!**
```bash
❌ your-secure-password
❌ changeme
❌ password123
❌ YOUR-32-CHARACTER-RANDOM-STRING-HERE
```

### 2. **User Permissions Must Match**
```bash
DOCKER_UID=1034
DOCKER_GID=100
```
These must match the `whatismyadapter` user on your server:
```bash
# On server, verify:
id whatismyadapter
# Should show: uid=1034(whatismyadapter) gid=100(users)
```

### 3. **URL Configuration**
- **Production with reverse proxy:** `APP_FULL_BASE_URL=https://whatismyadapter.me`
- **Direct access/testing:** `APP_FULL_BASE_URL=http://localhost:8080`

### 4. **API Keys**
Leave blank if not using:
```bash
OPENAI_API_KEY=
YOUTUBE_API_KEY=
TRANSLATE_API_KEY=
```

---

## ✅ **Validation Checklist**

Before deploying, verify:

- [ ] All **REQUIRED** variables have real values (no placeholders)
- [ ] All passwords are **strong** (min 20 characters, random)
- [ ] `SECURITY_SALT` is **32+ characters**
- [ ] `APP_FULL_BASE_URL` matches your **actual domain**
- [ ] `DOCKER_UID` and `DOCKER_GID` match **server user**
- [ ] Email addresses use your **actual domain**
- [ ] All passwords are **different** (don't reuse)
- [ ] Values are **saved securely** (password manager)

---

## 🎯 **Quick Copy-Paste Template**

```bash
# REQUIRED - Replace ALL values
SECURITY_SALT=
MYSQL_ROOT_PASSWORD=
MYSQL_DATABASE=whatismyadapter_db
MYSQL_USER=adapter_user
MYSQL_PASSWORD=
REDIS_PASSWORD=
REDIS_USERNAME=default
WILLOW_ADMIN_PASSWORD=
WILLOW_ADMIN_EMAIL=admin@whatismyadapter.me
APP_FULL_BASE_URL=https://whatismyadapter.me
DOCKER_UID=1034
DOCKER_GID=100
REDIS_COMMANDER_PASSWORD=

# OPTIONAL - Customize as needed
APP_NAME=WhatIsMyAdapter
DEBUG=false
APP_DEFAULT_TIMEZONE=America/Chicago
WILLOW_HTTP_PORT=8080
MYSQL_PORT=3310
PMA_HTTP_PORT=8082
MAILPIT_HTTP_PORT=8025
REDIS_COMMANDER_HTTP_PORT=8084
EMAIL_REPLY=hello@whatismyadapter.me
EMAIL_NOREPLY=noreply@whatismyadapter.me
```

---

## 🔄 **Environment Variable Priority**

Portainer loads variables in this order (last wins):

1. **Default values** in docker-compose.yml (e.g., `${VAR:-default}`)
2. **Environment variables** added in Portainer UI
3. **.env file** (if using "Load variables from .env file")

**Recommendation:** Set all required variables in Portainer UI for maximum control.

---

## 📝 **Example: Complete Set**

```bash
# SECURITY
SECURITY_SALT=Xk9mP2vN8qL4wR7sT1hG5bY3dF6jC0zA

# DATABASE
MYSQL_ROOT_PASSWORD=RootPass_2024_Secure!
MYSQL_DATABASE=whatismyadapter_db
MYSQL_USER=adapter_user
MYSQL_PASSWORD=DbUser_2024_Strong!

# REDIS
REDIS_PASSWORD=Redis_2024_Secure!
REDIS_USERNAME=default

# ADMIN
WILLOW_ADMIN_PASSWORD=Admin_2024_Strong!
WILLOW_ADMIN_EMAIL=admin@whatismyadapter.me

# APPLICATION
APP_FULL_BASE_URL=https://whatismyadapter.me
APP_NAME=WhatIsMyAdapter
DEBUG=false

# PERMISSIONS
DOCKER_UID=1034
DOCKER_GID=100

# REDIS COMMANDER
REDIS_COMMANDER_USERNAME=admin
REDIS_COMMANDER_PASSWORD=Commander_2024!

# EMAIL
EMAIL_REPLY=hello@whatismyadapter.me
EMAIL_NOREPLY=noreply@whatismyadapter.me

# TIMEZONE
APP_DEFAULT_TIMEZONE=America/Chicago
```

---

**Need help? Check DEPLOY_TO_CLOUD.md for full deployment guide!**
