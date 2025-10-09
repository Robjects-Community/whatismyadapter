# 🚀 WillowCMS Portainer Deployment

## 📚 Quick Navigation

| Guide | Description | Best For |
|-------|-------------|----------|
| **[10_STEPS.md](./10_STEPS.md)** | Ultra-simple 10-step guide | First-time users |
| **[PORTAINER_UI_GUIDE.md](./PORTAINER_UI_GUIDE.md)** | Comprehensive UI walkthrough | Detailed instructions |
| **[PORTAINER_LOCALHOST_GUIDE.md](./PORTAINER_LOCALHOST_GUIDE.md)** | CLI + Portainer monitoring | Local testing |
| **[QUICK_START.md](./QUICK_START.md)** | 5-minute quick reference | Experienced users |

---

## 🎯 Choose Your Path

### 🆕 **New to Portainer?**
Start here → **[10_STEPS.md](./10_STEPS.md)**

### 💻 **Prefer CLI?**
Start here → **[PORTAINER_LOCALHOST_GUIDE.md](./PORTAINER_LOCALHOST_GUIDE.md)**

### 🖱️ **Want to use Portainer UI?**
Start here → **[PORTAINER_UI_GUIDE.md](./PORTAINER_UI_GUIDE.md)**

### ⚡ **Already know what you're doing?**
Start here → **[QUICK_START.md](./QUICK_START.md)**

---

## 📦 What's Included

```
portainer-stacks/
├── docker-compose-portainer.yml   # Portainer-ready compose file
├── stack-test.env                 # Test environment variables
├── build-image.sh                 # Build WillowCMS image script
├── 10_STEPS.md                    # Simplest guide (recommended)
├── PORTAINER_UI_GUIDE.md          # Detailed UI instructions
├── PORTAINER_LOCALHOST_GUIDE.md   # CLI deployment guide
├── QUICK_START.md                 # Quick reference
└── README_NEW.md                  # This file
```

---

## ⚡ Super Quick Start

```bash
# 1. Build the image
./build-image.sh

# 2. Deploy
docker compose -f docker-compose-portainer.yml --env-file stack-test.env up -d

# 3. Access
open http://localhost:9080
```

**Monitor in Portainer:** http://localhost:49000

---

## 🎯 Service URLs (After Deployment)

| Service | URL | Credentials |
|---------|-----|-------------|
| 🏠 WillowCMS | http://localhost:9080 | admin / test_admin_123 |
| ⚙️ Admin Panel | http://localhost:9080/admin | admin / test_admin_123 |
| 🗄️ PHPMyAdmin | http://localhost:9082 | root / test_root_pass_123 |
| 📧 Mailpit | http://localhost:9025 | (no login) |
| 🔴 Redis Commander | http://localhost:9084 | admin / test_commander_123 |
| 📊 Portainer | http://localhost:49000 | (your credentials) |

---

## 🔧 Common Commands

```bash
# Build the image
./build-image.sh

# Start services
docker compose -f docker-compose-portainer.yml --env-file stack-test.env up -d

# Check status
docker compose -f docker-compose-portainer.yml ps

# View logs
docker compose -f docker-compose-portainer.yml logs -f willowcms

# Stop services
docker compose -f docker-compose-portainer.yml down

# Remove everything (including data)
docker compose -f docker-compose-portainer.yml down -v
```

---

## 🆘 Troubleshooting

### Can't access services?
- Wait 60 seconds for MySQL to initialize
- Check containers are running: `docker ps`
- Check logs: `docker compose logs willowcms`

### Port conflicts?
- Stop conflicting services: `lsof -i :9080`
- Or change ports in `stack-test.env`

### Image not found?
- Run `./build-image.sh` first
- Check image exists: `docker images | grep willowcms`

---

## 🔐 Security Notes

**⚠️ These are TEST credentials** - Change them for production!

Generate secure values:
```bash
# Generate a random string for SECURITY_SALT
openssl rand -base64 32

# Generate secure passwords
openssl rand -base64 16
```

---

## 📝 Key Features

✅ **Separate from dev environment** - Uses different ports (9xxx)  
✅ **Full stack included** - MySQL, Redis, PHPMyAdmin, Mailpit, Redis Commander  
✅ **Portainer monitoring** - View logs, manage containers via UI  
✅ **Test credentials** - Pre-configured for easy testing  
✅ **Volume persistence** - Data survives container restarts  

---

## 🎓 Learning Path

1. **Start with 10_STEPS.md** - Get it running first
2. **Explore in Portainer** - http://localhost:49000
3. **Read PORTAINER_UI_GUIDE.md** - Learn UI features
4. **Test the application** - http://localhost:9080
5. **Check other guides** - For advanced usage

---

## 🚀 Ready to Deploy?

Pick a guide from the top and follow along!

**Recommended:** Start with **[10_STEPS.md](./10_STEPS.md)**

---

**Questions?** Check the comprehensive guides above or the main WillowCMS documentation.

**Happy deploying! 🎉**
