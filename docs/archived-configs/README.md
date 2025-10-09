# Archived Environment Configurations

## Directory Cleanup - October 9, 2025

### What Was Removed
The `./env/` directory was removed as it contained redundant environment files that were not being referenced by any Docker Compose files.

### Files That Were in `./env/`
- `local.env` - Local development environment variables (duplicate)
- `remote.env` - Remote deployment environment variables (duplicate)
- `stack.env.example` - Template for Portainer stack deployment (moved to portainer-stacks/)

### Why It Was Removed
1. **Not Referenced**: No Docker Compose files referenced `./env/local.env` or `./env/remote.env`
2. **Redundant**: The root `.env` file already serves the local development purpose
3. **Incorrect Location**: `stack.env` files belong in `portainer-stacks/` directory where they're actually used

### Current Environment File Structure
```
willow/
├── .env                                    # ✅ Used by docker-compose.yml (local dev)
├── .env.example                           # ✅ Template for .env
├── .env.bak                               # Backup of .env
├── portainer-stacks/
│   ├── stack.env                          # ✅ Used by portainer-stacks/docker-compose.yml
│   ├── stack.env.cloud                    # ✅ Cloud deployment variables
│   ├── stack.env.template                 # ✅ Template
│   └── stack.env.example.legacy           # Legacy reference from old ./env/ directory
└── docs/
    └── archived-configs/
        └── env-directory-backup-*.tar.gz  # Complete backup of removed ./env/ directory
```

### How Docker Compose Files Load Environment Variables

#### Local Development (`docker-compose.yml`)
```yaml
services:
  willowcms:
    env_file:
      - ./.env  # Loads from root directory
```

#### Portainer Deployment (`portainer-stacks/docker-compose.yml`)
```yaml
services:
  willowcms:
    env_file:
      - stack.env  # Loads from same directory as compose file
```

### Backup Location
A complete backup of the `./env/` directory was created:
- **Location**: `docs/archived-configs/env-directory-backup-YYYYMMDD_HHMMSS.tar.gz`
- **Contents**: All files from the removed `./env/` directory
- **Restore**: If needed, extract with: `tar -xzf env-directory-backup-*.tar.gz`

### Best Practices Going Forward
1. ✅ Use `.env` in root directory for local development with `docker-compose.yml`
2. ✅ Use `stack.env` in `portainer-stacks/` for Portainer deployments
3. ✅ Keep `.env` files in `.gitignore` (never commit secrets)
4. ✅ Maintain `.env.example` templates for documentation
5. ❌ Don't create separate `./env/` directories that aren't referenced by compose files
