# WillowCMS Portainer Deployment - Quick Start Guide

## 🚀 Choose Your Deployment Path

This project supports **four deployment paths** for different use cases:

### 📋 Path Selection Guide

| Path | Use Case | When to Use |
|------|----------|-------------|
| **Path 1** | SSH Access | Initial server setup, emergencies only |
| **Path 2** | Local Testing | Pre-production staging and testing |
| **Path 3** ⭐ | Production | Main production deployment |
| **Path 4** | VSCode Dev | Daily development and feature creation |

---

## Path 1: SSH Access (Emergency/Setup)

**⚠️ Use sparingly - disable SSH when not needed!**

```bash
# From your local machine
./deploy/portainer/ssh-access/ssh-deploy.sh

# Configure first:
export SSH_HOST="your-server-ip"
export REMOTE_PROJECT_PATH="/path/to/willow"
```

**User**: whatismyadapter (UID: 1034, GID: 100)

[📖 Full Path 1 Documentation](deploy/portainer/ssh-access/README.md)

---

## Path 2: Local Development Testing (Staging)

**Best for**: Testing before production deployment

### Quick Setup:
```bash
cd deploy/portainer/local-dev
cp stack-local.env.template stack-local.env
# Edit stack-local.env with your credentials
```

### Deploy via Portainer UI:
1. Navigate to **Stacks** → **Add stack** → **Web editor**
2. Paste contents of `docker-compose-port-local-dev.yml`
3. Add environment variables from `stack-local.env`
4. Click **Deploy the stack**

**User**: Server user (UID: 1034, GID: 100)

[📖 Full Path 2 Documentation](deploy/portainer/local-dev/README.md)

---

## Path 3: Cloud Production ⭐ (Main Deployment)

**Best for**: Production deployment with automatic updates from GitHub

### Quick Setup via Portainer:
1. Open **Stacks** → **Add stack**
2. Select **Repository** method
3. Configure:
   - **URL**: `https://github.com/garzarobm/willow.git`
   - **Reference**: `main-clean`
   - **Path**: `deploy/portainer/cloud-production/docker-compose-port-cloud.yml`
4. Set environment variables (see template)
5. Deploy!

**Features**:
- ✅ Auto-deploys from GitHub
- ✅ Production security
- ✅ Resource limits
- ✅ SSL/TLS ready

**User**: Server user (UID: 1034, GID: 100)

[📖 Full Path 3 Documentation](deploy/portainer/cloud-production/README.md)

---

## Path 4: VSCode Development

**Best for**: Daily development with live code editing

### Quick Setup:
```bash
cd /Volumes/1TB_DAVINCI/docker/willow

# Copy and configure environment
cp deploy/portainer/vscode-dev/stack-vscode.env.template \
   deploy/portainer/vscode-dev/stack-vscode.env

# Start development environment
docker compose \
  --env-file deploy/portainer/vscode-dev/stack-vscode.env \
  -f deploy/portainer/vscode-dev/docker-compose-vscode-dev.yml \
  up -d
```

**Features**:
- ✅ Live code editing
- ✅ Hot-reload
- ✅ PHPUnit testing
- ✅ Full debugging

**User**: Local MacOS user (UID: 1000, GID: 1000)

[📖 Full Path 4 Documentation](deploy/portainer/vscode-dev/README.md)

---

## 📊 Development Workflow

```
Development (Path 4) → Staging (Path 2) → Production (Path 3)
         ↓                  ↓                    ↓
    Local Test         Server Test         Auto-Deploy
     VSCode            Portainer           from GitHub
```

### Recommended Flow:
1. **Develop** in Path 4 (VSCode) with live editing
2. **Test** locally with PHPUnit
3. **Deploy to staging** in Path 2 for final testing
4. **Merge** to `main-clean` branch
5. **Auto-deploys** to production via Path 3

---

## 🔒 Security Checklist

Before deploying to production:

- [ ] Change all passwords from defaults
- [ ] Generate unique 64-character security salt
- [ ] Set `DEBUG=false` for production
- [ ] Configure HTTPS with SSL/TLS
- [ ] Set up firewall rules
- [ ] Disable or restrict SSH access
- [ ] Configure backups
- [ ] Never commit `.env` files with secrets
- [ ] Use Portainer UI for production secrets

---

## 📁 Where to Find Everything

```
deploy/portainer/
├── README.md                    # Complete documentation
├── DEPLOYMENT_SUMMARY.md        # Implementation summary
├── ssh-access/                  # Path 1 files
├── local-dev/                   # Path 2 files  
├── cloud-production/            # Path 3 files ⭐
└── vscode-dev/                  # Path 4 files
```

---

## 🆘 Common Tasks

### View Service Status
```bash
# Path 4 (Local)
docker compose -f deploy/portainer/vscode-dev/docker-compose-vscode-dev.yml ps

# Path 1 (SSH) or Path 2/3 (Server via Portainer)
# Check in Portainer UI: Stacks → Your Stack → Containers
```

### View Logs
```bash
# Path 4 (Local)
docker compose -f deploy/portainer/vscode-dev/docker-compose-vscode-dev.yml logs -f

# Paths 1-3: Check Portainer UI → Containers → Logs
```

### Access Services
After deployment, access:
- **WillowCMS**: `http://your-server:8080`
- **phpMyAdmin**: `http://your-server:8082`
- **Mailpit**: `http://your-server:8025`
- **Redis Commander**: `http://your-server:8084`

---

## 📧 Get Help

- **Full Documentation**: [deploy/portainer/README.md](deploy/portainer/README.md)
- **GitHub Issues**: [Report Issues](https://github.com/garzarobm/willow/issues)
- **CakePHP Docs**: https://book.cakephp.org/
- **Docker Docs**: https://docs.docker.com/
- **Portainer Docs**: https://docs.portainer.io/

---

## ⚡ TL;DR - Choose Your Path

- **🚨 Emergency?** → Use Path 1 (SSH)
- **🧪 Testing?** → Use Path 2 (Staging)
- **🚀 Production?** → Use Path 3 (Cloud) ⭐
- **💻 Developing?** → Use Path 4 (VSCode)

**Start here**: [deploy/portainer/README.md](deploy/portainer/README.md)

---

**Last Updated**: 2025-01-04
