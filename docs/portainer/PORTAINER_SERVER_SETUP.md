# 🖥️ Portainer Server Setup for Edge Agents

## Overview

This guide walks you through setting up a Portainer Server instance that can manage remote Docker environments via Edge Agents. You'll learn how to install Portainer, configure it securely, and generate Edge Agent credentials.

## Prerequisites

Before starting, ensure you have:

- ✅ A server/machine to run Portainer Server (can be your MacOS, cloud VM, or Synology NAS)
- ✅ Docker and Docker Compose installed
- ✅ Ports 9443 and 8000 available (or customizable)
- ✅ Domain name (optional, but recommended for HTTPS)
- ✅ Basic understanding of Docker networking

## 📦 Portainer Editions

### Community Edition (CE) - FREE
- ✅ Full Edge Agent support (Polling mode)
- ✅ Unlimited environments
- ✅ Container management
- ✅ Stack deployment
- ✅ Docker Compose support
- ✅ Basic RBAC
- ❌ No async Edge Agent mode
- ❌ Limited enterprise features

### Business Edition (BE) - PAID
- ✅ Everything in Community Edition
- ✅ Async Edge Agent mode (real-time)
- ✅ Advanced RBAC
- ✅ GitOps deployment
- ✅ Registry management
- ✅ Commercial support

**Recommendation**: Start with Community Edition for WillowCMS deployment.

## 🚀 Installation Methods

### Method 1: Docker Compose (Recommended)

Create a dedicated directory for Portainer Server:

```bash
# On your Portainer Server host
mkdir -p ~/portainer-server
cd ~/portainer-server
```

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  portainer:
    image: portainer/portainer-ce:latest
    container_name: portainer-server
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    ports:
      - "${PORTAINER_HTTP_PORT:-9000}:9000"
      - "${PORTAINER_HTTPS_PORT:-9443}:9443"
      - "${PORTAINER_EDGE_PORT:-8000}:8000"
    volumes:
      - portainer_data:/data
      - /var/run/docker.sock:/var/run/docker.sock:ro
    environment:
      - TZ=${TZ:-America/Chicago}
    networks:
      - portainer_network

volumes:
  portainer_data:
    driver: local

networks:
  portainer_network:
    driver: bridge
```

Create `.env` file:

```bash
cat > .env << 'EOF'
# Portainer Server Configuration
PORTAINER_HTTP_PORT=9000
PORTAINER_HTTPS_PORT=9443
PORTAINER_EDGE_PORT=8000

# Timezone
TZ=America/Chicago

# Security (Optional - for reverse proxy)
# PORTAINER_SSL_CERT=/path/to/cert.pem
# PORTAINER_SSL_KEY=/path/to/key.pem
EOF

chmod 600 .env
```

Deploy Portainer Server:

```bash
# Start Portainer
docker compose up -d

# Check status
docker compose ps

# View logs
docker compose logs -f portainer
```

### Method 2: Docker Run Command

For quick deployment:

```bash
docker volume create portainer_data

docker run -d \
  --name portainer-server \
  --restart unless-stopped \
  -p 9000:9000 \
  -p 9443:9443 \
  -p 8000:8000 \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v portainer_data:/data \
  portainer/portainer-ce:latest
```

### Method 3: On Synology NAS (Container Manager)

1. Open **Container Manager** (formerly Docker)
2. Go to **Registry** → Search for `portainer/portainer-ce`
3. Download the image
4. Go to **Container** → Create
5. Configure:
   - **Ports**: 9000:9000, 9443:9443, 8000:8000
   - **Volumes**: Create `portainer_data` volume, mount to `/data`
   - **Volume**: Mount `/var/run/docker.sock` (read-only)
6. Apply and start

## 🔐 Initial Configuration

### Step 1: Access Portainer UI

Wait 30 seconds after deployment, then access:

```
HTTPS (Recommended): https://your-server-ip:9443
HTTP (Testing only):  http://your-server-ip:9000
```

**First-time access**: You'll see SSL warning (expected with self-signed cert)

### Step 2: Create Admin Account

On first access:

1. **Username**: `admin` (or your preferred username)
2. **Password**: Create a strong password (minimum 12 characters)
   ```bash
   # Generate secure password
   openssl rand -base64 24
   ```
3. Click **Create user**

⚠️ **IMPORTANT**: You have **5 minutes** to create the account after starting Portainer!

### Step 3: Add Local Docker Environment

After login:

1. Select **Get Started**
2. Portainer automatically detects local Docker environment
3. Click **Connect** to add the local environment

Now you can manage containers on the Portainer Server host itself.

## 🌐 Creating Edge Agent Environments

### Step 1: Navigate to Environments

1. Go to **Environments** in the left sidebar
2. Click **+ Add environment** button

### Step 2: Configure Edge Agent Environment

1. **Environment type**: Select **Docker Standalone**
2. **Start Wizard**: Choose **Edge Agent**
3. **Configuration**:
   - **Name**: `synology-nas` (or descriptive name)
   - **Portainer server URL**: `https://portainer.yourdomain.com:8000`
     - Or use IP: `https://your-server-ip:8000`
     - **Note**: Must be accessible from the remote host!
   - **Enable Edge Compute Features**: ✅ (Optional)
   - **Poll frequency**: 5 seconds (default)

4. Click **Create**

### Step 3: Copy Edge Agent Deployment Info

After creation, you'll see deployment information:

```bash
# Edge Agent Deployment Command (example)
docker run -d \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v /var/lib/docker/volumes:/var/lib/docker/volumes \
  -v /:/host \
  -v portainer_agent_data:/data \
  --restart always \
  -e EDGE=1 \
  -e EDGE_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx \
  -e EDGE_KEY=aHR0cHM6Ly9wb3J0YWluZXIueW91cmRvbWFpbi5jb206ODAwMHx5b3VyLXNlY3JldC1rZXk= \
  -e EDGE_INSECURE_POLL=0 \
  --name portainer_edge_agent \
  portainer/agent:latest
```

**Important values to save**:
- `EDGE_ID`: Unique identifier for this Edge Agent
- `EDGE_KEY`: Base64-encoded authentication key
- Portainer Server URL embedded in EDGE_KEY

### Step 4: Save Credentials Securely

Create a secure note file (don't commit to git!):

```bash
# On your development machine
cat > /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/.edge-credentials << 'EOF'
# Edge Agent Credentials
# Environment: synology-nas
# Created: 2025-10-07

EDGE_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
EDGE_KEY=aHR0cHM6Ly9wb3J0YWluZXIueW91cmRvbWFpbi5jb206ODAwMHx5b3VyLXNlY3JldC1rZXk=
PORTAINER_SERVER_URL=https://portainer.yourdomain.com:8000

# Notes:
# - Keep this file secure and private
# - Never commit to version control
# - Create separate credentials for each Edge Agent environment
EOF

chmod 600 /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/.edge-credentials
```

Add to `.gitignore`:

```bash
echo ".edge-credentials" >> /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/.gitignore
echo "edge-agent.env" >> /Volumes/1TB_DAVINCI/docker/willow/portainer-stacks/.gitignore
```

## 🔧 Network Configuration

### Required Ports

| Port | Protocol | Purpose | Accessibility |
|------|----------|---------|---------------|
| 9443 | HTTPS | Portainer Web UI | Internet/VPN |
| 9000 | HTTP | Portainer Web UI (alt) | Local only |
| 8000 | TCP | Edge Agent Communication | Internet |

### Firewall Rules

#### On Portainer Server Host

```bash
# Allow Portainer UI (HTTPS)
sudo ufw allow 9443/tcp comment 'Portainer UI HTTPS'

# Allow Edge Agent communication
sudo ufw allow 8000/tcp comment 'Portainer Edge Agents'

# Optional: HTTP (not recommended for production)
# sudo ufw allow 9000/tcp comment 'Portainer UI HTTP'

# Reload firewall
sudo ufw reload
```

#### Cloud Provider Firewall (AWS, DigitalOcean, etc.)

Add inbound rules:
- **Port 9443** (HTTPS) - Source: Your IP or 0.0.0.0/0
- **Port 8000** (TCP) - Source: 0.0.0.0/0 (Edge Agents need access)

#### Behind NAT/Router

If Portainer Server is behind NAT:
1. Configure port forwarding:
   - External 9443 → Internal Server 9443
   - External 8000 → Internal Server 8000
2. Use Dynamic DNS if you don't have static IP
3. Update `PORTAINER_SERVER_URL` with public address

## 🔒 Security Hardening

### 1. Enable HTTPS with Valid Certificate

#### Option A: Let's Encrypt with Reverse Proxy (Recommended)

Use Nginx or Traefik as reverse proxy:

```yaml
# Add to docker-compose.yml
services:
  nginx:
    image: nginx:alpine
    ports:
      - "443:443"
      - "80:80"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf:ro
      - ./certs:/etc/nginx/certs:ro
    depends_on:
      - portainer
    networks:
      - portainer_network
```

#### Option B: Portainer Built-in SSL

```bash
# Generate self-signed certificate (testing only)
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout portainer-key.pem \
  -out portainer-cert.pem \
  -subj "/CN=portainer.yourdomain.com"

# Update docker-compose.yml
services:
  portainer:
    # ... existing config ...
    command: >
      --ssl
      --sslcert /certs/portainer-cert.pem
      --sslkey /certs/portainer-key.pem
    volumes:
      - ./portainer-cert.pem:/certs/portainer-cert.pem:ro
      - ./portainer-key.pem:/certs/portainer-key.pem:ro
```

### 2. Change Default Admin Password

1. Go to **User settings** (top right)
2. Click **Change password**
3. Use strong password (24+ characters)
4. Save securely in password manager

### 3. Enable Access Control

1. Go to **Settings** → **Authentication**
2. Consider enabling:
   - OAuth (GitHub, Google, etc.)
   - LDAP/Active Directory
   - SAML SSO

### 4. Restrict Edge Agent Access

For production:

```yaml
# Edge Agent with minimal permissions
environment:
  EDGE_INSECURE_POLL: 0  # Force HTTPS
  CAP_HOST_MANAGEMENT: 0  # Disable host management
```

### 5. Enable Audit Logging

1. Go to **Settings** → **Application settings**
2. Enable **Activity auditing**
3. Configure log retention

## 📊 Monitoring Portainer Server

### Check Portainer Status

```bash
# Container status
docker compose ps

# Resource usage
docker stats portainer-server

# Recent logs
docker compose logs --tail=50 portainer

# Follow logs
docker compose logs -f portainer
```

### Health Check Endpoint

```bash
# Check if Portainer is responding
curl -k https://localhost:9443/api/status

# Expected response: {"Version":"2.x.x"}
```

### Monitor Edge Agents

In Portainer UI:
1. Go to **Environments**
2. Check status indicators:
   - 🟢 Green: Connected
   - 🔴 Red: Disconnected
   - 🟡 Yellow: Connection issues

## 🔄 Updating Portainer Server

### Using Docker Compose

```bash
cd ~/portainer-server

# Pull latest image
docker compose pull

# Stop current instance
docker compose down

# Start with new image
docker compose up -d

# Verify update
docker compose exec portainer /portainer --version
```

### Backup Before Update

```bash
# Backup Portainer data
docker run --rm \
  -v portainer_data:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/portainer-backup-$(date +%Y%m%d).tar.gz -C /data .
```

## 🆘 Troubleshooting

### Issue: Cannot Access Portainer UI

```bash
# Check if container is running
docker ps -a | grep portainer

# Check logs for errors
docker logs portainer-server

# Verify ports are open
sudo netstat -tlnp | grep -E '9443|9000|8000'

# Test local connectivity
curl -k https://localhost:9443/api/status
```

### Issue: Edge Agent Won't Connect

1. **Verify Portainer Server URL is accessible from remote host**:
   ```bash
   # From remote host
   curl -k https://your-portainer-server:8000
   ```

2. **Check firewall rules** on both Portainer Server and remote host

3. **Verify EDGE_ID and EDGE_KEY** are correct

4. **Check Edge Agent logs**:
   ```bash
   docker logs portainer_edge_agent
   ```

### Issue: SSL Certificate Errors

For testing with self-signed certs:
```yaml
environment:
  EDGE_INSECURE_POLL: 1  # Only for development!
```

For production, use valid SSL certificate (Let's Encrypt).

## 📚 Next Steps

Now that Portainer Server is configured:

1. **[SYNOLOGY_DEPLOYMENT.md](./SYNOLOGY_DEPLOYMENT.md)** - Deploy Edge Agent to Synology NAS
2. **[CLOUD_SERVER_DEPLOYMENT.md](./CLOUD_SERVER_DEPLOYMENT.md)** - Deploy Edge Agent to cloud server
3. **[WILLOWCMS_REMOTE_MANAGEMENT.md](./WILLOWCMS_REMOTE_MANAGEMENT.md)** - Deploy WillowCMS via Edge Agent

## 🔗 References

- **Portainer Documentation**: https://docs.portainer.io/start/install-ce
- **Edge Agent Setup**: https://docs.portainer.io/admin/environments/add/edge
- **Security Best Practices**: https://docs.portainer.io/admin/settings
- **Community Forum**: https://community.portainer.io/

---

**Updated**: 2025-10-07
**Version**: 1.0
**Author**: WillowCMS Infrastructure Team
