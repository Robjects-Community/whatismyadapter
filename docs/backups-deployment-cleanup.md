# Deployment Cleanup Backup Organization

This document describes the backup organization system for deployment cleanup backups in the Willow project.

## Overview

The deployment cleanup backup system consolidates all deployment-related backups into a well-organized directory structure with consistent naming and comprehensive metadata tracking.

## Directory Structure

```
.backups/
└── deployment-cleanup/
    ├── deployment-cleanup-backup-0001-20250920_164734/
    ├── deployment-cleanup-backup-0002-20250920_173433/
    ├── deployment-cleanup-backup-0003-20251001_161022/
    ├── ...
    ├── deployment-cleanup-backup-0026-20251002_072115/
    ├── index.json
    └── MIGRATION-YYYYMMDD_HHMMSS.log
```

### Location

All deployment cleanup backups are stored in:
```
.backups/deployment-cleanup/
```

This centralizes backup management and keeps the project root directory clean.

## Naming Scheme

### Pattern
```
deployment-cleanup-backup-NNNN-YYYYMMDD_HHMMSS
```

Where:
- **NNNN**: 4-digit, zero-padded sequential number (0001, 0002, 0003, ...)
- **YYYYMMDD_HHMMSS**: Timestamp in format Year-Month-Day_Hour-Minute-Second

### Examples
- `deployment-cleanup-backup-0001-20251001_161022`
- `deployment-cleanup-backup-0014-20251002_072115`

### Numbering Rules

1. **Sequential**: Numbers increment in chronological order based on original timestamps
2. **Zero-padded**: Always 4 digits (0001, 0002, etc.) for consistent sorting
3. **Chronological**: Earlier backups get lower numbers regardless of migration order
4. **Collision handling**: If duplicate names exist, `-dup1`, `-dup2`, etc. are appended

## Organization Tool

### Location
```bash
tools/backup/organize_deployment_backups.sh
```

### Usage

#### Dry Run (Preview)
```bash
# Preview what will be organized
./tools/backup/organize_deployment_backups.sh --dry-run

# With custom options
./tools/backup/organize_deployment_backups.sh --source . --dest .backups/deployment-cleanup --width 4 --dry-run
```

#### Apply Changes
```bash
# Actually perform the organization
./tools/backup/organize_deployment_backups.sh --apply
```

### Options

| Option | Default | Description |
|--------|---------|-------------|
| `--source` | `.` | Source directory to scan for backups |
| `--dest` | `.backups/deployment-cleanup` | Destination directory |
| `--width` | `4` | Number of digits for sequence numbers |
| `--dry-run` | Default | Preview actions without making changes |
| `--apply` | - | Actually perform the migration |

### What It Does

1. **Scans** for `deployment-cleanup-backup-*` directories in source
2. **Sorts** them chronologically by embedded timestamps or file modification time
3. **Assigns** sequential numbers in chronological order
4. **Moves** and renames directories to the destination with new naming scheme
5. **Generates** migration log and integrity metadata

## Metadata Files

### Migration Log
File: `MIGRATION-YYYYMMDD_HHMMSS.log`

Records the mapping of old paths to new paths:
```
# Deployment Cleanup Backup Migration Log
# Generated: Wed Oct  3 22:32:19 PDT 2025
# Source: .
# Destination: .backups/deployment-cleanup

./deployment-cleanup-backup-20250920_164734 -> .backups/deployment-cleanup/deployment-cleanup-backup-0001-20250920_164734
./deployment-cleanup-backup-20250920_173433 -> .backups/deployment-cleanup/deployment-cleanup-backup-0002-20250920_173433
...
```

### Index File
File: `index.json`

JSON array containing metadata for each backup:
```json
[
    {
        "name": "deployment-cleanup-backup-0001-20250920_164734",
        "number": "0001",
        "timestamp": "20250920_164734",
        "size_kb": 156,
        "checksum": "a1b2c3d4e5f6..."
    },
    {
        "name": "deployment-cleanup-backup-0002-20250920_173433",
        "number": "0002", 
        "timestamp": "20250920_173433",
        "size_kb": 203,
        "checksum": "b2c3d4e5f6a1..."
    }
]
```

#### Index Fields

| Field | Description |
|-------|-------------|
| `name` | Complete directory name |
| `number` | Sequential number (padded) |
| `timestamp` | Original timestamp from directory name |
| `size_kb` | Directory size in kilobytes |
| `checksum` | SHA-256 aggregate checksum of all files |

## Environment Configuration

### Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `DEPLOYMENT_BACKUP_DIR` | `.backups/deployment-cleanup` | Destination directory |
| `DEPLOYMENT_BACKUP_PREFIX` | `deployment-cleanup-backup` | Backup name prefix |
| `DEPLOYMENT_BACKUP_NUMBER_WIDTH` | `4` | Number of digits for sequence |

### Configuration Files

Add to `.env.example` (and optionally `.env`):
```bash
DEPLOYMENT_BACKUP_DIR=.backups/deployment-cleanup
DEPLOYMENT_BACKUP_PREFIX=deployment-cleanup-backup
DEPLOYMENT_BACKUP_NUMBER_WIDTH=4
```

## Verification and Integrity

### Quick Verification
```bash
# Check backup count
ls -1d .backups/deployment-cleanup/deployment-cleanup-backup-* | wc -l

# Verify index exists
test -s .backups/deployment-cleanup/index.json && echo "Index OK"

# Check sequencing
ls -1 .backups/deployment-cleanup/deployment-cleanup-backup-* | head -5
ls -1 .backups/deployment-cleanup/deployment-cleanup-backup-* | tail -5
```

### Checksum Verification
Each backup directory's checksum can be verified:
```bash
# Verify specific backup checksum
BACKUP_DIR=".backups/deployment-cleanup/deployment-cleanup-backup-0001-20250920_164734"
find "$BACKUP_DIR" -type f -print0 | sort -z | xargs -0 shasum -a 256 | shasum -a 256
```

## Backup Restoration

### List Available Backups
```bash
# List all backups chronologically 
ls -1 .backups/deployment-cleanup/deployment-cleanup-backup-*

# Find latest backup
ls -1 .backups/deployment-cleanup/deployment-cleanup-backup-* | tail -1

# Use index.json for metadata
jq '.[].name' .backups/deployment-cleanup/index.json
```

### Select and Restore
```bash
# Set backup to restore
BACKUP_NAME="deployment-cleanup-backup-0014-20251002_072115"
BACKUP_PATH=".backups/deployment-cleanup/$BACKUP_NAME"

# Verify backup exists
if [ -d "$BACKUP_PATH" ]; then
    echo "Restoring from: $BACKUP_PATH"
    # Perform restoration steps here...
else
    echo "Backup not found: $BACKUP_PATH"
fi
```

### Restoration Steps (Example)
```bash
# Extract timestamp for reference
TIMESTAMP=$(echo "$BACKUP_NAME" | sed -nE 's/.*([0-9]{8}_[0-9]{6}).*/\1/p')
echo "Backup timestamp: $TIMESTAMP"

# Restore configuration files
if [ -f "$BACKUP_PATH/root.env.backup" ]; then
    cp "$BACKUP_PATH/root.env.backup" .env
fi

if [ -f "$BACKUP_PATH/docker-compose.yml.backup" ]; then
    cp "$BACKUP_PATH/docker-compose.yml.backup" docker-compose.yml
fi

# Restore application environment
if [ -f "$BACKUP_PATH/app.env.backup" ]; then
    cp "$BACKUP_PATH/app.env.backup" app/.env
fi
```

## Future Backup Creation

When creating new deployment cleanup backups, they will automatically:

1. **Use the organized structure** (if backup tools are updated to use environment variables)
2. **Follow the numbering scheme** with the next sequential number
3. **Update the index.json** with new metadata
4. **Maintain chronological ordering**

## Troubleshooting

### Common Issues

**Issue**: Backup not found in expected location  
**Solution**: Check if backups are still in project root; run organizer tool

**Issue**: Numbering sequence gaps  
**Solution**: This is normal if backups were deleted; sequence continues from last number

**Issue**: Checksum mismatch  
**Solution**: Backup may be corrupted; compare with migration log to verify integrity

### Recovery

If the organization needs to be re-run:
```bash
# Dry run to preview
./tools/backup/organize_deployment_backups.sh --dry-run

# Apply if needed (will skip already organized backups)
./tools/backup/organize_deployment_backups.sh --apply
```

## Benefits

1. **Clean Root Directory**: No deployment backup clutter
2. **Consistent Naming**: Easy to identify and sort backups
3. **Integrity Tracking**: Checksums and metadata for verification
4. **Easy Restoration**: Clear numbering and timestamp information
5. **Migration History**: Complete audit trail of organization changes
6. **Scalable**: Supports unlimited backups with consistent numbering