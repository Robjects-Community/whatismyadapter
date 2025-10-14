# WillowCMS Backup Directory Structure

This document describes the organized backup directory structure for the WillowCMS project.

## Directory Layout

```
tools/backup/
├── code_backups/         # Application code backups
├── db_backups/           # Database backups
├── config_backups/       # Configuration file backups
└── backup.sh            # Main backup script
```

## Backup Naming Convention

All backups follow the format: `YYYYMMDD_NNN_description.tar.gz`

Examples:
- `20251014_001_code_backup.tar.gz`
- `20251014_001_database_backup.tar.gz`
- `20251014_001_config_backup.tar.gz`

## Backup Types

### Code Backups (`tools/backup/code_backups/`)
- Contains application source code
- CakePHP framework files
- Custom plugins and modules
- Web assets and templates

### Database Backups (`tools/backup/db_backups/`)
- MySQL database dumps
- Schema definitions
- Data exports
- Migration files

### Configuration Backups (`tools/backup/config_backups/`)
- Docker configuration files
- Environment settings
- Server configuration
- SSL certificates (encrypted)

## Integration

The backup system is compatible with:
- `manage-macos.sh` script
- `manage.sh` script
- Automated scheduling systems
- Manual backup procedures

## Security

- All backups include checksums for integrity verification
- Sensitive data is appropriately encrypted
- Access controls are enforced
- Backup retention policies are implemented