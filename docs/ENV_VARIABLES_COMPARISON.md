# Environment Variables Comparison

**Date:** 2025-10-04  
**Purpose:** Ensure all environment variables from `config/.env` are properly handled in cloud deployment

---

## Overview

This document compares environment variables across three key files:
1. **`config/.env`** - Local development configuration
2. **`stack.env.cloud`** - Cloud/production configuration
3. **`docker-compose-cloud.yml`** - Service definitions

---

## Variable Mapping Status

### ✅ Core Application Variables

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `APP_NAME` | ✅ WillowCMS | ✅ WhatIsMyAdapter | ✅ Used | ✅ Synced |
| `APP_ENV` | ✅ development | ✅ production | ❌ Not referenced | ✅ Available |
| `DEBUG` | ✅ true | ✅ false | ✅ Used | ✅ Synced |
| `APP_ENCODING` | ✅ UTF-8 | ✅ UTF-8 | ✅ Used | ✅ Synced |
| `APP_DEFAULT_LOCALE` | ✅ en_GB | ✅ en_US | ✅ Used | ⚠️ Different |
| `APP_DEFAULT_TIMEZONE` | ✅ America/Chicago | ✅ America/Chicago | ✅ Used | ✅ Synced |
| `APP_FULL_BASE_URL` | ✅ localhost:8080 | ✅ whatismyadapter.robjects.me | ✅ Used | ✅ Synced |
| `SECURITY_SALT` | ✅ Set | ✅ Placeholder | ✅ Used | ⚠️ Need to replace |

### ✅ Database Configuration

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `MYSQL_ROOT_PASSWORD` | ✅ password | ✅ Placeholder | ✅ Used | ⚠️ Need to replace |
| `MYSQL_DATABASE` | ✅ cms | ✅ whatismyadapter_db | ✅ Used | ⚠️ Different naming |
| `MYSQL_USER` | ✅ root | ✅ whatismyadapter_user | ✅ Used | ⚠️ Different |
| `MYSQL_PASSWORD` | ✅ password | ✅ Placeholder | ✅ Used | ⚠️ Need to replace |
| `MYSQL_IMAGE_TAG` | ❌ Not set | ✅ 8.0 | ✅ Used | ✅ Added |
| `MYSQL_PORT` | ❌ Not set | ✅ 3310 | ✅ Used | ✅ Added |
| `DB_HOST` | ✅ mysql | ❌ Not set | ✅ Hardcoded | ✅ Synced |
| `DB_DATABASE` | ✅ cms | ❌ Uses MYSQL_DATABASE | ✅ Used | ✅ Synced |
| `DB_USERNAME` | ✅ cms_user | ❌ Uses MYSQL_USER | ✅ Used | ⚠️ Different |
| `DB_PASSWORD` | ✅ password | ❌ Uses MYSQL_PASSWORD | ✅ Used | ✅ Synced |
| `DB_PORT` | ✅ 3306 | ❌ Not set | ✅ Hardcoded | ✅ Synced |

### ✅ Test Database Configuration

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `TEST_DB_HOST` | ✅ mysql | ❌ Not set | ✅ Hardcoded | ✅ Synced |
| `TEST_DB_DATABASE` | ✅ cms_test | ❌ Not set | ✅ Generated | ✅ Synced |
| `TEST_DB_USERNAME` | ✅ cms_user_test | ❌ Uses MYSQL_USER | ✅ Used | ✅ Synced |
| `TEST_DB_PASSWORD` | ✅ password | ❌ Uses MYSQL_PASSWORD | ✅ Used | ✅ Synced |
| `TEST_DB_PORT` | ✅ 3306 | ❌ Not set | ✅ Hardcoded | ✅ Synced |

### ✅ Redis Configuration

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `REDIS_HOST` | ✅ ********* | ❌ Not set | ✅ Hardcoded as 'redis' | ✅ Synced |
| `REDIS_PORT` | ✅ 6379 | ✅ 6379 | ✅ Used | ✅ Synced |
| `REDIS_USERNAME` | ✅ root | ✅ default | ✅ Used | ⚠️ Different |
| `REDIS_PASSWORD` | ✅ root | ✅ Placeholder | ✅ Used | ⚠️ Need to replace |
| `REDIS_DATABASE` | ✅ 0 | ✅ 0 | ✅ Used | ✅ Synced |
| `REDIS_URL` | ✅ Set | ❌ Not set | ✅ Generated | ✅ Synced |
| `REDIS_TEST_URL` | ✅ Set | ❌ Not set | ✅ Generated | ✅ Synced |
| `REDIS_TAG` | ❌ Not set | ✅ 7.2-alpine | ✅ Used | ✅ Added |

### ✅ Queue Configuration

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `QUEUE_DEFAULT_URL` | ✅ Set | ❌ Not set | ✅ Generated | ✅ Synced |
| `QUEUE_TEST_URL` | ✅ Set | ❌ Not set | ✅ Generated | ✅ Synced |

### ✅ Email Configuration

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `EMAIL_HOST` | ✅ mailpit | ✅ mailpit | ✅ Hardcoded | ✅ Synced |
| `EMAIL_PORT` | ✅ 1025 | ✅ 1025 | ✅ Hardcoded | ✅ Synced |
| `EMAIL_TIMEOUT` | ✅ 30 | ✅ 30 | ✅ Used | ✅ Synced |
| `EMAIL_USERNAME` | ✅ Empty | ✅ Empty | ❌ Not used | ✅ Synced |
| `EMAIL_PASSWORD` | ✅ Empty | ✅ Empty | ❌ Not used | ✅ Synced |
| `EMAIL_REPLY` | ✅ willowcms.robjects.me | ✅ whatismyadapter.robjects.me | ✅ Used | ⚠️ Different domain |
| `EMAIL_NOREPLY` | ✅ willowcms.robjects.me | ✅ whatismyadapter.robjects.me | ✅ Used | ⚠️ Different domain |

### ✅ Admin Configuration

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `WILLOW_ADMIN_USERNAME` | ✅ admin | ✅ admin | ✅ Used | ✅ Synced |
| `WILLOW_ADMIN_PASSWORD` | ✅ password | ✅ Placeholder | ✅ Used | ⚠️ Need to replace |
| `WILLOW_ADMIN_EMAIL` | ✅ admin@test.com | ✅ admin@whatismyadapter.robjects.me | ✅ Used | ⚠️ Different domain |

### ✅ phpMyAdmin Configuration

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `PMA_HOST` | ✅ mysql | ✅ mysql | ✅ Hardcoded | ✅ Synced |
| `PMA_USER` | ✅ root | ✅ root | ✅ Used | ✅ Synced |
| `PMA_PASSWORD` | ✅ password | ✅ Placeholder (must match MYSQL_ROOT_PASSWORD) | ✅ Used | ⚠️ Need to replace |
| `UPLOAD_LIMIT` | ✅ 1024M | ✅ 300M | ✅ Used | ⚠️ Different |
| `PHPMYADMIN_IMAGE_TAG` | ❌ Not set | ✅ latest | ✅ Used | ✅ Added |

### ✅ Mailpit Configuration

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `MP_MAX_MESSAGES` | ✅ 5000 | ✅ 5000 | ✅ Used | ✅ Synced |
| `MP_DATABASE` | ✅ /data/mailpit.db | ❌ Not set | ✅ Hardcoded | ✅ Synced |
| `MP_SMTP_AUTH_ACCEPT_ANY` | ✅ 1 | ✅ 1 | ✅ Used | ✅ Synced |
| `MP_SMTP_AUTH_ALLOW_INSECURE` | ✅ 1 | ✅ 1 | ✅ Used | ✅ Synced |
| `MAILPIT_IMAGE_TAG` | ❌ Not set | ✅ latest | ✅ Used | ✅ Added |
| `MAILPIT_SMTP_PORT` | ❌ Not set | ✅ 1125 | ✅ Used | ✅ Added |
| `MAILPIT_HTTP_PORT` | ❌ Not set | ✅ 8025 | ✅ Used | ✅ Added |

### ✅ Redis Commander Configuration

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `HTTP_USER` | ✅ root | ❌ Not set | ❌ Not used | ⚠️ Deprecated |
| `HTTP_PASSWORD` | ✅ root | ❌ Not set | ❌ Not used | ⚠️ Deprecated |
| `REDIS_COMMANDER_USERNAME` | ✅ root | ✅ admin | ✅ Used | ⚠️ Different |
| `REDIS_COMMANDER_PASSWORD` | ✅ root | ✅ Placeholder | ✅ Used | ⚠️ Need to replace |
| `REDIS_COMMANDER_HOST` | ✅ willowcms | ❌ Not set | ✅ Hardcoded as 'redis' | ✅ Synced |
| `REDIS_COMMANDER_PORT` | ✅ 6379 | ❌ Not set | ✅ Hardcoded | ✅ Synced |
| `REDIS_COMMANDER_IMAGE_TAG` | ❌ Not set | ✅ latest | ✅ Used | ✅ Added |
| `REDIS_COMMANDER_HTTP_PORT` | ❌ Not set | ✅ 8084 | ✅ Used | ✅ Added |

### ✅ API Keys

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `YOUTUBE_API_KEY` | ✅ Set | ✅ Empty | ✅ Used | ✅ Synced |
| `TRANSLATE_API_KEY` | ✅ Set | ✅ Empty | ✅ Used | ✅ Synced |
| `OPENAI_API_KEY` | ❌ Not set | ✅ Empty | ✅ Used | ✅ Added |
| `OPEN_AI_API_KEY` | ✅ Set | ✅ Empty | ❌ Not used | ✅ Added |
| `ANTHROPIC_API_KEY` | ✅ Set | ✅ Empty | ❌ Not used | ✅ Added |
| `DIGITAL_OCEAN_KEY` | ✅ Set | ✅ Empty | ❌ Not used | ✅ Added |
| `UNSPLASH_ACCESS_KEY` | ✅ Placeholder | ✅ Empty | ❌ Not used | ✅ Added |
| `PERPLEXITY_API_KEY` | ✅ Placeholder | ✅ Empty | ❌ Not used | ✅ Added |

### ✅ Docker Configuration

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `DOCKER_UID` | ✅ 501 | ✅ 1034 | ✅ Used | ⚠️ Different (cloud uses 1034) |
| `DOCKER_GID` | ✅ 20 | ✅ 100 | ✅ Used | ⚠️ Different (cloud uses 100) |
| `WILLOWCMS_IMAGE` | ✅ ghcr.io/... | ✅ whatismyadapter:main-clean-hardened | ✅ Used | ⚠️ Different |
| `TAG` | ✅ pre-willowcms-beta | ✅ latest | ❌ Not used | ✅ Added |

### ✅ Port Mappings (Cloud-specific)

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `WILLOW_HTTP_PORT` | ❌ Not set | ✅ 8080 | ✅ Used | ✅ Added |
| `WILLOW_HTTPS_PORT` | ❌ Not set | ✅ 8443 | ❌ Not used | ✅ Available |
| `PMA_HTTP_PORT` | ❌ Not set | ✅ 8082 | ✅ Used | ✅ Added |

### ✅ Logging Configuration (Cloud-specific)

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `LOG_DEBUG_PATH` | ❌ Not set | ✅ /var/www/html/logs | ✅ Used | ✅ Added |
| `LOG_ERROR_PATH` | ❌ Not set | ✅ /var/www/html/logs | ✅ Used | ✅ Added |
| `LOG_QUERIES_PATH` | ❌ Not set | ✅ /var/www/html/logs | ✅ Used | ✅ Added |
| `LOG_ADMIN_ACTIONS_PATH` | ❌ Not set | ✅ /var/www/html/logs | ✅ Used | ✅ Added |
| `LOG_DEBUG_FILE` | ❌ Not set | ✅ debug | ✅ Used | ✅ Added |
| `LOG_ERROR_FILE` | ❌ Not set | ✅ error | ✅ Used | ✅ Added |
| `LOG_QUERIES_FILE` | ❌ Not set | ✅ queries | ✅ Used | ✅ Added |
| `LOG_ADMIN_ACTIONS_FILE` | ❌ Not set | ✅ bulk_actions | ✅ Used | ✅ Added |
| `LOG_DEBUG_LEVELS` | ❌ Not set | ✅ notice,info,debug | ✅ Used | ✅ Added |
| `LOG_ERROR_LEVELS` | ❌ Not set | ✅ warning,error,critical,alert,emergency | ✅ Used | ✅ Added |
| `LOG_LEVEL` | ✅ debug | ✅ warning | ❌ Not used | ✅ Added |

### ✅ Feature Flags

| Variable | config/.env | stack.env.cloud | docker-compose-cloud.yml | Status |
|----------|-------------|-----------------|--------------------------|--------|
| `EXPERIMENTAL_TESTS` | ✅ On | ✅ Off | ✅ Used | ⚠️ Different (production = Off) |

### ❌ Variables in config/.env NOT in stack.env.cloud

| Variable | config/.env Value | Reason Not Included | Action Needed |
|----------|-------------------|---------------------|---------------|
| `JENKINS_*` | Various | Jenkins not used in cloud | ✅ OK - Not needed |

---

## Key Differences Explained

### 1. **Database Naming Convention**

**Local (`config/.env`):**
- `MYSQL_DATABASE=cms`
- `DB_USERNAME=cms_user`

**Cloud (`stack.env.cloud`):**
- `MYSQL_DATABASE=whatismyadapter_db`
- `MYSQL_USER=whatismyadapter_user`

**Why:** Cloud deployment uses descriptive naming that matches the project name for clarity in production.

### 2. **Docker UID/GID**

**Local (`config/.env`):**
- `DOCKER_UID=501` (macOS user ID)
- `DOCKER_GID=20` (macOS staff group)

**Cloud (`stack.env.cloud`):**
- `DOCKER_UID=1034` (cloud server user `whatismyadapter`)
- `DOCKER_GID=100` (cloud server users group)

**Why:** Different users on different systems. Cloud server has specific user with UID 1034.

### 3. **Redis Username**

**Local (`config/.env`):**
- `REDIS_USERNAME=root`

**Cloud (`stack.env.cloud`):**
- `REDIS_USERNAME=default`

**Why:** Cloud uses Redis default authentication mechanism for better security practices.

### 4. **Email Domains**

**Local (`config/.env`):**
- `EMAIL_REPLY=hello@willowcms.robjects.me`

**Cloud (`stack.env.cloud`):**
- `EMAIL_REPLY=hello@whatismyadapter.robjects.me`

**Why:** Cloud deployment uses the actual production domain.

---

## Variables That MUST Be Set Before Cloud Deployment

### Critical (Security):
1. `SECURITY_SALT` - Generate with `openssl rand -base64 32`
2. `MYSQL_ROOT_PASSWORD` - Generate with `openssl rand -base64 24`
3. `MYSQL_PASSWORD` - Generate with `openssl rand -base64 24`
4. `REDIS_PASSWORD` - Generate with `openssl rand -base64 24`
5. `WILLOW_ADMIN_PASSWORD` - Generate with `openssl rand -base64 24`
6. `REDIS_COMMANDER_PASSWORD` - Generate with `openssl rand -base64 24`

### Optional (API Keys - if using services):
- `YOUTUBE_API_KEY`
- `TRANSLATE_API_KEY`
- `OPENAI_API_KEY` or `OPEN_AI_API_KEY`
- `ANTHROPIC_API_KEY`
- `DIGITAL_OCEAN_KEY`
- `UNSPLASH_ACCESS_KEY`
- `PERPLEXITY_API_KEY`

---

## Compatibility Summary

### ✅ Fully Compatible:
- All core application variables are present
- All database connection variables are mapped
- All Redis configuration is complete
- Email configuration is complete
- Admin user configuration is complete

### ⚠️ Requires Attention:
1. **Passwords:** All placeholder passwords must be replaced
2. **Database Names:** Cloud uses different naming (acceptable, documented)
3. **UID/GID:** Different between local/cloud (expected, documented)
4. **API Keys:** Must be set if using external services

### 🎯 Recommendation:
The `stack.env.cloud` file is **fully compatible** with `docker-compose-cloud.yml` and includes all necessary variables from `config/.env`. The differences are intentional and appropriate for a production cloud deployment.

---

## Validation Checklist

Before deploying to cloud:

- [ ] Run validation script: `./tools/deployment/validate-cloud-env.sh stack.env.cloud`
- [ ] Replace all password placeholders
- [ ] Verify domain is set to `whatismyadapter.robjects.me`
- [ ] Confirm UID/GID are 1034:100 for cloud server
- [ ] Set API keys if using external services
- [ ] Double-check `PMA_PASSWORD` matches `MYSQL_ROOT_PASSWORD`

---

**Conclusion:** All required environment variables from `config/.env` are accounted for in `stack.env.cloud`. The configuration is production-ready once passwords are set.
