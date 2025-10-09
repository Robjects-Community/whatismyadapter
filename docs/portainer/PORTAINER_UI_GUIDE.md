# 🎨 Deploy WillowCMS via Portainer UI

## 📌 Overview
This guide shows you how to deploy WillowCMS using Portainer's web interface.

---

## ⚠️ Important: Check Your Portainer Mode

Portainer can run in two modes:
- **Standalone Docker** (what we need) ✅
- **Swarm Mode** (requires different setup) ⚠️

### How to Check:
1. Open Portainer: http://localhost:49000
2. Go to **"Endpoints"** in the left sidebar
3. Look at your endpoint - it should say **"Docker"** not "Docker Swarm"

---

## 🚀 Method 1: Using Portainer Stacks (Recommended)

### **Prerequisites**
✅ WillowCMS image built (run: `./portainer-stacks/build-image.sh`)  
✅ Portainer accessible at http://localhost:49000  
✅ Not in Swarm mode (or willing to enable it temporarily)

### **Step-by-Step Instructions**

#### **Step 1: Access Portainer**
1. Open browser: http://localhost:49000
2. Log in with your Portainer credentials
3. Click on your **local environment** (usually "primary" or "local")

#### **Step 2: Navigate to Stacks**
1. In the left sidebar, click **"Stacks"**
2. Click the **"+ Add stack"** button (top right)

#### **Step 3: Configure Stack**

##### **3.1 Name Your Stack**
- **Name:** `willowcms-test` (or any name you prefer)
- **Build method:** Select **"Web editor"**

##### **3.2 Add Docker Compose Content**

Click the **"Upload"** button or copy/paste the content:

**Option A: Upload File**
- Click **"Upload"**
- Select: `/Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/docker-compose-portainer.yml`

**Option B: Copy & Paste**
```bash
# Copy the file content to clipboard
cat /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/docker-compose-portainer.yml | pbcopy
```
Then paste into the Web editor

##### **3.3 Load Environment Variables**

Scroll down to **"Environment variables"** section:

1. Click **"Advanced mode"**
2. Click **"Load variables from .env file"**
3. Click **"Upload .env file"**
4. Select: `/Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/stack-test.env`
5. Click **"Load"**

You should now see all variables loaded!

**Alternative:** Manually copy and paste:
```bash
# Copy env file to clipboard
cat /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/stack-test.env | pbcopy
```
Then paste into the text area and click "Load"

##### **3.4 Optional Settings**

- ☑️ **Enable access control** (if you want to restrict who can manage this stack)
- ☐ **Pull latest image versions** (leave unchecked since we built locally)
- ☐ **Do not auto update the stack from its git repository** (not using git)

#### **Step 4: Deploy**

1. Scroll to the bottom
2. Click **"Deploy the stack"**
3. Wait for deployment (this can take 30-60 seconds)

#### **Step 5: Verify Deployment**

You'll be redirected to the stack details page. Check:

- ✅ **Status:** All containers show "running" (green)
- ✅ **No errors** in the logs
- ✅ **MySQL** might take 30 seconds to initialize

---

## 🖥️ Method 2: Using Portainer Containers (Alternative)

If Stacks don't work, you can deploy each container individually:

### **Step 1: Create Network**
1. Go to **"Networks"** in sidebar
2. Click **"+ Add network"**
3. Name: `willow_portainer_network`
4. Driver: **bridge**
5. Click **"Create the network"**

### **Step 2: Create Volumes**
Go to **"Volumes"** → **"Add volume"** for each:
- `willow_portainer_mysql_data`
- `willow_portainer_redis_data`
- `willow_portainer_mailpit_data`
- `willow_portainer_mailpit_logs`
- `willow_portainer_app_data`
- `willow_portainer_logs`
- `willow_portainer_nginx_logs`
- `willow_portainer_storage`

### **Step 3: Create Containers**

#### **3.1 MySQL Container**
1. Go to **"Containers"** → **"Add container"**
2. Name: `willow-portainer-mysql`
3. Image: `mysql:8.0`
4. Network: `willow_portainer_network`
5. Port mapping: `9310:3306`
6. Volume mapping: `willow_portainer_mysql_data:/var/lib/mysql`
7. Environment variables:
   ```
   MYSQL_ROOT_PASSWORD=test_root_pass_123
   MYSQL_DATABASE=willow_cms_test
   MYSQL_USER=willow_test
   MYSQL_PASSWORD=test_user_pass_123
   ```
8. Click **"Deploy the container"**

#### **3.2 Redis Container**
1. Name: `willow-portainer-redis`
2. Image: `redis:7.2-alpine`
3. Network: `willow_portainer_network`
4. Volume: `willow_portainer_redis_data:/data`
5. Command: `redis-server --requirepass test_redis_pass_123 --appendonly yes`
6. Deploy

#### **3.3 WillowCMS Container**
1. Name: `willow-portainer-cms`
2. Image: `willowcms:portainer-test`
3. Network: `willow_portainer_network`
4. Port mapping: `9080:80`
5. Volumes:
   - `willow_portainer_app_data:/var/www/html/`
   - `willow_portainer_logs:/var/www/html/logs/`
6. Environment variables: (copy from `stack-test.env`)
7. Deploy

(Repeat for PHPMyAdmin, Mailpit, and Redis Commander...)

---

## 🎯 Access Your Services

Once deployed via UI, access:

| Service | URL | Credentials |
|---------|-----|-------------|
| **WillowCMS** | http://localhost:9080 | admin / test_admin_123 |
| **Admin** | http://localhost:9080/admin | admin / test_admin_123 |
| **PHPMyAdmin** | http://localhost:9082 | root / test_root_pass_123 |
| **Mailpit** | http://localhost:9025 | (no login) |
| **Redis Commander** | http://localhost:9084 | admin / test_commander_123 |

---

## 📊 Monitor Your Deployment

### **In Portainer UI:**

1. **View Stack/Containers:**
   - Click **"Stacks"** → **"willowcms-test"** (if using Stack method)
   - OR click **"Containers"** (if using individual containers)

2. **View Logs:**
   - Click on any container name
   - Click the **"Logs"** tab
   - Toggle **"Auto-refresh logs"** for live monitoring

3. **Access Container Shell:**
   - Click on a container
   - Click **"Console"**
   - Select **"/bin/bash"** or **"/bin/sh"**
   - Click **"Connect"**

4. **Monitor Resources:**
   - Click **"Containers"**
   - View CPU, Memory, Network usage in real-time

5. **Inspect Container:**
   - Click container → **"Inspect"** tab
   - View full container configuration

---

## 🔧 Management via UI

### **Update Stack:**
1. Go to **"Stacks"** → **"willowcms-test"**
2. Click **"Editor"** tab
3. Make your changes
4. Click **"Update the stack"**
5. Choose: **"Re-pull image and redeploy"** or **"Prune services"**

### **Restart Containers:**
1. Go to **"Containers"**
2. Select checkboxes for containers to restart
3. Click **"Restart"** button at top

### **Stop Stack:**
1. Go to **"Stacks"** → **"willowcms-test"**
2. Click **"Stop this stack"**

### **Remove Stack:**
1. Go to **"Stacks"** → **"willowcms-test"**
2. Click **"Delete this stack"**
3. ☑️ Check **"Remove associated volumes"** (if you want to delete data)
4. Confirm deletion

---

## 🐛 Troubleshooting UI Deployment

### **Issue: "Network not found" Error**
**Solution:**
1. Manually create the network first (see Method 2, Step 1)
2. Or change network name in compose file to match existing network

### **Issue: "Image not found"**
**Solution:**
1. Make sure you built the image: `./portainer-stacks/build-image.sh`
2. Or change image name to use a pre-built one from Docker Hub

### **Issue: Containers Keep Restarting**
**Solution:**
1. Click container → **"Logs"** tab
2. Look for error messages
3. Common fixes:
   - Wait 60 seconds for MySQL to initialize
   - Check environment variables are set correctly
   - Verify volumes have proper permissions

### **Issue: Can't Access Services**
**Solution:**
1. Check container is "running" (green status)
2. Verify port mappings: Container details → **"Network"** tab
3. Check firewall isn't blocking ports 9080, 9082, 9025, 9084

### **Issue: Stack Deploy Button Grayed Out**
**Solution:**
1. Make sure compose file has no syntax errors
2. Verify all required environment variables are set
3. Check you have permission to deploy stacks

---

## 📝 Quick Checklist

Before deploying via UI, ensure:

- [ ] Image `willowcms:portainer-test` is built
- [ ] Portainer is accessible at http://localhost:49000
- [ ] You have the `docker-compose-portainer.yml` file ready
- [ ] You have the `stack-test.env` file ready
- [ ] Ports 9080, 9082, 9025, 9084, 9310 are not in use
- [ ] You're logged into Portainer with admin access

---

## 🎬 Video-Style Guide (Step Numbers)

If you prefer following numbered steps:

1. **Open** http://localhost:49000
2. **Login** to Portainer
3. **Click** "Stacks" in sidebar
4. **Click** "+ Add stack" button
5. **Type** name: `willowcms-test`
6. **Click** "Upload" and select `docker-compose-portainer.yml`
7. **Scroll** to "Environment variables"
8. **Click** "Load variables from .env file"
9. **Upload** `stack-test.env`
10. **Click** "Deploy the stack"
11. **Wait** 60 seconds
12. **Open** http://localhost:9080

Done! 🎉

---

## 🔗 Useful Portainer UI Links

Once logged in:
- **Dashboard:** http://localhost:49000/#!/home
- **Containers:** http://localhost:49000/#!/containers
- **Stacks:** http://localhost:49000/#!/stacks
- **Volumes:** http://localhost:49000/#!/volumes
- **Networks:** http://localhost:49000/#!/networks
- **Images:** http://localhost:49000/#!/images

---

## 💡 Pro Tips

1. **Use Stack method** - It's easier to manage all services together
2. **Save your compose file** in Portainer for easy updates
3. **Enable auto-refresh logs** when debugging
4. **Use Portainer's web console** instead of terminal for quick access
5. **Create templates** for frequently deployed stacks
6. **Use webhooks** for automated redeployments
7. **Set up user access controls** for team collaboration

---

**Ready to deploy via UI? Follow the steps above and you'll have WillowCMS running in Portainer! 🚀**
