# Backup Archival System

This document describes the backup archival features available in WillowCMS for managing project backups.

## Overview

The backup archival system helps keep your active backup folders clean by automatically moving older backups to an archive directory while keeping only the most recent N backups readily available.

## Features

- **Automatic Archival**: Keep only the N most recent backups in active folders
- **Archive Storage**: Old backups are moved to `./archives/` instead of being deleted
- **Statistics View**: See counts of active vs archived backups
- **Restore Capability**: Easily restore archived backups when needed
- **Safe Cleanup**: Delete archived backups with double confirmation
- **Multiple Backup Types**: Supports files, logs, and MySQL database backups

## Backup Types Managed

1. **project_files_backups** - Application file backups
2. **project_log_backups** - Log file backups  
3. **project_mysql_backups** - MySQL database dumps

## Access Methods

### Method 1: Interactive Menu (`./manage.sh`)

Run the management script and select from the **Backup Management** section:

```bash
./manage.sh
```

Menu options:
- **25) Archive Old Backups** - Move old backups to archives
- **26) View Backup Statistics** - Show active vs archived counts
- **27) Restore Archived Backup** - Restore a backup from archives
- **28) Clean Archived Backups** - Permanently delete archived backups

### Method 2: Standalone Script

For more advanced usage with command-line options:

```bash
./tools/backup/archive_old_backups.sh [OPTIONS]
```

**Options:**
- `-k, --keep NUM` - Number of recent backups to keep (default: 3)
- `-d, --dry-run` - Show what would be archived without moving files
- `-v, --verbose` - Enable verbose output
- `-c, --checksum` - Generate checksums for archived files
- `-h, --help` - Show help message

**Examples:**

```bash
# Archive backups, keeping 5 most recent
./tools/backup/archive_old_backups.sh --keep 5

# Dry run to see what would happen
./tools/backup/archive_old_backups.sh --dry-run

# Archive with checksums for verification
./tools/backup/archive_old_backups.sh --keep 3 --checksum

# Verbose output for debugging
./tools/backup/archive_old_backups.sh --verbose
```

## Configuration

### Retention Policy

**Default:** Keep 3 most recent backups per type

**To change via manage.sh module:**
Edit `./tool_modules/backup_management.sh`:
```bash
KEEP_BACKUPS_COUNT=3  # Change this value
```

**To change via standalone script:**
Use the `--keep` option when running the script

### Archive Location

**Default:** `./archives/` in the project root

This can be modified in both scripts by changing the `ARCHIVE_BASE_DIR` variable.

## Directory Structure

```
willow/
├── project_files_backups/          # Active file backups (N most recent)
├── project_log_backups/            # Active log backups (N most recent)
├── project_mysql_backups/          # Active DB backups (N most recent)
└── archives/
    ├── project_files_backups/      # Archived file backups
    ├── project_log_backups/        # Archived log backups
    └── project_mysql_backups/      # Archived DB backups
```

## Workflow Examples

### Daily Maintenance

1. Run `./manage.sh`
2. Select option **26** to view backup statistics
3. If needed, select option **25** to archive old backups
4. Review the output to confirm archival

### Restoring an Old Backup

1. Run `./manage.sh`
2. Select option **27** (Restore Archived Backup)
3. Choose the backup type (files, logs, or mysql)
4. Select the specific backup to restore
5. Confirm the restoration

### Cleaning Up Archives

1. Run `./manage.sh`
2. Select option **28** (Clean Archived Backups)
3. Choose specific type or all archives
4. Type "DELETE" to confirm (double confirmation required)

### Automation with Cron

Add to crontab to auto-archive weekly:

```bash
# Archive old backups every Sunday at 2 AM, keeping 5 recent
0 2 * * 0 cd /path/to/willow && ./tools/backup/archive_old_backups.sh --keep 5 >> ./tools/logs/archive_backups.log 2>&1
```

## Safety Features

### Archive Old Backups
- Creates archive directories automatically
- Preserves file timestamps and permissions
- Shows progress for each file moved
- Reports errors without stopping the process

### Restore Archived Backup
- Interactive file selection with size and date
- Shows destination before restoring
- Confirmation prompt before moving files
- Validates backup exists before restoration

### Clean Archived Backups
- Shows statistics before deletion
- Choice to clean specific type or all
- Double confirmation required
- Must type "DELETE" to proceed
- Cannot be undone - archives are permanently deleted

## Logs

The standalone script logs to:
```
./tools/logs/archive_backups.log
```

Log entries include:
- Timestamp
- Action performed
- Files archived/restored/deleted
- Any errors encountered

## Troubleshooting

### Archives not being created

Check permissions:
```bash
ls -ld ./archives
# Should be writable by your user
```

### Backups not found

Verify backup directories exist:
```bash
ls -ld ./project_*_backups
```

### Restore fails

Check that:
1. Archive file still exists
2. Destination directory is writable
3. Sufficient disk space available

## Integration with Existing Backups

The archival system integrates with existing backup operations:

- **Option 3** (Dump MySQL Database) → Creates files in `project_mysql_backups/`
- **Option 10** (Backup Files Directory) → Creates files in `project_files_backups/`
- **Option 23** (Backup Logs with Verification) → Creates files in `project_log_backups/`

After using these backup commands, you can use the archival system to manage old backups.

## Best Practices

1. **Regular Archival**: Archive backups weekly or monthly
2. **Monitor Statistics**: Check backup counts regularly (option 26)
3. **Verify Before Cleanup**: Always verify archives before cleaning
4. **Test Restores**: Periodically test restore functionality
5. **Off-site Copies**: Consider copying archives to external storage
6. **Document Retention**: Keep important backups archived longer

## Future Enhancements

Potential improvements for future versions:

- Compressed archives for space savings
- Age-based archival (e.g., archive backups older than 30 days)
- Integration with cloud storage (S3, Azure, etc.)
- Automated backup verification after archival
- Email notifications for archival operations
- Web UI for backup management

## Support

For issues or questions:
1. Check the log file: `./tools/logs/archive_backups.log`
2. Run with `--verbose` flag for detailed output
3. Use `--dry-run` to test without making changes
4. Review this documentation

---

**Last Updated:** 2025-10-07  
**Version:** 1.0
