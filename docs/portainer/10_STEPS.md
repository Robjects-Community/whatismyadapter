# 🎯 Deploy WillowCMS in Portainer - 10 Simple Steps

## ✅ Prerequisites (Do This First!)

```bash
# Navigate to the portainer-stacks folder
cd /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks

# Build the WillowCMS image
./build-image.sh
```

---

## 📋 The 10 Steps

### 1️⃣ **Open Portainer**
```
http://localhost:49000
```
*Login with your credentials*

---

### 2️⃣ **Click "Stacks"**
*Look in the left sidebar*

---

### 3️⃣ **Click "+ Add stack"**
*Button is in the top right corner*

---

### 4️⃣ **Name it**
```
willowcms-test
```
*Type this in the "Name" field*

---

### 5️⃣ **Select "Web editor"**
*Under "Build method"*

---

### 6️⃣ **Upload Compose File**

**Option A:** Click "Upload" button
- Browse to: `/Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/`
- Select: `docker-compose-portainer.yml`

**Option B:** Copy & Paste
```bash
cat docker-compose-portainer.yml | pbcopy
```
*Then paste into the editor*

---

### 7️⃣ **Scroll to "Environment variables"**
*It's below the compose editor*

---

### 8️⃣ **Load Environment File**

Click **"Advanced mode"** → **"Load variables from .env file"**

**Option A:** Upload file
- Click "Upload .env file"
- Select: `stack-test.env`
- Click "Load"

**Option B:** Copy & Paste
```bash
cat stack-test.env | pbcopy
```
*Paste into text area → Click "Load"*

---

### 9️⃣ **Deploy**
*Scroll to bottom → Click "Deploy the stack"*

⏳ **Wait 30-60 seconds** for all containers to start

---

### 🔟 **Test It!**

Open these URLs:

| Service | URL |
|---------|-----|
| 🏠 WillowCMS | http://localhost:9080 |
| ⚙️ Admin | http://localhost:9080/admin |
| 🗄️ PHPMyAdmin | http://localhost:9082 |
| 📧 Mailpit | http://localhost:9025 |
| 🔴 Redis | http://localhost:9084 |

**Login credentials:** `admin` / `test_admin_123`

---

## ✅ Success Checklist

After step 10, you should see:

- ✅ WillowCMS homepage loads
- ✅ Can login to admin panel
- ✅ PHPMyAdmin shows database
- ✅ All containers "running" in Portainer

---

## 🆘 If Something Goes Wrong

### Issue: Can't upload files in Portainer
**Fix:** Copy & paste the content instead (see Option B in steps 6 & 8)

### Issue: Containers keep restarting
**Fix:** 
1. Click the container in Portainer
2. Click "Logs" tab
3. Look for errors
4. Wait 60 seconds - MySQL needs time to initialize

### Issue: Services not accessible
**Fix:** Check ports aren't in use:
```bash
lsof -i :9080
lsof -i :9082
lsof -i :9025
```

---

## 🎉 That's It!

You now have:
- ✅ WillowCMS running on port 9080
- ✅ Full database & cache system
- ✅ Admin tools accessible
- ✅ Everything managed in Portainer UI

**Monitor everything at:** http://localhost:49000

---

## 🧹 Cleanup (When Done Testing)

1. Go to **Stacks** in Portainer
2. Click **"willowcms-test"**
3. Click **"Delete this stack"**
4. Check **"Remove associated volumes"**
5. Confirm

**Or use CLI:**
```bash
docker compose -f docker-compose-portainer.yml down -v
```

---

**Questions? Check the full guide:** `PORTAINER_UI_GUIDE.md`

**Happy deploying! 🚀**
