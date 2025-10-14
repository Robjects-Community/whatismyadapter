# WillowCMS Infrastructure Documentation

This document contains the current infrastructure setup and configuration details for the WillowCMS deployment.

## Current Infrastructure

### DigitalOcean Droplet Details

| Property | Value |
|----------|-------|
| **Name** | willow-prod |
| **Droplet ID** | 523484739 |
| **IP Address** | `[CONFIGURED_IN_ENV]` |
| **Region** | NYC1 (New York 1) |
| **Size** | s-1vcpu-512mb-10gb |
| **Cost** | $4.00/month ($0.00595/hour) |
| **RAM** | 512 MiB |
| **vCPUs** | 1 |
| **SSD Storage** | 10 GB |
| **Transfer** | 500 GB/month |
| **Operating System** | Ubuntu 24.04 LTS x64 |
| **Platform** | linux/amd64 |
| **Features** | Monitoring, IPv6, Droplet Agent |
| **Tags** | production, willow, docker |

### SSH Configuration

| Property | Value |
|----------|-------|
| **SSH Key Name** | mikey-local |
| **SSH Key ID** | 50597026 |
| **Deploy User** | deploy |
| **SSH Port** | 22 |
| **Root Login** | Disabled (security hardening) |
| **Password Auth** | Disabled |
| **Key-based Auth** | Enabled |

## Security Configuration

### Firewall Rules (UFW)

| Port | Protocol | Direction | Purpose |
|------|----------|-----------|---------|
| 22 | TCP | Incoming | SSH Access |
| 80 | TCP | Incoming | HTTP Traffic |
| 443 | TCP | Incoming | HTTPS Traffic |
| All Others | Any | Incoming | DENIED (default) |
| All | Any | Outgoing | ALLOWED (default) |

### Fail2Ban Configuration

| Setting | Value |
|---------|-------|
| **Service** | sshd |
| **Max Retries** | 3 attempts |
| **Ban Time** | 3600 seconds (1 hour) |
| **Find Time** | 600 seconds (10 minutes) |
| **Log File** | /var/log/auth.log |

### Docker Security

| Setting | Value |
|---------|-------|
| **User/Group** | 1000:1000 (non-root) |
| **Privileges** | no-new-privileges |
| **Log Driver** | json-file |
| **Log Rotation** | 10MB max, 3 files |
| **Live Restore** | Enabled |
| **Userland Proxy** | Disabled |

## Application Stack

### Docker Services

| Service | Container Name | Image | Ports | Purpose |
|---------|----------------|-------|-------|---------|
| **WillowCMS** | willow-prod | php:8.3-apache | 80:80, 443:443 | Main application |
| **Database** | willow-db-prod | mariadb:11.4-noble | 127.0.0.1:3306:3306 | MySQL database |
| **Cache** | willow-redis-prod | redis:7-alpine | Internal | Redis cache |
| **Admin** | willow-phpmyadmin-prod | phpmyadmin:latest | 127.0.0.1:8080:80 | Database admin |

### Volume Configuration

| Volume | Type | Host Path | Purpose |
|--------|------|-----------|---------|
| **db_data** | bind | /var/lib/docker/volumes/willow_db_data | Database storage |
| **redis_data** | bind | /var/lib/docker/volumes/willow_redis_data | Cache storage |
| **app_files** | bind | /opt/willow/app | Application code |
| **logs** | bind | /opt/willow/logs | Application logs |

### Network Configuration

| Network | Driver | Name |
|---------|--------|------|
| **Default** | bridge | willow_production |

## Monitoring and Logging

### DigitalOcean Monitoring

- **Enabled**: Yes
- **Metrics Collected**: CPU, Memory, Disk, Network
- **Retention**: 30 days (free tier)
- **Alerting**: Available (configure separately)

### Log Management

| Log Type | Location | Rotation | Retention |
|----------|----------|----------|-----------|
| **Application** | /opt/willow/logs/ | Daily | 30 days |
| **Apache** | /opt/willow/logs/ | Daily | 30 days |
| **Docker** | /var/lib/docker/containers/ | 10MB/3 files | Auto |
| **System** | /var/log/ | System default | System default |

## Backup Strategy

### Current Implementation

| Component | Backup Method | Frequency | Location | Retention |
|-----------|---------------|-----------|----------|-----------|
| **Database** | Manual/Script | On-demand | /opt/willow/backups/db | 7 days |
| **Application Code** | Git Repository | On commit | GitHub | Unlimited |
| **Configuration** | Git Repository | On commit | GitHub | Unlimited |
| **Docker Volumes** | Manual | On-demand | Host filesystem | Manual |

### Recommended Improvements

1. **Automated Database Backups**: Daily MySQL dumps
2. **Volume Snapshots**: DigitalOcean volume snapshots
3. **Offsite Storage**: S3-compatible backup storage
4. **Backup Testing**: Regular restore testing

## Performance Specifications

### Current Limitations (512 MiB RAM)

- **Concurrent Users**: ~10-50 (depending on application)
- **Database Size**: Limited by 10GB SSD
- **Traffic**: 500GB transfer/month included
- **Processing**: Single vCPU, suitable for small websites

### Scaling Recommendations

| Resource | Current | Next Tier | Cost Increase |
|----------|---------|-----------|---------------|
| **RAM** | 512 MiB | 1 GB | +$2/month |
| **Storage** | 10 GB | Add 100GB Volume | +$10/month |
| **CPU** | 1 vCPU | 2 vCPU (requires size upgrade) | +$8/month |
| **Transfer** | 500 GB | 1000 GB (with upgrade) | Included |

## Cost Analysis

### Current Monthly Costs

| Service | Cost | Notes |
|---------|------|-------|
| **Droplet** | $4.00 | s-1vcpu-512mb-10gb |
| **Monitoring** | $0.00 | Included |
| **IPv6** | $0.00 | Included |
| **Transfer** | $0.00 | 500GB included |
| **DNS** | $0.00 | Not using DO DNS |
| **Backups** | $0.00 | Manual backups |
| **Total** | **$4.00** | Base configuration |

### Potential Additional Costs

| Service | Cost | When Needed |
|---------|------|-------------|
| **Automated Backups** | 20% of droplet cost | $0.80/month for weekly |
| **Additional Storage** | $0.10/GB/month | When 10GB is full |
| **Load Balancer** | $12/month | High availability |
| **Managed Database** | $15/month | Database scaling |
| **Bandwidth Overage** | $0.01/GB | Over 500GB transfer |

## Security Best Practices Implemented

### ✅ Completed Security Measures

1. **SSH Hardening**
   - Root login disabled
   - Password authentication disabled
   - SSH key authentication only
   - Connection limits configured

2. **Firewall Protection**
   - UFW enabled with restrictive rules
   - Only necessary ports open
   - Default deny policy

3. **Intrusion Prevention**
   - Fail2ban monitoring SSH attempts
   - Automatic IP blocking for brute force

4. **System Hardening**
   - Automatic security updates enabled
   - Non-root user for services
   - Docker security best practices

5. **Access Control**
   - Dedicated deploy user
   - Sudo access properly configured
   - SSH key management

### 🔄 Recommended Additional Security

1. **SSL/TLS Certificate**: Let's Encrypt for HTTPS
2. **Security Scanning**: Regular vulnerability scans
3. **Log Monitoring**: Centralized log analysis
4. **Backup Encryption**: Encrypt backup data
5. **Network Segmentation**: VPC configuration

## Maintenance Schedule

### Daily (Automated)
- Security updates installation
- Log rotation
- Container health checks
- Backup cleanup (when automated)

### Weekly (Manual)
- Review system logs
- Check resource usage
- Verify backup integrity
- Update Docker images (as needed)

### Monthly (Manual)
- Full system health check
- Security audit
- Performance analysis
- Cost optimization review

### Quarterly (Manual)
- Infrastructure review
- Security configuration update
- Disaster recovery testing
- Scaling assessment

## Contact and Access Information

### Repository Information
- **GitHub Repository**: garzarobm/willow
- **Deployment Branch**: main
- **Configuration Path**: ./tools/deployment/

### Access Credentials
- **SSH Access**: `ssh deploy@[DROPLET_IP]`
- **Application URL**: `http://[DROPLET_IP]`
- **Database Admin**: `http://[DROPLET_IP]:8080`

### Emergency Contacts
- **DigitalOcean Support**: Available via dashboard
- **GitHub Support**: For repository issues
- **System Administrator**: [Configure as needed]

## Disaster Recovery

### Recovery Time Objectives (RTO)
- **Droplet Replacement**: 30 minutes (new droplet creation)
- **Application Deployment**: 15 minutes (automated script)
- **Database Restoration**: 10-60 minutes (depending on backup size)
- **Total Recovery Time**: 55-105 minutes

### Recovery Point Objectives (RPO)
- **Application Code**: 0 minutes (Git repository)
- **Configuration**: 0 minutes (Git repository)
- **Database**: 24 hours (daily backups recommended)
- **User Data**: Depends on backup frequency

---

**Last Updated**: $(date)  
**Document Version**: 1.0  
**Next Review**: $(date -d "+3 months")