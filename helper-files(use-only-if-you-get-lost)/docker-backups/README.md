# Docker Compose Backups

This directory contains organized backups of docker-compose.yml files from different stages of the WillowCMS project development.

## Directory Structure

### 📧 `email-configs/`
Contains docker-compose files related to email configuration changes:

- **`hardcoded-vars/`**: Versions where email settings were hardcoded in docker-compose.yml environment variables
- **`env-file-based/`**: Versions using env_file approach to load variables from ./cakephp/config/.env

### 📁 `historical/`
Contains older docker-compose file versions:

- **`original/`**: Early versions of docker-compose files
- **`port-configs/`**: Versions with port configuration changes  
- **`temp-configs/`**: Temporary configuration files used during development

### 🔄 `current-backup/`
Contains recent general backups not specifically tied to email configuration

## File Naming Convention

Files follow the pattern: `docker-compose-[purpose]-[date]_[time].yml`

Examples:
- `docker-compose-gmail-hardcoded-20250917_111647.yml`
- `docker-compose-env-file-gmail-20250917_161855.yml`

## Usage

To restore any backup:
1. Copy the desired backup file to the project root
2. Rename it to `docker-compose.yml`  
3. Restart containers: `docker compose down && docker compose up -d`

## Current Configuration

The active configuration uses:
- **env_file approach**: Variables loaded from `./cakephp/config/.env`
- **Gmail SMTP**: Configured for production email delivery
- **Minimal hardcoded overrides**: Only Docker networking settings in environment section