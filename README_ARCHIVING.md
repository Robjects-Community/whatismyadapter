# WillowCMS Final Archive Documentation

## Purpose

This archiving system packages WillowCMS for distribution or backups without generated content, focusing on preserving **Option 1** (main utility scripts) and **Option 3** (complete project structure) while excluding regenerable dependencies and temporary files.

## Quick Commands

### Create Archive (Recommended - with secrets redacted)
```bash
bash scripts/archive/create_final_archive.sh --redact-env
```

### Alternative Commands
```bash
# Dry-run preview (no archive created)
bash scripts/archive/create_final_archive.sh --dry-run

# Default (includes actual .env file)
bash scripts/archive/create_final_archive.sh

# Skip environment file entirely  
bash scripts/archive/create_final_archive.sh --no-env

# Using the developer alias (if setup_dev_aliases.sh is sourced)
willow:archive --redact-env
```

## Archive Contents

### ✅ **Option 1 Scripts (Main Utilities)**
- `manage.sh` - Main project management script
- `setup_dev_aliases.sh` - Development aliases setup
- `quick_security_check.sh` - Security verification
- `run_dev_env.sh` - Development environment runner
- `refactor_helper_files.sh` - File refactoring utilities
- `reorganize_willow.sh` - Project reorganization
- `reorganize_willow_secure.sh` - Secure reorganization

### ✅ **Option 3 Complete Project**
- **Source Code**: Complete CakePHP 5.x application (`app/`)
- **Tool Modules**: Management and utility modules (`tool_modules/`)
- **Scripts**: All project scripts (`scripts/`)
- **CakePHP Plugins**: 
  - Frontend theme (`app/plugins/DefaultTheme/`)
  - Admin interface (`app/plugins/AdminTheme/`)
- **Documentation**: All markdown and text documentation
- **Docker Configuration**: `docker-compose.yml` and related configs
- **Environment Files**: Configuration templates and environment setup

### ❌ **Excluded (Regenerable Content)**
- `vendor/` directories (Composer dependencies)
- `node_modules/` (NPM dependencies)
- `logs/`, `tmp/`, `coverage/` (Generated runtime data)
- `.git/` and version control files
- Database data directories (`**/mysql/`, `**/redis/`, etc.)
- Cache and build directories (`webroot/cache/`, `webroot/build/`)

## Output Files

After running the archiving script, you'll find these files in `final_archives/`:

```
WillowCMS_final_Option1+3_YYYYMMDDTHHMMSSZ.tar.gz      # Main archive (336MB)
WillowCMS_final_Option1+3_YYYYMMDDTHHMMSSZ.tar.gz.sha256   # Archive checksum
WillowCMS_final_Option1+3_YYYYMMDDTHHMMSSZ.tar.gz.manifest.txt  # Content list
packaging_YYYYMMDDTHHMMSSZ.log                         # Packaging log
packaging_YYYYMMDDTHHMMSSZ.log.sha256                  # Log checksum
```

## Integrity Verification

### Verify Archive Integrity
```bash
cd final_archives
shasum -a 256 -c WillowCMS_final_Option1+3_*.tar.gz.sha256
shasum -a 256 -c packaging_*.log.sha256
```

Expected output:
```
WillowCMS_final_Option1+3_20250920T065624Z.tar.gz: OK
packaging_20250920T065624Z.log: OK
```

### Inspect Archive Contents
```bash
# View all Option 1 scripts in archive
grep -E "(manage\.sh|setup_dev_aliases\.sh|quick_security_check\.sh)" final_archives/WillowCMS_final_Option1+3_*.manifest.txt

# View key project components
grep -E "(tool_modules/|plugins/DefaultTheme/|docker-compose\.yml)" final_archives/WillowCMS_final_Option1+3_*.manifest.txt

# Verify exclusions worked (should return no results)
grep -E "(vendor/|node_modules/|\.git/)" final_archives/WillowCMS_final_Option1+3_*.manifest.txt
```

## Archive Extraction & Restoration

### Extract Archive
```bash
mkdir -p ~/willow_restored
cd ~/willow_restored
tar -xzf /path/to/WillowCMS_final_Option1+3_YYYYMMDDTHHMMSSZ.tar.gz
```

### Restore Dependencies (Required)
Since vendor directories are excluded, you must restore them:

```bash
cd ~/willow_restored

# Restore PHP/Composer dependencies
composer install

# If Node.js dependencies exist
npm ci  # or npm install

# Make scripts executable
chmod +x *.sh scripts/**/*.sh
```

## Project Structure Notes

### CakePHP 5.x Conventions
- **Configuration**: Environment files located at `app/config/.env`
- **Frontend Theme**: `app/plugins/DefaultTheme/` (public website)
- **Admin Interface**: `app/plugins/AdminTheme/` (backend management)

### Docker Services
The archive includes metadata about Docker Compose services used for:
- **MySQL Editor Access**: phpMyAdmin or similar database management
- **CakePHP Frontend**: Web application frontend
- **Service Discovery**: Based on `docker-compose.yml` configuration

## Security Considerations

### Environment Variable Handling
- **`--redact-env`**: Replaces sensitive values (PASSWORD, SECRET, TOKEN, etc.) with `CHANGE_ME`
- **`--no-env`**: Excludes environment files entirely from archive
- **Default**: Includes actual environment files (⚠️ contains secrets)

### Best Practices
- Always use `--redact-env` for archives leaving your local machine
- Store actual environment files separately and securely
- Use `.env` and `stack.env` files to avoid hardcoding secrets in `docker-compose.yml`

## Customization

### Modify Exclusions
Edit `scripts/archive/exclude.lst` to add or remove exclusion patterns:

```bash
# Add new exclusion patterns
echo "new_pattern_to_exclude/" >> scripts/archive/exclude.lst

# View current exclusions
cat scripts/archive/exclude.lst
```

### Archive Naming
Archives are automatically timestamped with UTC format: `YYYYMMDDTHHMMSSZ`

## Troubleshooting

### Archive Too Large
If archives exceed expected size (~336MB), check for included files that should be excluded:
```bash
# Check what's taking up space in the archive
tar -tvf final_archives/WillowCMS_final_Option1+3_*.tar.gz | sort -k5 -nr | head -20
```

### Missing Scripts
If Option 1 scripts are missing from the archive, verify they exist:
```bash
ls -la *.sh
```

### Permission Issues
Ensure scripts remain executable after extraction:
```bash
chmod +x *.sh scripts/**/*.sh tool_modules/*.sh
```

## Development Integration

### Setup Alias
```bash
source setup_dev_aliases.sh
willow:archive --redact-env  # Shortcut command
```

### CI/CD Integration
Add to GitHub Actions or similar CI system:
```yaml
- name: Create Release Archive
  run: bash scripts/archive/create_final_archive.sh --redact-env
- name: Upload Archive
  uses: actions/upload-artifact@v3
  with:
    name: willow-cms-archive
    path: final_archives/*.tar.gz*
```

---

**Created**: 2025-09-20  
**Archive Size**: ~336MB (compressed)  
**Verification**: SHA256 checksums provided  
**License**: MIT (as per project license)