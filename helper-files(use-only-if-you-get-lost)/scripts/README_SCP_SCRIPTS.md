## SCP Transfer Scripts for Willow CMS

This directory contains backup scripts for transferring the Willow CMS development environment to a remote server.

## Scripts Available

### 1\. `scp_to_remote_backup.sh` (Comprehensive)

A full-featured script with error checking, validation, and automatic .env.example template creation.

**Features:**

*   ✅ Validates required files exist locally
*   ✅ Creates missing .env.example template automatically
*   ✅ Transfers all necessary files for ./run\_dev\_env.sh
*   ✅ Sets proper permissions on remote server
*   ✅ Verifies successful transfer
*   ✅ Provides post-transfer instructions
*   ✅ Color-coded output for better readability
*   ✅ Error handling and recovery

**Usage:**

```plaintext
./scp_to_remote_backup.sh user@remote_host /path/to/willow-app

# Example:
./scp_to_remote_backup.sh user@ip_address:/home/willowcms/willow-app
```

### 2\. `scp_to_remote_simple.sh` (Simple)

A basic one-liner script for quick transfers (original version).

**Usage:**

```plaintext
# Edit the script to update user@host and path, then run:
./scp_to_remote_simple.sh
```

## Files Transferred

Both scripts transfer the following essential files needed to run `./run_dev_env.sh`:

### Required Files:

*   `./run_dev_env.sh` - Main development environment script
*   `./docker-compose*.yml` - Docker Compose configurations
*   `./.env.example` - Environment variables template (created if missing)
*   `./docker/` - Complete Docker build context
*   `./cakephp/` - Complete CakePHP application

### Optional Files:

*   `./scripts/` - Additional utility scripts
*   `./.gitignore` - Git ignore patterns
*   `./README.md` - Project documentation
*   `./WARP.md` - Warp-specific documentation

## Post-Transfer Setup

After running either script, complete these steps on the remote server:

**Navigate to project directory:**

**Create environment files:**

**Edit configuration files:**

*   Update `.env` with your Docker and service configurations
*   Update `cakephp/config/.env` with database credentials and API keys

**Start the development environment:**

## Environment Variables Template

The comprehensive script automatically creates a `.env.example` template with:

*   Docker container configuration (UID/GID, ports)
*   MySQL database settings
*   Redis configuration
*   RabbitMQ settings
*   API key placeholders (OpenAI, etc.)
*   Service-specific configurations

## Prerequisites

### Local Machine:

*   SSH key-based authentication to remote server
*   All required files present in project directory

### Remote Server:

*   SSH access
*   Docker and Docker Compose installed
*   Sufficient disk space for application files
*   Network access for Docker image downloads

## Troubleshooting1

### Common Issues:

**SSH Connection Failed:**

*   Verify SSH key authentication
*   Check remote server accessibility
*   Ensure user has proper permissions

**Missing Files Locally:**

*   Run the comprehensive script which validates all required files
*   Check error output for specific missing files

**Permission Denied on Remote:**

*   Ensure remote user has write access to target directory
*   Check if remote path requires sudo access

**Transfer Verification Failed:**

*   Check network connectivity
*   Verify sufficient disk space on remote server
*   Retry transfer

## Security Notes

*   Scripts do not contain hardcoded IP addresses or credentials
*   Environment templates use placeholder values
*   SSH key-based authentication recommended
*   Update default passwords in production environments

## Integration with Willow CMS

These scripts are designed specifically for the Willow CMS project and its `./run_dev_env.sh` development environment script. They ensure all necessary Docker configurations, CakePHP application files, and dependencies are properly transferred for seamless remote deployment.

**Last Updated:** September 2025  
**Compatible With:** Willow CMS development environment

```plaintext
./run_dev_env.sh
```

```plaintext
cp .env.example .env
cp cakephp/config/.env.example cakephp/config/.env
```

```plaintext
cd /path/to/willow-app
```