# WillowCMS Development Environment Setup

## Overview

The `run_dev_env.sh` script provides automated setup and management of the WillowCMS development environment using Docker Compose. It has been enhanced to work seamlessly on both macOS and Linux systems, with automatic directory structure detection and robust environment file handling.

## Key Features

### 🔧 Cross-Platform Compatibility
- **macOS & Linux Support**: Uses portable commands and fallbacks for maximum compatibility
- **Symlink Handling**: Safely handles `.env` symlinks on macOS (avoids BSD sed limitations)
- **Directory Detection**: Automatically detects and prefers `./app/` over `./cakephp/` directory structure

### 🗂️ Environment File Management
- **Symlink Strategy**: Uses `.env` → `env/local.env` symlink structure for organization
- **Safe Editing**: Resolves symlinks before editing to prevent macOS BSD sed issues
- **Docker Integration**: Automatically sets `DOCKER_UID` and `DOCKER_GID` for proper file permissions
- **Override Support**: Supports `COMPOSE_FILE` and `WILLOW_COMPOSE_FILE` environment variables

### 🏗️ Directory Structure Support
- **Automatic Detection**: Detects between `./app/` (preferred) and `./cakephp/` (legacy) structures
- **Seamless Transition**: Works during repository reorganization with both structures present
- **Consistent Behavior**: Same functionality regardless of directory structure

## Usage

### Basic Usage
```bash
# Normal startup (interactive prompts)
./run_dev_env.sh

# Non-interactive startup
./run_dev_env.sh --no-interactive

# Include Jenkins service
./run_dev_env.sh --jenkins

# Load internationalization data
./run_dev_env.sh --i18n
```

### Operations
```bash
# Wipe containers and volumes
./run_dev_env.sh --wipe

# Rebuild containers from scratch
./run_dev_env.sh --rebuild

# Restart containers
./run_dev_env.sh --restart

# Run database migrations only
./run_dev_env.sh --migrate

# Fresh development setup (recommended for clean environment)
./run_dev_env.sh --fresh-dev
```

### Advanced Options
```bash
# Force clean development setup (removes all deployment configs)
./run_dev_env.sh --force-clean-dev

# Skip deployment state cleanup checks
./run_dev_env.sh --skip-cleanup

# Combined example: Fresh setup with Jenkins and i18n, non-interactive
./run_dev_env.sh --fresh-dev --jenkins --i18n --no-interactive
```

## Environment Variables

### Docker Configuration
- `DOCKER_UID`: Automatically set to current user's UID (e.g., `501`)
- `DOCKER_GID`: Automatically set to current user's GID (e.g., `20`)
- `SKIP_UID_GID=1`: Skip setting Docker UID/GID (useful in containers/CI)

### Compose File Overrides
- `COMPOSE_FILE`: Override default docker-compose.yml path
- `WILLOW_COMPOSE_FILE`: Willow-specific compose file override

### Examples
```bash
# Use custom compose file
COMPOSE_FILE=docker-compose.prod.yml ./run_dev_env.sh

# Skip UID/GID setup (for CI/containers)
SKIP_UID_GID=1 ./run_dev_env.sh --no-interactive
```

## Directory Structure

The script automatically detects and works with both directory structures:

### New Structure (Preferred)
```
willow/
├── .env → env/local.env          # Symlink to actual environment file
├── env/
│   └── local.env                 # Real environment file
├── app/                          # Application code (NEW structure)
│   ├── config/
│   │   └── .env                 # App-specific environment
│   ├── logs/
│   ├── tmp/
│   └── webroot/
├── tools/
│   └── dev/
│       └── run_dev_env.sh       # Main script
└── run_dev_env.sh               # Wrapper script
```

### Legacy Structure (Supported)
```
willow/
├── .env → env/local.env          # Symlink to actual environment file
├── env/
│   └── local.env                 # Real environment file
├── cakephp/                      # Application code (LEGACY structure)
│   ├── config/
│   │   └── .env                 # App-specific environment
│   ├── logs/
│   ├── tmp/
│   └── webroot/
├── tools/
│   └── dev/
│       └── run_dev_env.sh       # Main script
└── run_dev_env.sh               # Wrapper script
```

## Troubleshooting

### macOS Issues

#### Permission Denied on .env
The script automatically handles this by:
1. Resolving the symlink target (`env/local.env`)
2. Editing the real file instead of the symlink
3. Using portable `sed -i.bak` syntax

#### Command Not Found: docker compose
Install Docker Desktop for Mac which includes Docker Compose V2, or install manually:
```bash
# Install via Homebrew
brew install docker-compose
```

### Linux Issues

#### Docker Permission Denied
Add your user to the docker group:
```bash
sudo usermod -aG docker $USER
# Log out and back in
```

#### Missing Docker Compose
Install Docker Compose V2:
```bash
# Ubuntu/Debian
sudo apt-get update && sudo apt-get install docker-compose-plugin

# CentOS/RHEL
sudo yum install docker-compose-plugin
```

### General Issues

#### Environment File Problems
Check the symlink structure:
```bash
# Verify symlink exists and points correctly
ls -la .env
# Should show: .env -> env/local.env

# Check target file exists and is writable
ls -la env/local.env
```

#### Directory Detection Issues
Ensure either `./app/` or `./cakephp/` directory exists:
```bash
# Check directory structure
ls -la app/ cakephp/ 2>/dev/null || echo "No app directories found"
```

#### Docker Service Not Running
Start Docker service:
```bash
# macOS: Start Docker Desktop application
# Linux systemd:
sudo systemctl start docker

# Linux init:
sudo service docker start
```

## Technical Details

### Symlink Resolution
The script uses a portable `realpath_portable()` function that works across platforms:
1. **Python 3** (preferred): Uses `os.path.realpath()`
2. **Perl** (fallback): Uses `Cwd::abs_path()`
3. **Basic shell** (minimal): Manual symlink resolution

### Safe Environment Editing
- Uses `sed -E -i.bak` for cross-platform compatibility
- Automatically removes `.bak` files after successful edits
- Operates on resolved file paths to avoid symlink issues

### Docker Integration
- Automatically detects current user UID/GID
- Sets appropriate Docker environment variables
- Supports override via `SKIP_UID_GID=1`

## Migration Notes

### From Old Script
The new script is backward compatible. Existing usage patterns continue to work:
- All command-line options preserved
- Same environment file locations supported
- Automatic migration from old to new directory structure

### Directory Structure Migration
When migrating from `./cakephp/` to `./app/`:
1. The script detects both directories
2. Prefers `./app/` when both exist
3. Logs which structure is being used
4. Handles environment files for both during transition

### Environment File Migration
The script automatically handles:
- Creating `env/local.env` if missing
- Setting up `.env` symlink if missing
- Migrating from direct `.env` file to symlink structure