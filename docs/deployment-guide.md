# WillowCMS DigitalOcean Deployment Guide

This guide covers deploying WillowCMS to a DigitalOcean droplet using the lowest cost configuration ($4/month).

## Overview

- **Droplet Cost**: $4/month (512 MiB RAM, 1 vCPU, 10 GB SSD, 500 GB transfer)
- **Platform**: Ubuntu 24.04 LTS (AMD64)
- **Location**: NYC1 (configurable)
- **Features**: Monitoring enabled, IPv6 enabled, Security hardened

## Prerequisites

1. DigitalOcean account with API token
2. SSH key pair for secure access
3. GitHub repository for CI/CD (optional)
4. Local machine with:
   - `doctl` (DigitalOcean CLI)
   - `gh` (GitHub CLI)
   - `docker` and `docker compose`

## Quick Start

### 1. Create the Droplet

The droplet has already been created with these specifications:
- **Name**: willow-prod
- **ID**: 523484739
- **Region**: NYC1
- **Size**: s-1vcpu-512mb-10gb
- **Image**: Ubuntu 24.04 LTS
- **SSH Key**: mikey-local (ID: 50597026)

### 2. Configure Environment

Copy the environment template:
```bash
cp ./tools/deployment/.env.example ./tools/deployment/.env
```

Edit the `.env` file with your specific values:
```bash
# Required values
DROPLET_IP=your.droplet.ip.address
DEPLOY_USER=deploy
DB_PASSWORD=your_secure_database_password
APP_KEY=your_secure_app_key
```

### 3. Harden the Droplet

Run the setup script to secure your droplet:
```bash
./tools/deployment/setup-droplet.sh
```

This script will:
- Create a deploy user with sudo access
- Disable root SSH login
- Configure UFW firewall
- Install fail2ban for brute-force protection
- Enable automatic security updates
- Install Docker and Docker Compose
- Configure log rotation

### 4. Deploy the Application

Deploy your application:
```bash
./tools/deployment/deploy-to-droplet.sh
```

This script will:
- Copy configuration files to the droplet
- Start Docker containers
- Verify the deployment

## Security Features

### SSH Hardening
- Root login disabled
- Password authentication disabled
- Key-based authentication only
- MaxAuthTries limited to 3
- Idle timeout configured

### Firewall Configuration
- UFW enabled with restrictive rules
- Only ports 22 (SSH), 80 (HTTP), 443 (HTTPS) allowed
- All other traffic blocked by default

### Fail2Ban Protection
- SSH brute-force protection
- 3 failed attempts = 1 hour ban
- Monitors /var/log/auth.log

### Docker Security
- Non-root user execution (UID 1000:1000)
- No new privileges flag
- Log rotation configured
- Resource limits applied

### Automatic Updates
- Security updates installed automatically
- System packages updated daily
- Cleanup of old packages weekly

## Best Practices Implemented

### 1. Principle of Least Privilege
- Dedicated deploy user instead of root
- Minimal firewall rules
- Docker containers run as non-root

### 2. Defense in Depth
- Multiple security layers
- SSH hardening + Fail2ban + Firewall
- Container isolation

### 3. Monitoring and Logging
- DigitalOcean monitoring enabled
- Application logs rotated daily
- System logs monitored by fail2ban

### 4. Secrets Management
- Environment variables in .env files
- GitHub repository secrets for CI/CD
- No hardcoded credentials

### 5. Backup Strategy
- Database backups (configure separately)
- Application code in version control
- Configuration files documented

## Environment Variables

### Required Variables
- `DROPLET_IP`: Your droplet's IP address
- `DEPLOY_USER`: Non-root user for deployments (default: deploy)
- `DB_PASSWORD`: Database password
- `APP_KEY`: Application encryption key

### Optional Variables
- `DOCKER_PLATFORM`: Platform architecture (default: linux/amd64)
- `APP_ENV`: Environment (default: production)
- `BACKUP_SCHEDULE`: Backup frequency (default: daily)

## GitHub Actions Integration

### Repository Secrets
The following secrets are configured in your GitHub repository:
- `DROPLET_IP`: Droplet IP address
- `SSH_PRIVATE_KEY`: SSH private key for deployment (add manually)
- `DO_API_TOKEN`: DigitalOcean API token (optional)

### Adding SSH Private Key
1. Copy your SSH private key:
   ```bash
   cat ~/.ssh/id_rsa
   ```
2. Go to GitHub → Repository → Settings → Secrets and variables → Actions
3. Create new secret named `SSH_PRIVATE_KEY`
4. Paste the entire private key including headers

## Troubleshooting

### Cannot SSH to Droplet
1. Verify droplet IP: `doctl compute droplet get willow-prod`
2. Check SSH key: `ssh-add -l`
3. Try verbose SSH: `ssh -v deploy@your.droplet.ip`

### Docker Containers Not Starting
1. Check logs: `ssh deploy@your.droplet.ip 'cd /opt/willow && docker-compose logs'`
2. Verify environment: `ssh deploy@your.droplet.ip 'cd /opt/willow && cat .env'`
3. Check disk space: `ssh deploy@your.droplet.ip 'df -h'`

### Firewall Issues
1. Check UFW status: `ssh deploy@your.droplet.ip 'sudo ufw status'`
2. Verify port accessibility: `telnet your.droplet.ip 80`
3. Check Docker port mapping: `ssh deploy@your.droplet.ip 'docker ps'`

## Cost Optimization

### Current Setup ($4/month)
- Smallest available droplet
- Basic monitoring included
- 500 GB transfer included
- No additional storage

### Scaling Options
- **Upgrade RAM**: $6/month for 1GB RAM
- **Add Volume**: $10/month for 100GB block storage
- **Load Balancer**: $12/month for high availability
- **Database**: $15/month for managed database

### Cost Monitoring
- Set billing alerts in DigitalOcean dashboard
- Monitor bandwidth usage (500GB limit)
- Use DigitalOcean monitoring for resource usage

## Maintenance

### Regular Tasks
1. **Weekly**: Check system updates and logs
2. **Monthly**: Review security alerts and access logs
3. **Quarterly**: Update Docker images and review configuration

### Automated Tasks
- Security updates (automatic)
- Log rotation (daily)
- Container health checks (every 30s)
- Backup retention (7 days)

## Next Steps

1. **SSL Certificate**: Set up Let's Encrypt for HTTPS
2. **Domain Name**: Point your domain to the droplet IP
3. **Monitoring**: Configure alerts for resource usage
4. **Backups**: Implement database backup strategy
5. **CI/CD**: Set up automated deployments from GitHub

## Support and Resources

- [DigitalOcean Documentation](https://docs.digitalocean.com/)
- [Docker Security Best Practices](https://docs.docker.com/engine/security/)
- [Ubuntu Security Guide](https://ubuntu.com/security)
- [WillowCMS Documentation](../README.md)

---

**Security Note**: This configuration implements industry-standard security practices for a production environment. Regular monitoring and updates are essential for maintaining security.