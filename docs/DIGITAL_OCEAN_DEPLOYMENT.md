# Digital Ocean Docker Droplet Deployment Guide

## Recommended Configuration

### Droplet Specifications
- **Size**: Basic-S ($12/month)
- **RAM**: 2GB (sufficient for PHP compilation + all services)
- **Storage**: 50GB SSD 
- **vCPU**: 1 core
- **Region**: Choose closest to your users

### Optional Add-ons
- **Backups**: $2.40/month (20% of droplet cost) - Recommended
- **Block Storage**: $1/month for 10GB volume (for database persistence) - Optional
- **Monitoring**: Free (built-in)

## Docker Compose Optimizations

Add resource limits to prevent memory issues:

```yaml
services:
  willowcms:
    # ... existing config
    deploy:
      resources:
        limits:
          memory: 512M
        reservations:
          memory: 256M
    restart: unless-stopped

  mysql:
    # ... existing config
    deploy:
      resources:
        limits:
          memory: 512M
        reservations:
          memory: 256M
    restart: unless-stopped

  redis:
    # ... existing config
    deploy:
      resources:
        limits:
          memory: 128M
        reservations:
          memory: 64M
    restart: unless-stopped

  phpmyadmin:
    # ... existing config
    deploy:
      resources:
        limits:
          memory: 128M
    restart: unless-stopped
```

## Deployment Steps

### 1. Create Droplet
```bash
# Via DO CLI (optional)
doctl compute droplet create willow-cms \
  --image docker-20-04 \
  --size s-2vcpu-2gb \
  --region nyc1 \
  --ssh-keys YOUR_SSH_KEY_ID
```

### 2. Initial Server Setup
```bash
# SSH into droplet
ssh root@YOUR_DROPLET_IP

# Create non-root user
adduser deploy
usermod -aG sudo deploy
usermod -aG docker deploy

# Copy your project
scp -r /path/to/willow deploy@YOUR_DROPLET_IP:/home/deploy/
```

### 3. Deploy Application
```bash
# On the droplet
cd /home/deploy/willow
cp .env.example .env
# Edit .env with production values

# Build and start services
docker-compose up -d --build

# Check status
docker-compose ps
docker-compose logs
```

## Security Configuration

### Firewall Setup
```bash
# Via DO console or ufw
ufw allow ssh
ufw allow 8080  # Your app port
ufw allow 3310  # MySQL (if external access needed)
ufw enable
```

### Environment Variables
Ensure your `.env` file has secure values:
```bash
MYSQL_ROOT_PASSWORD=your-secure-password
REDIS_PASSWORD=your-redis-password
OPENAI_API_KEY=your-api-key
```

## Monitoring & Maintenance

### Enable DO Monitoring
- Free monitoring via DO dashboard
- Tracks CPU, memory, disk usage
- Alerts via email

### Automated Backups
- Enable in DO console: $2.40/month
- Daily snapshots of entire droplet
- 4 backups retained

### Log Management
```bash
# Configure log rotation
echo '/home/deploy/willow/infrastructure/docker/logs/*/*.log {
    daily
    rotate 7
    compress
    delaycompress
    missingok
    create 644 deploy deploy
}' > /etc/logrotate.d/willow
```

## Cost Breakdown

### Monthly Costs
- **Droplet Basic-S**: $12.00
- **Backups (optional)**: $2.40
- **Block Storage (optional)**: $1.00
- **Total**: $12-15/month

### vs App Platform
- **App Platform equivalent**: $27-35/month
- **Savings**: 50-60% cost reduction

## Performance Optimization

### Build Caching
- First deployment: ~5 minutes (compiling PHP extensions)
- Subsequent deployments: ~30 seconds (cached layers)

### Resource Monitoring
```bash
# Monitor resource usage
docker stats
docker system df

# Clean up unused images/containers
docker system prune -f
```

## Scaling Considerations

### Traffic Growth
- Basic-S handles 1000-5000 requests/hour
- Upgrade to General Purpose droplets for more traffic
- Add load balancer when multiple droplets needed

### Database Growth
- Add block storage volume for MySQL data
- Consider managed database when > 10GB data

## Troubleshooting

### Common Issues
1. **Out of memory during build**
   - Temporarily scale up during deployment
   - Scale back down after successful build

2. **Slow performance**
   - Check `docker stats` for resource usage
   - Adjust memory limits in docker-compose.yml

3. **PHP extension errors**
   - The fixed Dockerfile handles this automatically
   - Extensions compile during build process

## Migration from Local Development

Your existing setup requires NO changes:
- Same `docker-compose.yml` file
- Same `.env` configuration pattern  
- Same Dockerfile (with our PHP extension fixes)
- Same volume mounts and networking