# Environment Variables Guide

## File Locations

### 1. `tools/deployment/.env`
**Purpose:** Build-time and deployment configuration for DigitalOcean droplet production environment.

**Used by:**
- `tools/deployment/deploy-to-droplet.sh` - deployment script
- `tools/deployment/docker-compose-prod.yml` - production compose file
- `.github/workflows/deploy-to-droplet.yml` - CI/CD workflow

**Contains:**
- Droplet connection info (DROPLET_IP, SSH_USER)
- Docker build args (USER_ID=1000, GROUP_ID=1000, DOCKER_PLATFORM)
- Database and Redis connection details
- Security secrets (APP_KEY, JWT_SECRET)
- Optional admin and email settings

### 2. `app/config/.env`
**Purpose:** Application-level runtime variables for CakePHP.

**Used by:**
- CakePHP framework at runtime
- `docker-compose.yml` via env_file directive (local development)

**Contains:**
- Core app settings (APP_NAME, DEBUG, LOG_LEVEL)
- Application URLs (APP_FULL_BASE_URL)
- Security salt (SECURITY_SALT)
- Database and Redis configuration
- Email settings
- External API keys (OpenAI, YouTube, etc.)

## Setup Instructions

### Local Development
1. Copy example files (if provided) or create new .env files with templates above
2. Set `DEBUG=true` and `APP_ENV=development` in `app/config/.env`
3. Use `localhost` for all URLs
4. Run: `docker compose up -d`

### Production Droplet Deployment

#### Option A: GitHub Actions (Recommended)
1. Set all secrets in GitHub repository settings
2. Workflow automatically populates `tools/deployment/.env`
3. Push to trigger deployment

#### Option B: Manual Deployment
1. SSH to droplet as deploy user
2. Create `tools/deployment/.env` with production values
3. Set `APP_ENV=production`, `APP_DEBUG=false`
4. Replace localhost URLs with domain: `APP_URL=https://yourdomain.com`
5. Run: `./tools/deployment/deploy-to-droplet.sh` (can be run from any directory)

#### Option C: Portainer Stack
1. Use Portainer UI to create stack
2. Paste `docker-compose-prod.yml` content
3. Set environment variables via stack.env editor
4. Deploy stack

## Validation Commands

### Render and validate production compose:
```bash
docker compose --env-file tools/deployment/.env \
  -f tools/deployment/docker-compose-prod.yml config
```

### Check required variables are set:
```bash
# List all variables in deployment .env
cat tools/deployment/.env | grep -v '^#' | grep -v '^$'

# Verify critical ones are not blank
grep -E "^(DB_PASSWORD|SECURITY_SALT|REDIS_PASSWORD)=" tools/deployment/.env
```

### Test app can read variables:
```bash
# Inside running container
docker compose exec willowcms php -r "echo env('DB_HOST');"
```

## Domain Configuration

When deploying with a real domain instead of localhost:

1. **In `tools/deployment/.env`:**
   ```bash
   APP_URL=https://yourdomain.com
   APP_FULL_BASE_URL=https://yourdomain.com
   ```

2. **In `app/config/.env`:**
   ```bash
   APP_FULL_BASE_URL=https://yourdomain.com
   ```

3. **Update nginx/web server configuration** to match domain

4. **Ensure SSL certificate** is configured (Let's Encrypt recommended)

## Security Notes

- Both `.env` files are excluded via `.gitignore` - never commit secrets
- Use UID:GID 1000:1000 for container processes (standard non-root user)
- Rotate secrets regularly (APP_KEY, JWT_SECRET, DB_PASSWORD)
- Store production secrets in GitHub Secrets or secure vault
- Use different values for dev/staging/production environments
- Leave API keys blank in templates; populate securely at deployment time

## Troubleshooting

**Container won't start:**
- Check `docker compose logs willowcms` for missing variable errors
- Verify DB_*, SECURITY_SALT are set
- Ensure Redis password matches between db and app configs

**Permission errors:**
- Verify USER_ID/GROUP_ID match deploy user (1000:1000)
- Check volume mount permissions

**App can't connect to services:**
- Verify DB_HOST=db, REDIS_HOST=redis (Docker network hostnames)
- Check services are running: `docker compose ps`

**Wrong URL in generated links:**
- Update APP_FULL_BASE_URL to match actual domain
- Clear cache: `docker compose exec willowcms bin/cake cache clear_all`