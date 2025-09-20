# CakePHP Migration Schema Dump Management

This document explains how we handle CakePHP migration schema dump files in this project.

## Overview

CakePHP generates `schema-dump-*.lock` files to optimize migration performance by creating snapshots of the database schema. However, these files change frequently during development and can cause merge conflicts in version control.

## Our Approach

### Files Not Tracked by Git:
- `schema-dump-default.lock` - Auto-generated, not tracked
- `schema-dump-test.lock` - Auto-generated, not tracked (if you want to exclude it too)

### Files Tracked by Git:
- `schema-dump-default.lock.template` - Reference template created from production schema
- `SCHEMA_MANAGEMENT.md` - This documentation

## Workflow

### For Developers:

1. **Fresh Setup**: The lock files will be auto-generated when you run migrations
2. **After Major Schema Changes**: Update the template file:
   ```bash
   cp cakephp/config/Migrations/schema-dump-default.lock cakephp/config/Migrations/schema-dump-default.lock.template
   git add cakephp/config/Migrations/schema-dump-default.lock.template
   git commit -m "Update schema dump template after [describe changes]"
   ```

### For New Team Members:

The schema dump lock files will be automatically generated when running:
```bash
./run_dev_env.sh
# or manually:
docker compose exec willowcms /var/www/html/bin/cake migrations migrate
```

## Benefits

- ✅ No merge conflicts from auto-generated schema files
- ✅ Maintain reference of expected schema structure  
- ✅ Faster migration performance (CakePHP still generates lock files locally)
- ✅ Clear documentation of schema management approach

## Files in .gitignore

```
# CakePHP Migration Schema Dumps #
##################################
/cakephp/config/Migrations/schema-dump-default.lock
```

## When to Update Template

Update the template file when:
- Major database schema changes are made
- New tables are added
- Significant column changes occur
- After completing a major feature that modifies the database

This helps team members understand the expected database structure without tracking volatile auto-generated files.