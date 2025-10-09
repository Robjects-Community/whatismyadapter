# WillowCMS Development Environment Setup Guide

## Quick Start

To set up the development environment, simply run:

```bash
./run_dev_env.sh
```

This script will:
- Create `.env` files from templates if they don't exist
- Set up proper UID/GID for Docker containers
- Start all required services
- Run database migrations
- Clear application cache

## Available Services

After setup, the following services will be available:

- **WillowCMS Application**: http://localhost:8080
- **Admin Interface**: http://localhost:8080/admin
- **PHPMyAdmin**: http://localhost:8082 (Database management)
- **Mailpit**: http://localhost:8025 (Email testing)
- **Redis Commander**: http://localhost:8084 (Redis management)

## Setup Script Options

The setup script supports several operation modes:

```bash
# Normal startup with prompts
./run_dev_env.sh

# Start with Jenkins and internationalization data
./run_dev_env.sh -j -i

# Rebuild containers without prompts
./run_dev_env.sh --rebuild --no-interactive

# Wipe data and restart with Jenkins
./run_dev_env.sh --wipe -j

# Just run migrations
./run_dev_env.sh --migrate
```

## Development Aliases

Source the `dev_aliases.txt` file to get useful development commands:

```bash
source dev_aliases.txt
```

Key aliases include:
- `wt` - Run tests (`./scripts/run_tests.sh`)
- `wdev` - Development environment setup (`./run_dev_env.sh`)
- `cake_shell` - Run CakePHP console commands
- `phpunit` - Run PHPUnit tests  
- `willowcms_shell` - Access container shell
- `docker_up/docker_down` - Manage containers

## Environment Configuration

- **Project root `.env`**: Docker Compose configuration
- **CakePHP `.env`**: Application configuration at `cakephp/config/.env`

Both files are created from `.env.example` templates during setup.

## Database

MySQL runs on port 3307 (mapped from container port 3306) with:
- Database: `willowcms_dev`
- User: `willowcms_user` 
- Password: `dev_password_123` (development only)

## Testing

Run tests using the PHPUnit alias or filter by component:

```bash
# Run all model tests
phpunit tests/TestCase/Model

# Run specific test
phpunit tests/TestCase/Model/Table/UsersTableTest.php

# Run with coverage
phpunit_cov
```

## Troubleshooting

- If containers fail to start, check logs: `docker compose logs -f`
- For permission issues, use the `change_ownership` function from dev_aliases.txt
- To rebuild completely: `./run_dev_env.sh --rebuild --no-interactive`

## File Structure

```
willow/
├── run_dev_env.sh             # Main development environment script
├── scripts/
│   └── run_tests.sh           # Test runner script
├── docker-compose.yml         # Docker services configuration
├── .env                       # Docker Compose environment variables
├── .env.example              # Template for Docker environment
├── dev_aliases.txt           # Development shell aliases
├── cakephp/
│   └── config/
│       ├── .env              # CakePHP application environment
│       └── .env.example      # Template for CakePHP environment
└── SETUP_GUIDE.md           # This file
```