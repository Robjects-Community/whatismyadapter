# Docker Compose Environment Configuration

This document explains how environment variables from `config/.env` are handled in this Docker Compose setup.

## Configuration Status ✅

Your Docker Compose setup is **properly configured** to use environment variables from `config/.env`:

1. **Environment File Location**: `config/.env` ✓
2. **Docker Compose Version**: v2.39.1 (supports `--env-file`) ✓
3. **Variable Substitution**: Working correctly ✓

## How It Works

### Current Setup

Your `docker-compose.yml` already includes:
```yaml
services:
  willowcms:
    env_file:
      - ./config/.env
    # ... other services also reference config/.env
```

This configuration works in two ways:
1. **Container Environment**: Variables are loaded into each container's environment
2. **Compose Substitution**: Variables like `${APP_NAME}` are substituted in the compose file

## Usage Methods

### Method 1: Docker Compose v2 with --env-file (RECOMMENDED)

```bash
# Direct usage
docker compose --env-file config/.env up -d
docker compose --env-file config/.env down
docker compose --env-file config/.env logs

# Test configuration
docker compose --env-file config/.env config >/dev/null
```

### Method 2: Using the Helper Script (EASIEST)

We've created a `docker-compose.sh` helper script that automatically handles the environment file:

```bash
# Make executable (first time only)
chmod +x docker-compose.sh

# Use exactly like regular docker compose
./docker-compose.sh up -d
./docker-compose.sh down
./docker-compose.sh logs willowcms
./docker-compose.sh exec willowcms bash
```

### Method 3: Legacy/Fallback Method

If you need to use the legacy approach or have issues with the `--env-file` flag:

```bash
# Export variables to shell environment
set -a
. config/.env
set +a

# Then use regular docker compose
docker compose up -d
```

## Environment Variables in Your Setup

### Build Arguments
- `${UID:-1000}` - User ID (defaults to 1000 if not set)
- `${GID:-1000}` - Group ID (defaults to 1000 if not set)

### Application Variables
All variables from `config/.env` are available:
- Database credentials (DB_HOST, DB_USERNAME, etc.)
- Application settings (APP_NAME, DEBUG, etc.)
- Service configurations (Redis, Email, etc.)

## Troubleshooting

### Check Configuration
```bash
# Validate compose file with environment
docker compose --env-file config/.env config

# Check specific service environment
docker compose --env-file config/.env config | grep -A 10 environment:
```

### Check Variable Substitution
```bash
# View resolved build args
docker compose --env-file config/.env config | grep -A 5 "args:"

# Check if variables are being substituted
docker compose --env-file config/.env config | grep "APP_NAME\|DB_HOST"
```

### Common Issues
1. **Variables not found**: Ensure `config/.env` exists and contains the required variables
2. **Permission errors**: Check file permissions on `config/.env`
3. **Syntax errors**: Validate `.env` file format (no spaces around `=`)

## Best Practices

1. **Always use `--env-file config/.env`** with Docker Compose v2
2. **Use the helper script** for consistent behavior
3. **Test configuration** before running services: `docker compose --env-file config/.env config >/dev/null`
4. **Keep sensitive variables secure** in `config/.env`
5. **Never commit real credentials** to version control

## Examples

```bash
# Start all services
./docker-compose.sh up -d

# View logs for specific service
./docker-compose.sh logs -f willowcms

# Access database
./docker-compose.sh exec mysql mysql -u ${DB_USERNAME} -p

# Rebuild and restart
./docker-compose.sh down
./docker-compose.sh up --build -d
```

## Verification

To verify everything is working:

```bash
# 1. Test configuration
./docker-compose.sh config >/dev/null && echo "✅ Configuration valid"

# 2. Check environment variables in running container
./docker-compose.sh exec willowcms env | grep APP_NAME

# 3. Verify database connection
./docker-compose.sh exec willowcms cat config/app_local.php | grep -A 5 Datasources
```
