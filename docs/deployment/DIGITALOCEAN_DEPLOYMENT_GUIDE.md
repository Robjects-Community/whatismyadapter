# DigitalOcean App Platform Deployment Guide

Complete guide for deploying WillowCMS to DigitalOcean App Platform with managed services.

## 📋 Prerequisites

### Required Tools
- [DigitalOcean CLI (doctl)](https://docs.digitalocean.com/reference/doctl/how-to/install/) installed and authenticated
- [AWS CLI](https://aws.amazon.com/cli/) for Spaces management
- Git repository hosted on GitHub/GitLab
- MySQL client for database operations

### Account Setup
```bash
# Install and configure doctl
doctl auth init

# Verify access
doctl account get
```

## 🗂️ Service Mapping

Your current Docker services map to DigitalOcean services as follows:

| Local Service | DigitalOcean Service | Purpose |
|---|---|---|
| `willowcms` | App Platform Web Service | Main application |
| `mysql` | Managed MySQL Database | Database storage |
| `redis` | Managed Redis | Cache & sessions |
| `phpmyadmin` | ❌ Removed | Use local admin tools |
| `mailpit` | ❌ Removed | Use SMTP service |
| File uploads | Spaces bucket | File storage |

## 🚀 Step-by-Step Deployment

### Step 1: Create DigitalOcean Resources

#### Create Managed MySQL Database
```bash
# Create MySQL cluster
doctl databases create willowcms-mysql-staging \
  --engine mysql \
  --version 8 \
  --size db-s-1vcpu-1gb \
  --region nyc1 \
  --num-nodes 1

# Get connection details
doctl databases connection willowcms-mysql-staging

# Create application database and user
doctl databases sql willowcms-mysql-staging \
  --query "CREATE DATABASE willowcms_staging;"
doctl databases sql willowcms-mysql-staging \
  --query "CREATE USER 'willowcms_user'@'%' IDENTIFIED BY 'secure_password_here';"
doctl databases sql willowcms-mysql-staging \
  --query "GRANT ALL PRIVILEGES ON willowcms_staging.* TO 'willowcms_user'@'%';"
```

#### Create Managed Redis
```bash
# Create Redis cluster  
doctl databases create willowcms-redis-staging \
  --engine redis \
  --version 7 \
  --size db-s-1vcpu-1gb \
  --region nyc1 \
  --num-nodes 1

# Get connection details
doctl databases connection willowcms-redis-staging
```

#### Create Spaces Bucket
```bash
# Create bucket for file uploads
doctl spaces buckets create willowcms-staging-uploads \
  --region nyc3

# Create access keys for programmatic access
doctl spaces keys create willowcms-staging-access
```

### Step 2: Update Your Application

#### Add Health Check Routes
The health controller has been created at `app/src/Controller/HealthController.php`. Add these routes to your `config/routes.php`:

```php
// Add to config/routes.php
$builder->connect('/healthz', ['controller' => 'Health', 'action' => 'healthz']);
$builder->connect('/readyz', ['controller' => 'Health', 'action' => 'readyz']);
```

#### Configure CakePHP for App Platform

Update your `app/config/app_local.php` to handle App Platform environment:

```php
<?php
// app/config/app_local.php for App Platform

// Database configuration using DATABASE_URL
if ($databaseUrl = env('DATABASE_URL')) {
    $config = parse_url($databaseUrl);
    $datasources['default'] = [
        'host' => $config['host'],
        'port' => $config['port'],
        'username' => $config['user'], 
        'password' => $config['pass'],
        'database' => ltrim($config['path'], '/'),
        'driver' => 'Cake\Database\Driver\Mysql',
        'persistent' => false,
        'encoding' => 'utf8mb4',
        'timezone' => 'UTC',
        'flags' => [],
        'cacheMetadata' => true,
        'log' => false,
        'quoteIdentifiers' => false,
    ];
}

// Redis configuration using REDIS_URL
if ($redisUrl = env('REDIS_URL')) {
    $config = parse_url($redisUrl);
    $cache['_cake_core_']['config']['server'] = [
        'host' => $config['host'],
        'port' => $config['port'] ?? 6379,
        'password' => $config['pass'] ?? null,
        'database' => 0,
    ];
    $cache['_cake_model_']['config']['server'] = $cache['_cake_core_']['config']['server'];
}

return [
    'debug' => filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'Security' => [
        'salt' => env('SECURITY_SALT', 'change_me_to_32_char_random_string'),
    ],
    'Datasources' => $datasources ?? [],
    'Cache' => $cache ?? [],
];
```

### Step 3: Deploy to App Platform

#### Deploy Staging Environment
```bash
# Update the GitHub repo in do-app-staging.yaml
sed -i 's/your-username\/willow/YOUR_GITHUB_USERNAME\/willow/g' tools/deploy/do-app-staging.yaml

# Create the app
doctl apps create --spec tools/deploy/do-app-staging.yaml

# Get app ID 
doctl apps list

# Set environment secrets (replace <APP_ID> with actual ID)
doctl apps update <APP_ID> --spec tools/deploy/do-app-staging.yaml
```

#### Configure Environment Variables
```bash
# Set database URL (replace with actual values from database creation)
doctl apps create-deployment <APP_ID> \
  --env DATABASE_URL=mysql://user:pass@host:port/dbname

# Set Redis URL
doctl apps create-deployment <APP_ID> \
  --env REDIS_URL=rediss://:password@host:port/0

# Set security salt (generate 32 character random string)
doctl apps create-deployment <APP_ID> \
  --env SECURITY_SALT=$(openssl rand -base64 32)

# Set Spaces credentials
doctl apps create-deployment <APP_ID> \
  --env SPACES_KEY=your_spaces_access_key \
  --env SPACES_SECRET=your_spaces_secret_key
```

### Step 4: Import Database

```bash
# Export from local development
docker-compose exec mysql mysqldump \
  -u root -p${MYSQL_ROOT_PASSWORD} willowcms_dev > willowcms_export.sql

# Import to managed database (replace with actual connection details)
mysql -h your-managed-db-host \
  -P 25060 \
  -u willowcms_user \
  -p willowcms_staging < willowcms_export.sql

# Clean up local export
rm willowcms_export.sql
```

### Step 5: Configure File Upload Storage

Update your file upload configuration to use Spaces:

```php
// In app/config/app_local.php
'Storage' => [
    'default' => [
        'className' => 'League\Flysystem\Adapter\AwsS3v3\AwsS3Adapter',
        'config' => [
            'credentials' => [
                'key' => env('SPACES_KEY'),
                'secret' => env('SPACES_SECRET'),
            ],
            'region' => env('SPACES_REGION', 'nyc3'),
            'version' => 'latest',
            'endpoint' => 'https://' . env('SPACES_ENDPOINT'),
            'bucket' => env('SPACES_BUCKET'),
        ],
    ],
],
```

### Step 6: Deploy Production

```bash
# Update production spec file
sed -i 's/your-username\/willow/YOUR_GITHUB_USERNAME\/willow/g' tools/deploy/do-app-production.yaml

# Create production resources first
doctl databases create willowcms-mysql-production --engine mysql --version 8 --size db-s-2vcpu-4gb --region nyc1
doctl databases create willowcms-redis-production --engine redis --version 7 --size db-s-2vcpu-4gb --region nyc1  
doctl spaces buckets create willowcms-production-uploads --region nyc3

# Deploy production app
doctl apps create --spec tools/deploy/do-app-production.yaml
```

## 🔒 Security Configuration

### Environment Variables Setup

**Never commit secrets to your repository.** Set these via App Platform console or CLI:

#### Required Secrets:
```bash
DATABASE_URL=mysql://user:pass@host:port/dbname?ssl-mode=REQUIRED
REDIS_URL=rediss://:password@host:port/0
SECURITY_SALT=your_32_character_random_string
OPENAI_API_KEY=your_openai_api_key
SPACES_KEY=your_spaces_access_key
SPACES_SECRET=your_spaces_secret_key
```

#### Public Environment Variables:
```bash
APP_ENV=staging  # or production
DEBUG=false
APP_NAME=WillowCMS
TRUSTED_PROXIES=*
FORCE_HTTPS=true
```

### Database Security
```bash
# Add App Platform as trusted source to database
doctl databases firewall append willowcms-mysql-staging \
  --rule type:app,value:willowcms-staging

# Enable SSL-only connections
doctl databases update willowcms-mysql-staging --sql-mode REQUIRE_SSL
```

## 📊 Monitoring & Alerts

### Configure Alerts
```bash
# CPU utilization alert
doctl monitoring alerts create \
  --type app_cpu_percentage \
  --description "High CPU usage" \
  --compare greater_than \
  --value 80 \
  --window 5m \
  --entities willowcms-staging

# Memory utilization alert  
doctl monitoring alerts create \
  --type app_memory_percentage \
  --description "High memory usage" \
  --compare greater_than \
  --value 80 \
  --window 5m \
  --entities willowcms-staging
```

### View Logs
```bash
# Application logs
doctl apps logs willowcms-staging --type build
doctl apps logs willowcms-staging --type deploy  
doctl apps logs willowcms-staging --type run

# Database logs
doctl databases logs willowcms-mysql-staging
```

## 🔄 CI/CD Setup

### Automatic Deployments
Configure in your App Platform settings:
- **Staging**: Auto-deploy from `develop` branch
- **Production**: Manual deploy from `main` branch

### GitHub Actions Integration
```yaml
# .github/workflows/deploy.yml
name: Deploy to DigitalOcean
on:
  push:
    branches: [main, develop]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v2
    - name: Install doctl
      uses: digitalocean/action-doctl@v2
      with:
        token: ${{ secrets.DIGITALOCEAN_ACCESS_TOKEN }}
    - name: Deploy
      run: |
        if [ "${{ github.ref }}" = "refs/heads/main" ]; then
          doctl apps create-deployment ${{ secrets.PRODUCTION_APP_ID }}
        else
          doctl apps create-deployment ${{ secrets.STAGING_APP_ID }}
        fi
```

## 🧪 Testing Deployment

### Health Checks
```bash
# Test health endpoints
curl https://willowcms-staging.yourdomain.com/healthz
curl https://willowcms-staging.yourdomain.com/readyz

# Expected response:
{
  "status": "healthy",
  "service": "WillowCMS", 
  "timestamp": "2024-01-15T10:30:00+00:00",
  "environment": "staging"
}
```

### Smoke Tests
```bash
# Test main application
curl -I https://willowcms-staging.yourdomain.com/

# Test admin login
curl -I https://willowcms-staging.yourdomain.com/admin/login

# Test file upload endpoint
curl -I https://willowcms-staging.yourdomain.com/admin/files/upload
```

## 💰 Cost Optimization

### Staging Environment (~$62/month)
- Web Service: basic-xxs (512MB RAM)
- MySQL: db-s-1vcpu-1gb
- Redis: db-s-1vcpu-1gb
- Spaces: 250GB storage

### Production Environment (~$164/month)  
- Web Service: 2x basic-xs (1GB RAM each)
- MySQL: db-s-2vcpu-4gb
- Redis: db-s-2vcpu-4gb  
- Spaces: Variable based on usage

### Cost Reduction Tips
```bash
# Scale down non-production environments
doctl apps update <STAGING_APP_ID> --spec tools/deploy/do-app-staging.yaml

# Use smaller database sizes for development
# Enable database connection pooling
# Implement efficient caching strategies
```

## 🚨 Troubleshooting

### Common Issues

#### Build Failures
```bash
# Check build logs
doctl apps logs <APP_ID> --type build

# Common fixes:
# - Verify composer.json PHP version
# - Check for missing PHP extensions
# - Ensure proper directory structure
```

#### Database Connection Issues  
```bash
# Test database connectivity
doctl databases connection <DB_ID>

# Verify SSL requirements
# Check firewall rules
# Validate DATABASE_URL format
```

#### File Upload Problems
```bash
# Test Spaces connectivity
aws s3 ls s3://your-bucket-name --endpoint-url=https://nyc3.digitaloceanspaces.com

# Verify CORS settings
# Check bucket permissions
# Validate access keys
```

### Recovery Procedures

#### Rollback Deployment
```bash
# List previous deployments
doctl apps list-deployments <APP_ID>

# Rollback to previous version
doctl apps create-deployment <APP_ID> --deployment-id <PREVIOUS_DEPLOYMENT_ID>
```

#### Database Recovery
```bash
# Restore from backup (automatic daily backups available)
doctl databases backups list <DB_ID>
doctl databases restore <DB_ID> --backup-id <BACKUP_ID>
```

## 📚 Additional Resources

- [DigitalOcean App Platform Documentation](https://docs.digitalocean.com/products/app-platform/)
- [Managed Databases Guide](https://docs.digitalocean.com/products/databases/)
- [Spaces Documentation](https://docs.digitalocean.com/products/spaces/)
- [CakePHP Deployment Best Practices](https://book.cakephp.org/5/en/deployment.html)

## ✅ Deployment Checklist

### Pre-Deployment
- [ ] GitHub repository configured
- [ ] Health check endpoints implemented  
- [ ] Database migration scripts ready
- [ ] Environment variables documented
- [ ] Backup strategy implemented

### Deployment
- [ ] MySQL database created
- [ ] Redis cluster created
- [ ] Spaces bucket created
- [ ] App Platform application deployed
- [ ] Environment variables configured
- [ ] Database imported
- [ ] DNS configured

### Post-Deployment  
- [ ] Health checks passing
- [ ] Application accessible
- [ ] Admin login working
- [ ] File uploads functional
- [ ] Monitoring alerts configured
- [ ] Backup jobs scheduled

### Security Verification
- [ ] SSL certificates active
- [ ] Database firewall configured
- [ ] No secrets in code
- [ ] Access keys rotated
- [ ] Log integrity checks enabled