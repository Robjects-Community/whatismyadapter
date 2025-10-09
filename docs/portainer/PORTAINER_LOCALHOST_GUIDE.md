# 🚀 Portainer Local Testing Guide - Updated

## ⚠️ Important: Portainer Deployment Modes

Portainer has **two different deployment modes** that behave differently:

1. **Swarm Mode** (what you encountered) - Requires overlay networks, no build support
2. **Standalone Docker** - Better for local testing but less integrated

For **local testing on Docker Desktop**, the **easiest approach is to use docker compose CLI** instead of Portainer's UI.

---

## 🎯 **Recommended Approach: CLI with Portainer Monitoring**

### **Step 1: Build the Image**

```bash
# Run the build script
./portainer-stacks/build-image.sh
```

✅ **Already done!** Your image `willowcms:portainer-test` is ready.

### **Step 2: Deploy Using Docker Compose CLI**

```bash
# Navigate to portainer-stacks directory
cd /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks

# Deploy the stack
docker compose -f docker-compose-portainer.yml --env-file stack-test.env up -d
```

### **Step 3: Monitor in Portainer**

1. Open Portainer: http://localhost:49000
2. Go to **Containers**
3. You'll see all your containers there!
4. You can:
   - View logs
   - Stop/start containers
   - Access container terminals
   - Monitor resource usage

### **Step 4: Access Your Services**

| Service | URL | Credentials |
|---------|-----|-------------|
| WillowCMS | http://localhost:9080 | admin / test_admin_123 |
| Admin | http://localhost:9080/admin | admin / test_admin_123 |
| PHPMyAdmin | http://localhost:9082 | root / test_root_pass_123 |
| Mailpit | http://localhost:9025 | (no login) |
| Redis Commander | http://localhost:9084 | admin / test_commander_123 |

---

## 🧹 **Cleanup**

```bash
# Stop and remove everything
cd /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks
docker compose -f docker-compose-portainer.yml down

# Remove volumes too (deletes all data)
docker compose -f docker-compose-portainer.yml down -v
```

---

## 🔧 **Alternative: Using Portainer Stacks (Advanced)**

If you really want to use Portainer's Stack UI, you need to:

1. **Enable Docker Swarm** (makes things more complex):
   ```bash
   docker swarm init
   ```

2. **Deploy via Portainer UI**:
   - Upload `docker-compose-portainer.yml`
   - Load `stack-test.env`
   - Deploy as Stack

3. **Disable Swarm when done**:
   ```bash
   docker swarm leave --force
   ```

**Note:** Swarm mode adds complexity and isn't necessary for local testing.

---

## ✅ **Quick Commands Reference**

```bash
# Deploy
cd /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks
docker compose -f docker-compose-portainer.yml --env-file stack-test.env up -d

# Check status
docker compose -f docker-compose-portainer.yml ps

# View logs
docker compose -f docker-compose-portainer.yml logs -f willowcms

# Stop
docker compose -f docker-compose-portainer.yml down

# Rebuild and restart
docker compose -f docker-compose-portainer.yml up -d --build
```

---

## 📊 **Why This Approach is Better**

✅ **Simple** - No Swarm complexity  
✅ **Fast** - Direct Docker Compose deployment  
✅ **Monitored** - Still visible in Portainer  
✅ **Flexible** - Easy to modify and redeploy  
✅ **Compatible** - Works on Docker Desktop  

---

## 🎯 **Next Steps**

Your environment is ready! Just run:

```bash
cd /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks
docker compose -f docker-compose-portainer.yml --env-file stack-test.env up -d
```

Then access WillowCMS at: **http://localhost:9080**

Monitor it in Portainer at: **http://localhost:49000**

---

**Happy Testing! 🚀**
