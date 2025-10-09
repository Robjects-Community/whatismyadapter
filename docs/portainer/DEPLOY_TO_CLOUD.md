# 🚀 Deploy WhatIsMyAdapter to Cloud Portainer

## 📋 Quick Deploy Steps

### **Step 1: Prepare Host Directories (SSH into your cloud server)**

```bash
# Create the whatismyadapter user (UID:1034, GID:100)
sudo groupadd -g 100 users 2>/dev/null || true  # Create group if not exists
sudo useradd -u 1034 -g 100 -m -s /bin/bash whatismyadapter || true

# Create directory structure
sudo mkdir -p /volume1/docker/whatismyadapter/{app,logs,nginx-logs,tmp,mysql,redis,mailpit}

# Set ownership to whatismyadapter user (UID:1034, GID:100)
sudo chown -R 1034:100 /volume1/docker/whatismyadapter

# Set proper permissions
sudo chmod -R 755 /volume1/docker/whatismyadapter

# Verify ownership
ls -ln /volume1/docker/whatismyadapter
```

**Expected output:** All directories should show `1034 100` as owner.

### **Step 2: Access Portainer**
Open your cloud Portainer URL (e.g., `https://your-server-ip:9443`)

### **Step 3: Create Stack Using Repository**

1. Click **"Stacks"** → **"+ Add stack"**
2. **Name:** `whatismyadapter`
3. **Build method:** Select **"Repository"**
4. **Repository configuration:**
   - **Repository URL:** `https://github.com/Robjects-Community/WhatIsMyAdaptor.git`
   - **Repository reference:** `main-clean` (or `portainer-stack`)
   - **Compose path:** `portainer-stacks/docker-compose-cloud.yml`

### **Step 4: Add Environment Variables**

Scroll to **"Environment variables"** and add these:

```bash
# CRITICAL - Required
SECURITY_SALT=YOUR-32-CHARACTER-RANDOM-STRING-HERE
MYSQL_ROOT_PASSWORD=your-secure-root-password
MYSQL_DATABASE=whatismyadapter_db
MYSQL_USER=adapter_user
MYSQL_PASSWORD=your-secure-db-password
REDIS_PASSWORD=your-secure-redis-password
REDIS_USERNAME=default
WILLOW_ADMIN_PASSWORD=your-secure-admin-password
WILLOW_ADMIN_EMAIL=admin@whatismyadapter.me

# Application Settings
APP_NAME=WhatIsMyAdapter
APP_FULL_BASE_URL=https://whatismyadapter.me
DEBUG=false

# User Permissions (IMPORTANT - must match server user)
DOCKER_UID=1034
DOCKER_GID=100

# Ports (adjust if needed - use for reverse proxy)
WILLOW_HTTP_PORT=8080
MYSQL_PORT=3310
PMA_HTTP_PORT=8082
MAILPIT_HTTP_PORT=8025
MAILPIT_SMTP_PORT=1125
REDIS_COMMANDER_HTTP_PORT=8084

# Redis Commander
REDIS_COMMANDER_USERNAME=admin
REDIS_COMMANDER_PASSWORD=your-redis-commander-password

# Email Addresses
EMAIL_REPLY=hello@whatismyadapter.me
EMAIL_NOREPLY=noreply@whatismyadapter.me

# Optional
APP_DEFAULT_TIMEZONE=America/Chicago
EXPERIMENTAL_TESTS=Off
```

**Important Notes:**
- **Production:** Set `APP_FULL_BASE_URL=https://whatismyadapter.me`
- **Development/Testing:** Set `APP_FULL_BASE_URL=http://localhost:8080`
- **Behind Reverse Proxy:** Container listens on port 8080, proxy handles SSL

### **Step 5: Deploy**

1. Click **"Deploy the stack"**
2. Wait 5-10 minutes for build to complete
3. Monitor logs in Portainer

### **Step 6: Set Up Reverse Proxy (Production)**

#### **Nginx Reverse Proxy Example:**

```nginx
server {
    listen 80;
    server_name whatismyadapter.me;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name whatismyadapter.me;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/whatismyadapter.me.crt;
    ssl_certificate_key /etc/ssl/private/whatismyadapter.me.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

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

#### **Caddy Reverse Proxy Example:**

```caddy
whatismyadapter.me {
    reverse_proxy localhost:8080
}
```

### **Step 7: Verify Services**

**Production (via reverse proxy):**
- **Application:** `https://whatismyadapter.me`
- **Admin Panel:** `https://whatismyadapter.me/admin`

**Direct Access (internal/testing):**
- **Application:** `http://your-server:8080`
- **Admin Panel:** `http://your-server:8080/admin`
- **PHPMyAdmin:** `http://your-server:8082`
- **Mailpit:** `http://your-server:8025`
- **Redis Commander:** `http://your-server:8084`

---

## 📂 Directory Structure on Server

```
/volume1/docker/whatismyadapter/
├── app/              # Application code (from build) - Owner: 1034:100
├── logs/             # Application logs - Owner: 1034:100
├── nginx-logs/       # Nginx access/error logs - Owner: 1034:100
├── tmp/              # Cache and temp files - Owner: 1034:100
├── mysql/            # MySQL database files - Owner: 999:999 (MySQL default)
├── redis/            # Redis persistence - Owner: 1034:100
└── mailpit/          # Email storage - Owner: 1034:100
```

**User Details:**
- **Username:** `whatismyadapter`
- **UID:** `1034`
- **GID:** `100` (users group)

---

## 🌐 Domain & URL Configuration

### **Production Setup (with Reverse Proxy):**
```bash
APP_FULL_BASE_URL=https://whatismyadapter.me
WILLOW_HTTP_PORT=8080  # Container port (not exposed publicly)
```

**Setup:**
1. Container runs on `localhost:8080`
2. Reverse proxy (Nginx/Caddy) listens on ports 80/443
3. Proxy handles SSL/TLS termination
4. Proxy forwards to `localhost:8080`

### **Development/Testing Setup (Direct Access):**
```bash
APP_FULL_BASE_URL=http://localhost:8080
WILLOW_HTTP_PORT=8080  # Exposed publicly
```

**Setup:**
1. Container runs on `0.0.0.0:8080`
2. Direct access without reverse proxy
3. No SSL (use for testing only)

---

## 🔐 Generate Secure Passwords

```bash
# Generate SECURITY_SALT (32+ characters)
openssl rand -base64 32

# Generate strong passwords
openssl rand -base64 24
```

---

## 🛠️ Troubleshooting

### Build Fails
- **Check:** Repository access (make sure repo is public or credentials provided)
- **Check:** Branch name is correct (`main-clean` or `portainer-stack`)
- **Check:** Compose file path is correct: `portainer-stacks/docker-compose-cloud.yml`

### Permission Errors

```bash
# On your server, verify user exists
id whatismyadapter
# Should show: uid=1034(whatismyadapter) gid=100(users)

# Fix ownership if needed
sudo chown -R 1034:100 /volume1/docker/whatismyadapter

# Fix permissions
sudo chmod -R 755 /volume1/docker/whatismyadapter

# Check specific directories
ls -ln /volume1/docker/whatismyadapter/
```

### Container Won't Start
- **Check logs** in Portainer → Containers → [container name] → Logs
- **Verify** all required environment variables are set
- **Ensure** ports are not already in use
- **Verify** UID:1034 and GID:100 are set correctly in environment variables

### Database Connection Issues
- Wait 60-90 seconds for MySQL to fully initialize
- Check MySQL logs in Portainer
- Verify MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE are set correctly
- MySQL runs as its own user (999:999), not as whatismyadapter user

### Reverse Proxy Issues

**Application redirects to wrong URL:**
```bash
# Make sure APP_FULL_BASE_URL matches your public domain
APP_FULL_BASE_URL=https://whatismyadapter.me
```

**SSL errors:**
- Verify SSL certificates are valid
- Check reverse proxy configuration
- Ensure proxy sets X-Forwarded-Proto header

**Cannot access container:**
```bash
# Test direct access first
curl http://localhost:8080

# Check if port is accessible
netstat -tlnp | grep 8080
```

### Volume Permission Issues

If you see "permission denied" errors:

```bash
# Check current ownership
ls -ln /volume1/docker/whatismyadapter/

# Fix all directories
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/app
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/logs
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/nginx-logs
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/tmp
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/redis
sudo chown -R 1034:100 /volume1/docker/whatismyadapter/mailpit

# MySQL directory should be owned by mysql user (999:999)
sudo chown -R 999:999 /volume1/docker/whatismyadapter/mysql

# Verify
ls -ln /volume1/docker/whatismyadapter/
```

---

## 🔄 Update Stack

1. Push changes to GitHub repository
2. In Portainer: **Stacks** → **whatismyadapter** → **Pull and redeploy**
3. Portainer will rebuild from latest repository code

---

## 🧹 Clean Up / Remove

To completely remove the stack:

1. **In Portainer:** Stacks → whatismyadapter → **Delete**
2. **On Server (SSH):**
   ```bash
   # WARNING: This deletes ALL data
   sudo rm -rf /volume1/docker/whatismyadapter
   
   # Optional: Remove the user
   sudo userdel whatismyadapter
   ```

---

## 📝 Important Notes

1. **First deployment takes 5-10 minutes** (building from source)
2. **MySQL needs 60-90 seconds** to initialize on first run
3. **Volumes are host-mounted** at `/volume1/docker/whatismyadapter`
4. **Data persists** between stack updates/restarts
5. **User UID:1034 and GID:100** must be consistent across environment variables and host
6. **Use reverse proxy** for production SSL/TLS termination
7. **APP_FULL_BASE_URL** must match your public domain
8. **Backup** your MySQL data regularly

---

## 🔒 Security Best Practices

1. **Create the whatismyadapter user** before deploying
2. **Use strong passwords** for all services
3. **Never use default passwords** in production
4. **Set up firewall rules** to restrict access:
   ```bash
   # Only allow ports 80, 443 publicly
   # Keep 8080, 3310, 8082, etc. internal only
   ```
5. **Use SSL/TLS** via reverse proxy for production
6. **Regular backups** of `/volume1/docker/whatismyadapter`
7. **Monitor logs** for suspicious activity
8. **Keep containers updated** with latest security patches

---

## ✅ Post-Deployment Checklist

- [ ] User `whatismyadapter` (UID:1034, GID:100) created on server
- [ ] All directories owned by 1034:100
- [ ] All services showing as "running" in Portainer
- [ ] Reverse proxy configured (Nginx/Caddy)
- [ ] SSL certificate installed and valid
- [ ] DNS pointing to server: `whatismyadapter.me → your-server-ip`
- [ ] Application accessible at `https://whatismyadapter.me`
- [ ] Admin panel login works
- [ ] PHPMyAdmin database connection works (internal access)
- [ ] Check logs for any errors
- [ ] Configure firewall rules
- [ ] Set up automated backups
- [ ] Test email functionality (Mailpit)

---

## 📊 Service User Mapping

| Service | User Inside Container | Volume Ownership |
|---------|----------------------|------------------|
| **willowcms** | 1034:100 (whatismyadapter) | 1034:100 |
| **redis** | 1034:100 (whatismyadapter) | 1034:100 |
| **mysql** | 999:999 (mysql) | 999:999 |
| **mailpit** | 1034:100 (whatismyadapter) | 1034:100 |
| **phpmyadmin** | 33:33 (www-data) | N/A |
| **redis-commander** | Root (default) | N/A |

---

## 🌐 Network Architecture

```
Internet
    ↓
Port 80/443 (Reverse Proxy - Nginx/Caddy)
    ↓ SSL Termination
localhost:8080 (WhatIsMyAdapter Container)
    ↓
mysql:3306 (Internal network only)
redis:6379 (Internal network only)
```

**Public Access:**
- `https://whatismyadapter.me` → Application

**Internal Access Only:**
- `http://localhost:8080` → Application (direct)
- `http://localhost:8082` → PHPMyAdmin
- `http://localhost:8025` → Mailpit
- `http://localhost:8084` → Redis Commander
- `http://localhost:3310` → MySQL

---

**Ready to deploy? Follow the steps above! 🚀**

**Production URL:** https://whatismyadapter.me
