# WillowCMS Docker Cleanup Script

Safe, interactive Docker cleanup tool with backup and rollback capabilities.

## Features

- ✅ **Dry-run by default** - No destructive actions until you're ready
- ✅ **Interactive confirmations** - Safe default of "No" for all prompts
- ✅ **Automatic backups** - Volumes backed up with SHA256 checksums before deletion
- ✅ **Rollback support** - Restore deleted volumes from backups
- ✅ **Smart protection** - Never removes running containers, volumes in use, or project images
- ✅ **Colorized output** - Easy-to-read color-coded messages
- ✅ **Comprehensive logging** - Every action logged with timestamps
- ✅ **Flexible filtering** - Clean specific resources or everything at once

## What Gets Cleaned Up?

The script can clean up:

1. **Build Cache** (~1.9 GB in your environment)
   - Docker builder cache that accumulates over time
   
2. **Unused Volumes** (~2.5 GB)
   - Volumes not attached to any containers
   - Especially Jenkins volumes taking up significant space
   
3. **Unused Images** (varies)
   - Images older than 30 days (configurable)
   - Not used by running containers
   - Not matching Willow project patterns
   
4. **Dangling Images**
   - Intermediate build layers with `<none>` tags
   
5. **Unused Networks**
   - Networks with no attached containers

## Requirements

- Docker installed and running
- Docker Compose v2+ (` docker compose` command)
- Internet access (to pull alpine image for backups)
- Bash 4.0 or later
- ~100MB free space for logs and backups

## Installation

The script is already installed at:
```bash
./tools/cleanup/docker-cleanup.sh
```

No additional setup required! The script is executable and ready to use.

### Optional: Configure .env

Copy and customize the configuration:
```bash
cp tools/cleanup/.env.example tools/cleanup/.env
# Edit tools/cleanup/.env with your preferences
```

## Usage

### Basic Command

```bash
./tools/cleanup/docker-cleanup.sh [OPTIONS]
```

### Command-Line Options

| Option | Description | Default |
|--------|-------------|---------|
| `--dry-run` | Simulate cleanup without making changes | `true` |
| `--no-dry-run` | Actually perform cleanup operations | - |
| `--verbose` | Show detailed command output | `false` |
| `--force` | Skip confirmation prompts | `false` |
| `--preserve-project` | Protect Willow project images | `true` |
| `--no-preserve-project` | Allow cleanup of project images | - |
| `--grace-days N` | Keep images newer than N days | `30` |
| `--compose-file PATH` | Path to docker-compose.yml | `./docker-compose.yml` |
| `--context NAME` | Docker context to use | (default) |
| `--only OPERATION` | Only perform specific operation | - |
| `--exclude OPERATION` | Exclude specific operations | - |
| `--rollback [VOLUME]` | Restore volume from backup | - |
| `-h, --help` | Show help message | - |
| `--version` | Show version | - |

### Operations

Use with `--only` or `--exclude`:
- `build-cache` - Docker build cache
- `volumes` - Unused Docker volumes
- `images` - Unused Docker images
- `dangling` - Dangling images (`<none>` tags)
- `networks` - Unused Docker networks

## Examples

### 1. Dry-Run (Safe Preview)

**See what would be cleaned without making any changes:**
```bash
./tools/cleanup/docker-cleanup.sh
```

This is the default mode - completely safe to run anytime!

### 2. Clean Build Cache Only

**Remove ~1.9GB of Docker build cache:**
```bash
./tools/cleanup/docker-cleanup.sh --no-dry-run --only build-cache
```

This is the **fastest and safest** way to free up space.

### 3. Clean Unused Volumes

**Backup and remove unused volumes (including Jenkins):**
```bash
./tools/cleanup/docker-cleanup.sh --no-dry-run --only volumes
```

Volumes are backed up to `./backups/docker-volumes-TIMESTAMP/` before deletion.

### 4. Clean Old Images

**Remove images older than 30 days:**
```bash
./tools/cleanup/docker-cleanup.sh --no-dry-run --only images
```

Willow project images are automatically protected.

### 5. Full Cleanup (Interactive)

**Clean everything with confirmations:**
```bash
./tools/cleanup/docker-cleanup.sh --no-dry-run
```

You'll be asked to confirm each cleanup operation.

### 6. Full Cleanup (Non-Interactive)

**Clean everything without prompts:**
```bash
./tools/cleanup/docker-cleanup.sh --no-dry-run --force
```

⚠️ **Use with caution!** This skips all confirmations.

### 7. Verbose Dry-Run

**See exactly what commands would run:**
```bash
./tools/cleanup/docker-cleanup.sh --verbose
```

Perfect for understanding what the script will do.

### 8. Rollback a Volume

**Restore a deleted volume from backup:**
```bash
# List available backups
./tools/cleanup/docker-cleanup.sh --rollback

# Restore specific volume
./tools/cleanup/docker-cleanup.sh --rollback jenkins_home
```

## Safety Features

### What's Protected?

The script **NEVER** removes:

1. **Running Containers** - Any container currently running
2. **Images Used by Running Containers** - Images in active use
3. **Volumes Attached to Running Containers** - Volumes currently mounted
4. **Willow Project Images** - Images defined in docker-compose.yml
5. **Pattern-Matched Images** - Images matching `willow*`, `willowcms*`, `adaptercms*`
6. **Extra Protected Resources** - Anything you specify in `.env`

### Backup Strategy

Before deleting any volume:
1. Creates timestamped backup directory: `./backups/docker-volumes-YYYYmmdd_HHMMSS/`
2. Exports volume contents to compressed tar: `VOLUME_NAME.tar.gz`
3. Generates SHA256 checksum: `VOLUME_NAME.tar.gz.sha256`
4. Writes metadata JSON with volume details
5. Only deletes if backup succeeds

### Default Confirmations

All confirmation prompts default to **No** (`[y/N]`), requiring explicit yes to proceed.

## Configuration

### Environment Variables

Configure via `./tools/cleanup/.env`:

```bash
# Docker Compose file path
WILLOW_COMPOSE_FILE="/path/to/docker-compose.yml"

# Image patterns to protect (comma-separated)
PROJECT_IMAGE_PATTERNS="willow,willowcms,adaptercms"

# Jenkins volume candidates
JENKINS_VOLUME_CANDIDATES="jenkins_home,portainer_jenkins_home,..."

# Directories
BACKUP_ROOT="./backups"
LOGS_DIR="./logs"

# Operation modes
DRY_RUN="true"
VERBOSE="false"
FORCE="false"
PRESERVE_PROJECT="true"
IMAGE_GRACE_DAYS="30"

# Extra protection (comma-separated)
EXTRA_PROTECTED_IMAGES="mysql:8.4.3,redis:7.2-alpine"
EXTRA_PROTECTED_VOLUMES="willow_mysql_data,willow_redis-data"
```

### Priority Order

Configuration is loaded in this order (later overrides earlier):

1. Script defaults
2. `./env` (project root)
3. `./tools/cleanup/.env`
4. Command-line flags

## Logs and Backups

### Log Files

Located in `./logs/`:
- `docker-cleanup-YYYYmmdd_HHMMSS.log` - Main log file
- `docker-cleanup-YYYYmmdd_HHMMSS.log.sha256` - Checksum
- `protected_*_TIMESTAMP.txt` - Lists of protected resources

### Backup Files

Located in `./backups/docker-volumes-YYYYmmdd_HHMMSS/`:
- `VOLUME_NAME.tar.gz` - Compressed volume backup
- `VOLUME_NAME.tar.gz.sha256` - Backup checksum
- `VOLUME_NAME.json` - Volume metadata

## Troubleshooting

### Permission Errors

If you encounter permission errors:
```bash
# Check Docker socket permissions
ls -la /var/run/docker.sock

# May need to run with appropriate permissions
# Or add user to docker group (not recommended for production)
```

### Date Parsing on macOS

The script automatically detects macOS and uses BSD date commands. If you see date errors:
```bash
# Verify date command works
date -v-30d "+%s"  # macOS
date -d "30 days ago" "+%s"  # Linux
```

### Images That Cannot Be Removed

Some images have dependencies. The script will:
1. Try to remove the image
2. Log a warning if it fails
3. Continue with other images

To force removal:
```bash
docker rmi --force IMAGE_ID
```

### Adjusting Grace Period

To clean more aggressively, reduce the grace period:
```bash
# Remove images older than 7 days
./tools/cleanup/docker-cleanup.sh --no-dry-run --grace-days 7
```

### Checking Protected Resources

Run with verbose to see what's protected:
```bash
./tools/cleanup/docker-cleanup.sh --verbose
```

## Recommended Workflows

### Weekly Maintenance

```bash
# 1. Preview what would be cleaned
./tools/cleanup/docker-cleanup.sh --verbose

# 2. Clean build cache (fast, safe)
./tools/cleanup/docker-cleanup.sh --no-dry-run --only build-cache

# 3. Review and clean old images
./tools/cleanup/docker-cleanup.sh --no-dry-run --only images
```

### Before Major Updates

```bash
# Full cleanup before updating Willow
./tools/cleanup/docker-cleanup.sh --no-dry-run --force --exclude volumes
```

### Emergency Space Recovery

```bash
# Aggressive cleanup (keeps protected resources)
./tools/cleanup/docker-cleanup.sh --no-dry-run --force --grace-days 7
```

## .gitignore Recommendations

Add to your `.gitignore`:
```
# Docker cleanup logs and backups
/logs/docker-cleanup-*.log
/logs/docker-cleanup-*.log.sha256
/logs/protected_*.txt
/backups/docker-volumes-*/
```

## Version History

### v1.0.0 (Current)
- Initial release
- Dry-run by default
- Interactive mode with confirmations
- Volume backup with SHA256 checksums
- Protected resource detection
- Colorized output
- Comprehensive logging
- macOS and Linux support

## Support

For issues or questions:
1. Check the log file: `./logs/docker-cleanup-YYYYmmdd_HHMMSS.log`
2. Run with `--verbose` to see detailed output
3. Review protected resources to understand what's being kept
4. Check Docker daemon status: `docker info`

## License

Part of the WillowCMS project.
