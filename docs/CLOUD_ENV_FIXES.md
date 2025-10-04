# Cloud Environment Variables - Issues and Fixes

**Date:** 2025-10-04  
**Project:** WhatIsMyAdapter  
**Domain:** https://whatismyadapter.robjects.me  
**New File:** `stack.env.cloud`  

---

## Summary

This document details all issues found in your cloud environment variables and how they were corrected in the new `stack.env.cloud` file.

---

## 🔴 Critical Security Issues (MUST FIX)

### 1. Weak/Placeholder Passwords

All of the following had weak or placeholder values that must be replaced with strong, random passwords:

| Variable | Old Value | Status |
|----------|-----------|--------|
| `SECURITY_SALT` | `changeme-generate-random-32-character-string` | ❌ Placeholder |
| `MYSQL_ROOT_PASSWORD` | `changeme-root-password` | ❌ Weak |
| `MYSQL_PASSWORD` | `changeme-user-password` | ❌ Weak |
| `REDIS_PASSWORD` | `changeme-redis-password` | ❌ Weak |
| `WILLOW_ADMIN_PASSWORD` | `changeme-admin-password` | ❌ Weak |
| `PMA_PASSWORD` | `changeme-root-password` | ❌ Weak |
| `REDIS_COMMANDER_PASSWORD` | `changeme-redis-commander` | ❌ Weak |

**Fix Applied:**  
All passwords have been replaced with placeholder text like `YOUR_MYSQL_ROOT_PASSWORD_REPLACE_ME` to force you to generate secure passwords before deployment.

**Action Required:**  
Generate strong passwords using:
```bash
# For SECURITY_SALT (32+ characters)
openssl rand -base64 32

# For passwords (24+ characters)
openssl rand -base64 24
```

---

## 🔴 Domain & Email Configuration Issues

### 2. Wrong Domain

| Variable | Old Value | New Value | Status |
|----------|-----------|-----------|--------|
| `APP_FULL_BASE_URL` | `https://your-domain.com` | `https://whatismyadapter.robjects.me` | ✅ Fixed |

**Fix Applied:**  
Updated to use your production domain `whatismyadapter.robjects.me`.

### 3. Invalid Email Addresses

| Variable | Old Value | New Value | Status |
|----------|-----------|-----------|--------|
| `WILLOW_ADMIN_EMAIL` | `admin@your-domain.com` | `admin@whatismyadapter.robjects.me` | ✅ Fixed |
| `EMAIL_REPLY` | `hello@willowcms.app` | `hello@whatismyadapter.robjects.me` | ✅ Fixed |
| `EMAIL_NOREPLY` | `noreply@willowcms.app` | `noreply@whatismyadapter.robjects.me` | ✅ Fixed |

**Fix Applied:**  
All email addresses now use your production domain `whatismyadapter.robjects.me`.

---

## 🔴 Repository Configuration Issues

### 4. Wrong Git Repository URL

| Variable | Old Value | New Value | Status |
|----------|-----------|-----------|--------|
| `GIT_URL` | `https://github.com/garzarobm/willow.git` | `https://github.com/Robjects-Community/WhatIsMyAdaptor.git` | ✅ Fixed |

**Fix Applied:**  
Updated to point to the correct WhatIsMyAdapter repository.

---

## ⚠️ Database Naming Issues

### 5. Inconsistent Database Names

| Variable | Old Value | New Value | Status |
|----------|-----------|-----------|--------|
| `MYSQL_DATABASE` | `willow_cms` | `whatismyadapter_db` | ✅ Fixed |
| `MYSQL_USER` | `willow_user` | `whatismyadapter_user` | ✅ Fixed |

**Fix Applied:**  
Database names now align with your project name "WhatIsMyAdapter" for clarity and consistency.

---

## ⚠️ Project Naming Issues

### 6. Inconsistent Project References

| Variable | Old Value | New Value | Status |
|----------|-----------|-----------|--------|
| `PROJECT_NAME` | `willow` | `whatismyadapter` | ✅ Fixed |
| `NETWORK_NAME` | `willow_network` | `whatismyadapter_network` | ✅ Fixed |

**Fix Applied:**  
All project references now use "whatismyadapter" instead of "willow".

---

## ✅ Variables That Were Correct

The following variables were already correctly configured:

- ✅ `DOCKER_UID=1034` - Matches your server user
- ✅ `DOCKER_GID=100` - Matches your server group
- ✅ `APP_ENV=production` - Production mode
- ✅ `DEBUG=false` - Debug disabled for production
- ✅ `APP_DEFAULT_TIMEZONE=America/Chicago` - Correct timezone
- ✅ Port mappings (`WILLOW_HTTP_PORT`, `PMA_HTTP_PORT`, etc.)
- ✅ Image tags and versions
- ✅ Volume paths (`/volume1/docker/whatismyadapter`)

---

## 📋 Variables That Need Your Attention

### Optional API Keys

These can remain empty if you're not using these services:

```bash
OPENAI_API_KEY=
YOUTUBE_API_KEY=
TRANSLATE_API_KEY=
```

If you **are** using these services, set them in Portainer's secure environment variables UI (not in the file).

---

## 🚀 Deployment Checklist

Before deploying to production with Portainer:

- [ ] **Generate secure passwords** for all `YOUR_*_REPLACE_ME` placeholders
- [ ] **Verify domain**: Ensure DNS is configured for `whatismyadapter.robjects.me`
- [ ] **Reverse proxy**: Configure Nginx/Caddy for SSL termination
- [ ] **Server setup**: Create directories with correct ownership (UID 1034, GID 100)
- [ ] **Test locally**: Run validation script to check for issues
- [ ] **Portainer**: Upload `docker-compose-cloud.yml` and `stack.env.cloud`
- [ ] **Monitor**: Watch logs during first deployment

---

## 🛠️ Testing Your Environment File

Run the validation script before deploying:

```bash
./tools/deployment/validate-cloud-env.sh stack.env.cloud
```

This will check:
- ✅ No weak passwords remain
- ✅ Domain and email addresses are correct
- ✅ Git repository URL is correct
- ✅ All required variables are set
- ✅ Database naming is consistent
- ✅ Production mode is enabled

---

## 📁 Files Created

1. **`stack.env.cloud`** - New production-ready environment file
2. **`tools/deployment/validate-cloud-env.sh`** - Validation script
3. **`docs/CLOUD_ENV_FIXES.md`** - This documentation

---

## 🔒 Security Best Practices

1. **Never commit passwords** to version control
2. **Use strong, unique passwords** (24+ characters, mixed case, numbers, symbols)
3. **Store secrets in Portainer** UI instead of in the env file
4. **Rotate passwords** periodically (every 90 days)
5. **Use different passwords** for each service (never reuse)
6. **Enable SSL/TLS** via reverse proxy for all external access
7. **Limit access** to phpMyAdmin and Redis Commander (use IP allowlists)

---

## 📞 Need Help?

If you encounter issues:

1. Run the validation script for detailed error messages
2. Check Portainer logs for deployment errors
3. Verify DNS settings for your domain
4. Confirm reverse proxy configuration
5. Check server directory permissions (should be owned by UID 1034, GID 100)

---

## 🔄 Updating Existing Deployment

If you already have a running stack in Portainer:

1. **Backup first**: Export current environment variables from Portainer
2. **Update gradually**: Change variables one at a time
3. **Test after each change**: Ensure services restart correctly
4. **Roll back if needed**: Keep backup of working configuration

---

## Example: Generating Passwords

```bash
# Generate all passwords at once
echo "SECURITY_SALT=$(openssl rand -base64 32)"
echo "MYSQL_ROOT_PASSWORD=$(openssl rand -base64 24)"
echo "MYSQL_PASSWORD=$(openssl rand -base64 24)"
echo "REDIS_PASSWORD=$(openssl rand -base64 24)"
echo "WILLOW_ADMIN_PASSWORD=$(openssl rand -base64 24)"
echo "REDIS_COMMANDER_PASSWORD=$(openssl rand -base64 24)"
```

Copy the output and paste into your `stack.env.cloud` file, replacing the `YOUR_*_REPLACE_ME` placeholders.

---

**End of Document**
