# Quick Start: Cloud Deployment with Corrected Environment Variables

**Domain:** https://whatismyadapter.robjects.me  
**Project:** WhatIsMyAdapter  
**Last Updated:** 2025-10-04

---

## 🚀 Quick Deployment Steps

### Step 1: Generate Secure Passwords

Run this command to generate all required passwords:

```bash
echo "# Copy these values into stack.env.cloud"
echo ""
echo "SECURITY_SALT=$(openssl rand -base64 32)"
echo "MYSQL_ROOT_PASSWORD=$(openssl rand -base64 24)"
echo "MYSQL_PASSWORD=$(openssl rand -base64 24)"
echo "REDIS_PASSWORD=$(openssl rand -base64 24)"
echo "WILLOW_ADMIN_PASSWORD=$(openssl rand -base64 24)"
echo "REDIS_COMMANDER_PASSWORD=$(openssl rand -base64 24)"
```

**Copy the output and save it securely** (password manager, encrypted notes, etc.)

---

### Step 2: Update Environment File

Edit `stack.env.cloud` and replace all placeholders:

```bash
# Open in your editor
nano stack.env.cloud

# Or use VS Code
code stack.env.cloud
```

Find and replace these values with the passwords you generated:
- `YOUR_RANDOM_32_CHAR_SALT_REPLACE_ME_NOW`
- `YOUR_MYSQL_ROOT_PASSWORD_REPLACE_ME`
- `YOUR_MYSQL_USER_PASSWORD_REPLACE_ME`
- `YOUR_REDIS_PASSWORD_REPLACE_ME`
- `YOUR_ADMIN_PASSWORD_REPLACE_ME`
- `YOUR_REDIS_COMMANDER_PASSWORD_REPLACE_ME`

**Important:** `PMA_PASSWORD` must match `MYSQL_ROOT_PASSWORD`

---

### Step 3: Validate Configuration

Run the validation script to check for issues:

```bash
./tools/deployment/validate-cloud-env.sh stack.env.cloud
```

**You should see:**
```
✓ All checks passed! Environment file is ready for deployment.
```

If you see errors, fix them before proceeding.

---

### Step 4: Prepare Cloud Server

SSH into your cloud server and set up directories:

```bash
# SSH to your server
ssh whatismyadapter@your-server-ip

# Create required directories
sudo mkdir -p /volume1/docker/whatismyadapter/{app,logs,nginx-logs,tmp,mysql,redis,mailpit}

# Set ownership (UID 1034, GID 100)
sudo chown -R 1034:100 /volume1/docker/whatismyadapter

# Set permissions
sudo chmod -R 755 /volume1/docker/whatismyadapter
```

---

### Step 5: Configure DNS

Set up DNS record for your domain:

**Type:** A Record  
**Name:** `whatismyadapter` (or subdomain)  
**Value:** Your server's IP address  
**TTL:** 3600 (or default)

**Verify DNS propagation:**
```bash
dig whatismyadapter.robjects.me +short
# Should return your server IP
```

---

### Step 6: Set Up Reverse Proxy

#### Option A: Nginx with Let's Encrypt

```nginx
# /etc/nginx/sites-available/whatismyadapter.robjects.me

server {
    listen 80;
    server_name whatismyadapter.robjects.me;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name whatismyadapter.robjects.me;
    
    # SSL Certificate (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/whatismyadapter.robjects.me/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/whatismyadapter.robjects.me/privkey.pem;
    
    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    
    # Proxy to Docker container
    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # WebSocket support (if needed)
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

**Enable site and get SSL certificate:**
```bash
# Get SSL certificate
sudo certbot --nginx -d whatismyadapter.robjects.me

# Enable site
sudo ln -s /etc/nginx/sites-available/whatismyadapter.robjects.me /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

#### Option B: Caddy (Automatic HTTPS)

```caddy
# /etc/caddy/Caddyfile

whatismyadapter.robjects.me {
    reverse_proxy localhost:8080
    
    # Security headers
    header {
        Strict-Transport-Security "max-age=31536000; includeSubDomains"
        X-Content-Type-Options "nosniff"
        X-Frame-Options "SAMEORIGIN"
    }
    
    # Logging
    log {
        output file /var/log/caddy/whatismyadapter.log
    }
}
```

**Reload Caddy:**
```bash
sudo systemctl reload caddy
```

---

### Step 7: Deploy to Portainer

1. **Log into Portainer** at your Portainer URL

2. **Create/Update Stack:**
   - Click **Stacks** → **Add Stack**
   - Name: `whatismyadapter`
   - Build method: **Repository** or **Web editor**

3. **If using Repository:**
   - Repository URL: `https://github.com/Robjects-Community/WhatIsMyAdaptor.git`
   - Repository reference: `main-clean`
   - Compose path: `docker-compose-cloud.yml`

4. **If using Web Editor:**
   - Copy contents of `docker-compose-cloud.yml` and paste

5. **Load Environment Variables:**
   - Click **Load variables from .env file**
   - Upload `stack.env.cloud`
   - OR manually enter variables in Portainer UI

6. **Deploy:**
   - Click **Deploy the stack**
   - Monitor deployment logs for errors

---

### Step 8: Verify Deployment

Check that all services are running:

```bash
# Via Portainer UI: check container status

# Or via SSH on server:
docker ps

# Check logs
docker logs whatismyadapter-willowcms-1
docker logs whatismyadapter-mysql-1
docker logs whatismyadapter-redis-1
```

---

### Step 9: Test Access

1. **Main Application:**  
   https://whatismyadapter.robjects.me

2. **phpMyAdmin:**  
   http://your-server-ip:8082

3. **Redis Commander:**  
   http://your-server-ip:8084

4. **Mailpit (email testing):**  
   http://your-server-ip:8025

---

## 🔍 Troubleshooting

### Issue: "Can't connect to MySQL"

**Check:**
```bash
docker logs whatismyadapter-mysql-1
```

**Solution:** Ensure `MYSQL_ROOT_PASSWORD` and `MYSQL_PASSWORD` are set correctly

---

### Issue: "502 Bad Gateway" on domain

**Check:**
1. Is the container running?
   ```bash
   docker ps | grep willowcms
   ```

2. Is the reverse proxy configured correctly?
   ```bash
   sudo nginx -t
   # or
   sudo caddy validate
   ```

3. Can you access directly?
   ```bash
   curl http://localhost:8080
   ```

---

### Issue: SSL certificate errors

**For Let's Encrypt:**
```bash
sudo certbot renew --dry-run
sudo certbot certificates
```

---

### Issue: Permission errors in logs

**Fix directory ownership:**
```bash
sudo chown -R 1034:100 /volume1/docker/whatismyadapter
```

---

## 📋 Post-Deployment Checklist

After successful deployment, verify:

- [ ] Main site loads at https://whatismyadapter.robjects.me
- [ ] SSL certificate is valid (green padlock)
- [ ] phpMyAdmin accessible and can connect to database
- [ ] Redis Commander accessible
- [ ] Application logs show no errors
- [ ] Database is properly initialized
- [ ] Admin account works (login test)
- [ ] Email functionality working (check Mailpit)

---

## 🔒 Security Hardening (Post-Deployment)

1. **Restrict phpMyAdmin access:**
   - Add IP allowlist in Nginx/Caddy
   - Or use SSH tunnel: `ssh -L 8082:localhost:8082 user@server`

2. **Restrict Redis Commander access:**
   - Same as phpMyAdmin

3. **Set up backups:**
   ```bash
   # Database backup
   docker exec whatismyadapter-mysql-1 mysqldump -u root -p whatismyadapter_db > backup.sql
   ```

4. **Monitor logs regularly:**
   ```bash
   tail -f /volume1/docker/whatismyadapter/logs/*.log
   ```

---

## 📚 Additional Resources

- **Full Documentation:** `docs/CLOUD_ENV_FIXES.md`
- **Deployment Guide:** `DEPLOY_TO_CLOUD.md`
- **Validation Script:** `tools/deployment/validate-cloud-env.sh`
- **Docker Compose:** `docker-compose-cloud.yml`

---

## 🆘 Need Help?

If you run into issues:

1. Check the validation script output
2. Review Portainer container logs
3. Check reverse proxy logs
4. Verify DNS settings
5. Confirm firewall rules allow ports 80, 443, 8080

---

**Ready to deploy?** Follow the steps above, and you'll have a secure, production-ready deployment! 🎉
