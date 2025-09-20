# Corruption-Resistant SCP Backup Guide

This guide covers the `willow_scp_backup.sh` script that provides secure, corruption-resistant backup of your entire 10GB willow directory to a remote server.

## Features

- **Complete integrity verification** with SHA256 and MD5 checksums
- **Compression**: Prefers zstd for better compression/speed, falls back to gzip
- **All hidden files included** (`.env`, `.git/`, `.vscode/`, etc.)
- **Progress monitoring** with optional `pv` support
- **Connection resilience** with timeouts and retry logic
- **Secure by default** with host key verification and no hardcoded secrets
- **Comprehensive logging** with timestamped output

## Configuration with .env Files (Recommended)

The script automatically loads configuration from `.env` files. It searches for environment files in this order:

1. `./tools/scp_backup.env` (script-specific config)
2. `./tools/.env` (tools directory config)
3. `./.env` (project root config)
4. `./config/.env` (project config directory)

### Setup Instructions

1. **Copy the example file:**
```bash
cp tools/scp_backup.env.example tools/scp_backup.env
```

2. **Edit the configuration:**
```bash
# Edit tools/scp_backup.env with your values
REMOTE_USER=your_ssh_username
REMOTE_HOST=your.remote.server.com
REMOTE_PORT=22
REMOTE_PATH=/path/to/remote/backup/directory
SSH_KEY=/Users/mikey/.ssh/id_ed25519
WILLOW_DIR=/Volumes/1TB_DAVINCI/docker/willow
SCP_BW_LIMIT_KBIT_S=0
```

### Alternative: Environment Variables

If you prefer to use environment variables instead of .env files:

```bash
export REMOTE_USER=your_ssh_username
export REMOTE_HOST=your.remote.server.com
export REMOTE_PORT=22                                    # Optional, defaults to 22
export REMOTE_PATH=/path/to/remote/backup/directory
export SSH_KEY=$HOME/.ssh/id_ed25519                     # Optional, defaults to ~/.ssh/id_rsa
export WILLOW_DIR=/Volumes/1TB_DAVINCI/docker/willow    # Optional, auto-detected
export SCP_BW_LIMIT_KBIT_S=0                           # Optional bandwidth limit
```

## Pre-flight Setup

### 1. Ensure SSH Key Authentication
```bash
# Test SSH connection
ssh -i "$SSH_KEY" "$REMOTE_USER@$REMOTE_HOST" "echo connected"
```

### 2. Add Remote Host to known_hosts
```bash
# Add host key (verify fingerprint out-of-band first!)
ssh-keygen -F "$REMOTE_HOST" >/dev/null || ssh-keyscan -p "$REMOTE_PORT" "$REMOTE_HOST" >> "$HOME/.ssh/known_hosts"

# Verify host fingerprint
ssh-keygen -lf "$HOME/.ssh/known_hosts" | grep "$REMOTE_HOST"
```

## Usage

### Using .env File (Recommended)
```bash
# 1. Configure your settings (one time setup)
cp tools/scp_backup.env.example tools/scp_backup.env
# Edit tools/scp_backup.env with your actual values

# 2. Run the backup (loads config automatically from .env)
time ./tools/willow_scp_backup.sh
```

### Using Environment Variables (Alternative)
```bash
# Set required variables
export REMOTE_USER=alice
export REMOTE_HOST=backup.example.com
export REMOTE_PATH=/data/backups/willow

# Run the backup
time ./tools/willow_scp_backup.sh
```

### With Bandwidth Limiting
```bash
# Option 1: Set in .env file
echo "SCP_BW_LIMIT_KBIT_S=1000" >> tools/scp_backup.env
./tools/willow_scp_backup.sh

# Option 2: Set as environment variable
export SCP_BW_LIMIT_KBIT_S=1000
./tools/willow_scp_backup.sh
```

### Monitor Progress
- SCP shows per-file progress by default
- If `pv` is installed, tar shows streaming progress with ETA
- Install pv: `brew install pv` (macOS) or `apt install pv` (Ubuntu)

## Script Output

The script creates:
- `./tools/archives/willow_YYYYMMDD_HHMMSS.tar.zst` (or .gz)
- `./tools/archives/willow_YYYYMMDD_HHMMSS.tar.zst.sha256`
- `./tools/archives/willow_YYYYMMDD_HHMMSS.tar.zst.md5`
- `./tools/logs/willow_backup_YYYYMMDD_HHMMSS.log`

## Remote Verification Commands

### Linux Remote Host
```bash
cd "$REMOTE_PATH"
sha256sum -c willow_YYYYMMDD_HHMMSS.tar.zst.sha256
md5sum -c willow_YYYYMMDD_HHMMSS.tar.zst.md5
```

### macOS Remote Host
```bash
cd "$REMOTE_PATH"
shasum -a 256 -c willow_YYYYMMDD_HHMMSS.tar.zst.sha256

# MD5 verification for macOS
f="willow_YYYYMMDD_HHMMSS.tar.zst"
test "$(md5 -q "$f")" = "$(awk 'NR==1 {print $1}' "$f.md5")" && echo "MD5 OK" || echo "MD5 MISMATCH"
```

### Optional Archive Integrity Check
```bash
# Verify archive structure without extracting
tar -tf willow_YYYYMMDD_HHMMSS.tar.zst > /dev/null
```

## Error Handling

The script uses strict error handling:
- `set -Eeuo pipefail` - Fails fast on any error
- Comprehensive logging with timestamps
- Pre-flight SSH connectivity check
- Archive creation verification
- Dual checksum verification (SHA256 + MD5)

## Security Features

- **StrictHostKeyChecking=yes** - Prevents MITM attacks
- **BatchMode=yes** - No interactive prompts
- **Host key verification** - Required in known_hosts
- **No hardcoded secrets** - All sensitive data via environment
- **Connection timeouts** - Prevents hanging connections

## Housekeeping

### Clean Old Archives
```bash
# Keep only last 5 archives
ls -1t ./tools/archives/willow_*.tar.* | tail -n +6 | xargs -r rm -f
```

### View Recent Logs
```bash
# View latest backup log
tail -f ./tools/logs/willow_backup_*.log | tail -1
```

## Troubleshooting

### "ssh check failed"
- Verify SSH key exists and has correct permissions (600)
- Ensure remote host key is in known_hosts
- Test manual SSH connection

### "sha256 verification tools missing on remote"
Install missing tools on remote host:
- Ubuntu/Debian: `apt install coreutils`
- CentOS/RHEL: `yum install coreutils`
- macOS: Should have `shasum` and `md5` by default

### "Archive creation failed"
- Check disk space in `./tools/archives/`
- Verify source directory permissions
- Check if compression tools (zstd/gzip) are available

### Transfer Interrupted
- The script will fail safely on interruption
- Simply re-run - each backup gets a unique timestamp
- Use `SCP_BW_LIMIT_KBIT_S` for slower, more reliable transfers

## What Gets Backed Up

The script backs up your entire willow directory including:
- All source code and configuration files
- Hidden files: `.env`, `.env.bak`, `.gitignore`
- Hidden directories: `.git/`, `.vscode/`, `.devcontainer/`, `.backups/`
- Docker configurations and compose files
- Documentation and scripts
- All file permissions and timestamps

**Total size**: ~10GB compressed to ~2-4GB (depending on compression algorithm)