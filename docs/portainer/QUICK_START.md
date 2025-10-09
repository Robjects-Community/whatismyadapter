# 🚀 Portainer Quick Start - WillowCMS

## TL;DR - Get Running in 5 Minutes

### 1️⃣ Access Portainer
Open: `http://localhost:49000` or `https://localhost:49443`

### 2️⃣ Create Stack
- Navigate to **Stacks** → **Add Stack**
- Name: `willowcms-test`

### 3️⃣ Upload Files
1. **Compose File:** Upload `docker-compose-portainer.yml`
2. **Environment:** Upload `stack-test.env` via "Load variables from .env file"

### 4️⃣ Deploy
Click **"Deploy the stack"** (takes 5-10 minutes first time)

### 5️⃣ Access Services

| Service | URL | Credentials |
|---------|-----|-------------|
| **WillowCMS** | http://localhost:9080 | admin / test_admin_123 |
| **Admin** | http://localhost:9080/admin | admin / test_admin_123 |
| **PHPMyAdmin** | http://localhost:9082 | root / test_root_pass_123 |
| **Mailpit** | http://localhost:9025 | (no login) |
| **Redis Commander** | http://localhost:9084 | admin / test_commander_123 |

---

## 📁 File Locations

```
/Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/
├── docker-compose-portainer.yml   ← Upload to Portainer
├── stack-test.env                 ← Load as environment variables
├── PORTAINER_TEST_GUIDE.md        ← Full detailed guide
└── QUICK_START.md                 ← This file
```

---

## 🔧 Key Differences from Dev Environment

| Item | Dev Environment | Portainer Test |
|------|----------------|----------------|
| **Ports** | 8080, 8082, 8025, 8084 | **9080, 9082, 9025, 9084** |
| **Containers** | willow-* | willow-portainer-* |
| **Volumes** | willow_* | willow_portainer_* |
| **Management** | CLI | Portainer UI |

**You can run BOTH simultaneously!**

---

## ✅ Testing Checklist

After deployment, verify:
- [ ] WillowCMS loads at http://localhost:9080
- [ ] Can login to admin area
- [ ] PHPMyAdmin shows database
- [ ] Mailpit is accessible
- [ ] Redis Commander connects

---

## 🧹 Cleanup

**Stop (keep data):**
Portainer → Stacks → willowcms-test → **Stop**

**Delete (remove everything):**
Portainer → Stacks → willowcms-test → **Delete** (check "Remove volumes")

**CLI:**
```bash
docker stop $(docker ps -aq --filter "name=willow-portainer")
docker rm $(docker ps -aq --filter "name=willow-portainer")
docker volume rm $(docker volume ls -q --filter "name=willow_portainer")
```

---

## 🆘 Troubleshooting

**Container keeps restarting?**
→ Check logs in Portainer (click container → Logs)

**Can't access services?**
→ Wait 60 seconds for MySQL to fully start

**Port conflicts?**
→ Change ports in environment variables before deploying

**Build fails?**
→ Check GitHub repo is accessible and branch exists

---

## 📖 Need More Details?

See the full guide: `PORTAINER_TEST_GUIDE.md`

---

**Ready to deploy? Let's go! 🚀**
