# Deployment Guide for WhatIsMyAdaptor (WillowCMS)

## Important Notice: Cloudflare Workers Compatibility

⚠️ **This application CANNOT be deployed to Cloudflare Workers.**

### Why Not Cloudflare Workers?

Cloudflare Workers is a serverless JavaScript/WebAssembly platform designed for edge computing. This project is a **PHP-based CakePHP application** with the following requirements that are incompatible with Cloudflare Workers:

- **Runtime**: Requires PHP 8.1+ (Workers supports JavaScript/WASM only)
- **Database**: Requires persistent MySQL database connection
- **Cache/Queue**: Requires Redis for caching and job queuing
- **Filesystem**: Needs writable filesystem for uploads, cache, and logs
- **Long-running processes**: Requires background queue workers for AI processing
- **Docker containers**: Multi-service architecture (nginx, PHP-FPM, MySQL, Redis, Mailpit)

### Recommended Deployment Options

#### Option 1: Docker Compose (Recommended)

The project is fully containerized with Docker Compose. This is the **recommended deployment method** for development and production.

```bash
# Development
docker compose up -d

# Production (with override)
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

**Requirements:**
- Docker Engine 20.10+
- Docker Compose 2.0+
- 2GB RAM minimum (4GB recommended)
- 10GB disk space

**Advantages:**
- Consistent environment across dev/staging/prod
- All dependencies managed (MySQL, Redis, Mailpit)
- Easy scaling with multiple containers
- Built-in health checks and logging

#### Option 2: Traditional PHP Hosting

Deploy to any PHP hosting provider that supports:
- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Redis (optional but recommended)
- Composer for dependency management
- SSH access for queue workers

**Suitable Platforms:**
- DigitalOcean Droplets ($7-12/month)
- AWS EC2 / Lightsail
- Linode
- Vultr
- Traditional shared hosting (with caveats)

#### Option 3: Kubernetes (For Scale)

For high-traffic deployments (1000+ concurrent users):
- Use Helm charts for orchestration
- Separate pods for web, workers, cache
- Managed MySQL (AWS RDS, Google Cloud SQL)
- Managed Redis (AWS ElastiCache, Google Memorystore)

**Note**: Most CMS installations don't need this complexity. Start with Docker Compose.

#### Option 4: Cloudflare Pages (Documentation Only)

While the **main application cannot run on Cloudflare**, you can use **Cloudflare Pages** to host:
- Static documentation site
- Public landing pages
- Static marketing content

The main PHP application must be hosted separately using one of the options above.

## Current Deployment Status

The Cloudflare Workers GitHub integration is **intentionally disabled** via the `wrangler.toml` configuration file. This prevents automatic deployment attempts that would fail.

If you want to use Cloudflare services:
1. Deploy the main PHP app using Docker Compose
2. Optionally create a separate static docs site for Cloudflare Pages
3. Use Cloudflare as a CDN/proxy in front of your PHP application

## Quick Start (Docker Development)

```bash
# 1. Clone the repository
git clone https://github.com/Robjects-Community/WhatIsMyAdaptor.git
cd WhatIsMyAdaptor

# 2. Copy environment configuration
cp docker-compose.override.yml.example docker-compose.override.yml

# 3. Start all services
docker compose up -d

# 4. Access the application
open http://localhost:8080
```

## Production Deployment Checklist

- [ ] Set strong `SECURITY_SALT` in environment
- [ ] Configure production database credentials
- [ ] Set up automated backups (MySQL + uploaded files)
- [ ] Configure Redis for production (persistence, memory limits)
- [ ] Set up queue workers as systemd services or separate containers
- [ ] Configure SSL/TLS certificates (Let's Encrypt recommended)
- [ ] Set up monitoring (logs, metrics, alerts)
- [ ] Configure email delivery (SMTP, SendGrid, Mailgun, etc.)
- [ ] Review and harden PHP security settings
- [ ] Set `DEBUG=false` in production

## Cost Estimates

Based on the project's [deployment comparison](app/src/Controller/Admin/PagesController.php):

- **Development**: Local Docker (free)
- **Demo/Staging**: DigitalOcean Droplet ($7-12/month)
- **Production**: DigitalOcean/Linode ($12-25/month)
- **High Scale**: Kubernetes ($100-300/month)

**Note**: AI API costs (Anthropic Claude, Google Translate) typically exceed infrastructure costs for active CMS deployments (~$250/month for moderate AI usage).

## Getting Help

- **Issues**: [GitHub Issues](https://github.com/Robjects-Community/WhatIsMyAdaptor/issues)
- **Documentation**: See `docs/` directory
- **Docker Guide**: See `docker/README.md` (if available)
- **CakePHP Docs**: https://book.cakephp.org/5/

## Migration from Cloudflare Workers (If Attempted)

If you previously attempted to deploy to Cloudflare Workers:

1. **Stop the integration**: Remove the Cloudflare Workers GitHub app from this repository
2. **Deploy properly**: Use Docker Compose or traditional PHP hosting
3. **Optional**: Set up Cloudflare as a reverse proxy/CDN in front of your PHP server

The `wrangler.toml` file in this repository serves only to disable automatic deployment attempts.

---

**Last Updated**: October 2025  
**Maintained By**: Robjects Community
