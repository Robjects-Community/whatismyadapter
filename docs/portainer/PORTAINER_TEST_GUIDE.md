# 🚀 WillowCMS Portainer Testing Guide

## Overview
This guide will walk you through deploying and testing WillowCMS using Portainer running on your local Docker Desktop.

## Prerequisites
✅ Docker Desktop installed and running  
✅ Portainer already running (accessible at http://localhost:49000 or https://localhost:49443)  
✅ WillowCMS repository cloned locally

---

## 📋 Step-by-Step Deployment Guide

### **Step 1: Access Portainer**

1. Open your browser and navigate to:
   - **HTTP:** `http://localhost:49000`
   - **HTTPS:** `https://localhost:49443`

2. Log in with your Portainer credentials

3. Select your **local Docker environment**

---

### **Step 2: Create a New Stack**

1. In the left sidebar, click **"Stacks"**

2. Click the **"+ Add stack"** button at the top

3. Name your stack: `willowcms-test`

---

### **Step 3: Upload the Docker Compose File**

You have **two options**:

#### **Option A: Upload File (Recommended)**

1. In the "Web editor" section, click **"Upload"**

2. Navigate to: `/Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/`

3. Select the file: `docker-compose-portainer.yml`

4. The contents will appear in the editor

#### **Option B: Copy & Paste**

1. Open the file in a text editor:
   ```bash
   cat /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/docker-compose-portainer.yml
   ```

2. Copy the entire contents

3. Paste into Portainer's Web editor

---

### **Step 4: Load Environment Variables**

This is **critical** for the stack to work properly!

1. Scroll down to the **"Environment variables"** section

2. Click **"Load variables from .env file"**

3. Click **"Upload .env file"**

4. Navigate to: `/Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/`

5. Select the file: `stack-test.env`

6. Click **"Load"**

You should now see all environment variables populated in the list below.

**Alternatively**, you can manually copy and paste from `stack-test.env`:
```bash
# Copy the contents to clipboard
cat /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/stack-test.env | pbcopy
```

Then paste into the "Add an environment variable" text area and click "Load".

---

### **Step 5: Review Key Configuration**

Before deploying, verify these important settings:

#### **Ports (Different from Dev to Avoid Conflicts)**
- **WillowCMS:** `9080` → `http://localhost:9080`
- **PHPMyAdmin:** `9082` → `http://localhost:9082`
- **Mailpit:** `9025` → `http://localhost:9025`
- **Redis Commander:** `9084` → `http://localhost:9084`
- **MySQL:** `9310` (external access)

#### **Credentials (Test Environment)**
| Service | Username | Password |
|---------|----------|----------|
| MySQL Root | `root` | `test_root_pass_123` |
| MySQL User | `willow_test` | `test_user_pass_123` |
| Redis | `default` | `test_redis_pass_123` |
| WillowCMS Admin | `admin` | `test_admin_123` |
| Redis Commander | `admin` | `test_commander_123` |

---

### **Step 6: Deploy the Stack**

1. Scroll to the bottom of the page

2. **Optional:** Enable "Pull latest image versions" (recommended for testing)

3. Click **"Deploy the stack"**

4. Portainer will:
   - Pull required Docker images
   - Build the WillowCMS image from GitHub
   - Create volumes
   - Create the network
   - Start all containers

⏱️ **Initial deployment may take 5-10 minutes** (building the image from source)

---

### **Step 7: Monitor Deployment**

1. You'll see a progress indicator at the top

2. Once deployed, you'll be redirected to the stack details page

3. Check the **container status**:
   - All containers should show "running" (green)
   - MySQL might take a moment to initialize
   - Redis health check needs to pass before dependent services start

---

### **Step 8: View Container Logs**

To troubleshoot or monitor:

1. In the stack view, click on any container name

2. Click the **"Logs"** tab

3. Common things to check:
   - **mysql:** Look for "ready for connections"
   - **redis:** Look for "Ready to accept connections"
   - **willowcms:** Look for successful startup messages

---

### **Step 9: Access the Applications**

Once all containers are running:

#### **WillowCMS Application**
- **URL:** `http://localhost:9080`
- **Admin Area:** `http://localhost:9080/admin`
- **Login:** 
  - Username: `admin`
  - Password: `test_admin_123`

#### **PHPMyAdmin (Database Management)**
- **URL:** `http://localhost:9082`
- **Login:** 
  - Server: `mysql`
  - Username: `root`
  - Password: `test_root_pass_123`

#### **Mailpit (Email Testing)**
- **URL:** `http://localhost:9025`
- View all emails sent by WillowCMS

#### **Redis Commander (Cache Management)**
- **URL:** `http://localhost:9084`
- **Login:**
  - Username: `admin`
  - Password: `test_commander_123`

---

## 🧪 Testing Checklist

After deployment, verify these key functions:

### ✅ Basic Functionality
- [ ] WillowCMS homepage loads
- [ ] Admin area accessible
- [ ] Can log in with admin credentials
- [ ] PHPMyAdmin shows `willow_cms_test` database
- [ ] Redis Commander shows connected to Redis

### ✅ Database Testing
- [ ] Check tables exist in PHPMyAdmin
- [ ] Browse data in various tables
- [ ] Verify test database (`willow_cms_test_test`) exists

### ✅ Email Testing
- [ ] Trigger an email from WillowCMS
- [ ] Check Mailpit for received emails
- [ ] Verify email content

### ✅ Cache Testing
- [ ] View cache keys in Redis Commander
- [ ] Verify Redis is storing session data
- [ ] Check cache hit/miss metrics

---

## 🔧 Common Issues & Solutions

### **Issue: Stack fails to deploy**
**Solution:** Check the container logs for specific errors

### **Issue: Port already in use**
**Solution:** Either:
1. Stop the conflicting service
2. Change the port in Portainer's environment variables before deploying

### **Issue: WillowCMS container keeps restarting**
**Solutions:**
- Check `willowcms` container logs
- Verify MySQL is fully started (check mysql logs)
- Ensure Redis health check is passing
- Check environment variables are properly loaded

### **Issue: Can't access applications**
**Solutions:**
- Verify containers are running: `docker ps | grep willow-portainer`
- Check port mappings match what you're trying to access
- Try accessing via container IP directly

### **Issue: Database connection errors**
**Solutions:**
- Wait 30-60 seconds after deployment (MySQL initialization)
- Check MySQL logs for "ready for connections"
- Verify `DB_HOST` variable is set to `mysql` (service name)

---

## 🧹 Cleanup / Removal

### **Option 1: Stop Stack (Keep Data)**
1. Go to **Stacks** → **willowcms-test**
2. Click **"Stop"**
3. Volumes are preserved

### **Option 2: Remove Stack (Delete Everything)**
1. Go to **Stacks** → **willowcms-test**
2. Click **"Delete this stack"**
3. Check **"Remove associated volumes"** if you want to delete data
4. Click **"Delete"**

### **Option 3: CLI Cleanup**
```bash
# Stop and remove containers
docker stop $(docker ps -aq --filter "name=willow-portainer")
docker rm $(docker ps -aq --filter "name=willow-portainer")

# Remove volumes (⚠️ This deletes all data)
docker volume rm $(docker volume ls -q --filter "name=willow_portainer")

# Remove network
docker network rm willow_portainer_network
```

---

## 📊 Comparing Portainer vs Dev Environment

| Aspect | Dev Environment | Portainer Test |
|--------|----------------|----------------|
| **Ports** | 8080, 8082, 8025, 8084 | 9080, 9082, 9025, 9084 |
| **Volumes** | `willow_*` | `willow_portainer_*` |
| **Network** | `willow_default` | `willow_portainer_network` |
| **Containers** | `willow-*` | `willow-portainer-*` |
| **Management** | CLI (`docker compose`) | Portainer UI |

This separation allows you to run **both environments simultaneously** for comparison!

---

## 🚀 Next Steps

### **For Development**
Continue using your regular dev environment (`./run_dev_env.sh`)

### **For Production Deployment**
1. Update `stack.env` with production values:
   - Strong passwords
   - Production domain in `APP_FULL_BASE_URL`
   - Real SECURITY_SALT (32+ random characters)
   - API keys for external services
   
2. Change ports if needed (default 8080 is fine for production)

3. Consider using host-mounted volumes for easier backup:
   ```bash
   WILLOWCMS_CODE_PATH=/path/to/production/code
   WILLOWCMS_LOGS_PATH=/path/to/production/logs
   ```

4. Deploy to remote Portainer instance (same process)

---

## 📚 Additional Resources

- **Portainer Documentation:** https://docs.portainer.io/user/docker/stacks
- **WillowCMS Documentation:** (Add your docs link)
- **Docker Compose Reference:** https://docs.docker.com/compose/compose-file/

---

## ❓ Need Help?

If you encounter issues:

1. Check container logs in Portainer
2. Verify environment variables are loaded
3. Ensure ports aren't conflicting with dev environment
4. Check Docker Desktop resource limits (CPU, Memory)

**Pro Tip:** Use Portainer's built-in terminal feature to exec into containers and debug from inside!

---

**Happy Testing! 🎉**
