# Docker Swarm Management Testing Runbook

This document provides a comprehensive testing plan to validate all aspects of the `manage_swarm.sh` Docker Swarm management system for WillowCMS.

## Prerequisites

1. **Docker Swarm initialized**:
   ```bash
   docker info --format '{{.Swarm.LocalNodeState}}'
   # Should return: active
   
   # If not active:
   docker swarm init
   ```

2. **Environment setup**:
   ```bash
   # Ensure script is executable
   chmod +x manage_swarm.sh
   
   # Load swarm aliases for convenience
   source dev_aliases_swarm.txt
   ```

## Phase 1: Core Functionality Tests

### 1.1 Basic Help and Status
```bash
# Test help system
./manage_swarm.sh --help
./manage_swarm.sh -h

# Test status before deployment
./manage_swarm.sh --status
sw_status
```

### 1.2 Debug and Dry-Run Modes
```bash
# Test debug mode
DEBUG=1 ./manage_swarm.sh --status
sw_debug --status

# Test dry-run mode  
./manage_swarm.sh --dry-run --deploy
sw_dry --deploy

# Test combined debug + dry-run
sw_debug_dry --deploy
```

### 1.3 Environment Loading
```bash
# Test environment variable handling
echo "STACK_NAME=test-stack" > .env.swarm.local
./manage_swarm.sh --status
rm .env.swarm.local

# Test stack name override
./manage_swarm.sh --stack-name custom-willow --status
```

## Phase 2: Stack Deployment & Management

### 2.1 Deploy Stack
```bash
# Deploy the stack
./manage_swarm.sh --deploy
sw_deploy

# Verify deployment
docker stack ls
docker stack services willow
./manage_swarm.sh --status
```

### 2.2 Service Operations
```bash
# View service logs
./manage_swarm.sh --logs willowcms
sw_logs_app

# Follow all service logs
sw_logs_mysql
sw_logs_mailpit

# Check service tasks
docker service ps willow_willowcms
```

### 2.3 Service Scaling & Updates
```bash
# Scale willowcms service
./manage_swarm.sh --scale willowcms 2
sw_scale willowcms 2

# Verify scaling
docker service ls | grep willow_willowcms

# Scale back to 1
sw_scale willowcms 1

# Rolling restart
./manage_swarm.sh --restart willowcms
sw_restart willowcms

# Test image update (dry-run first)
./manage_swarm.sh --dry-run --update-image willowcms adaptercms/willowcms:latest
```

## Phase 3: Interactive Menu System

### 3.1 Docker/Swarm Menu
```bash
# Test interactive docker menu
./manage_swarm.sh
# Choose: 1) Docker/Swarm
# Test each menu option:
# - Stack Status
# - Follow App Logs  
# - List Services
# - Back
```

### 3.2 Data Management Menu
```bash
# Test data operations
./manage_swarm.sh
# Choose: 2) Data

# Test DB Backup
# Choose: 1) DB Backup

# Verify backup file created
ls -la backups/

# Test log checksum generation
# Choose: 6) Gen Log Checksums

# Verify log checksums
# Choose: 7) Verify Log Checksums
```

### 3.3 i18n Menu
```bash
# Test i18n operations
./manage_swarm.sh
# Choose: 3) i18n

# Test extract
# Choose: 1) Extract

# Test translate (if API key available)
# Choose: 2) Translate (AI)
```

### 3.4 Assets Menu
```bash
# Test asset operations
./manage_swarm.sh  
# Choose: 4) Assets

# Test build
# Choose: 1) Build

# Test cache clear
# Choose: 3) Clear Cache
```

### 3.5 System Menu
```bash
# Test system operations
./manage_swarm.sh
# Choose: 5) System

# Test migrations
# Choose: 3) Migrate DB

# Test health check
# Choose: 7) Health Check

# Test shell access
# Choose: 6) Shell (willowcms)
# Exit shell: exit
```

## Phase 4: Command Line Interface Tests

### 4.1 Direct Commands
```bash
# Test direct database backup
./manage_swarm.sh --db-backup
sw_db_backup

# Test direct shell access
./manage_swarm.sh --shell willowcms
# OR
./manage_swarm.sh --exec willowcms bash

# Test direct command execution
./manage_swarm.sh --exec willowcms "bin/cake migrations status"
sw_cake migrations status

# Test direct migration
sw_migrate

# Test direct cache clear
sw_cache_clear

# Test direct composer install
sw_composer
```

### 4.2 Alias Functionality
```bash
# Test all main aliases
sw_help           # Show help
sw_env            # Show environment
sw_urls           # Show service URLs

# Test asset aliases
sw_assets_build
sw_assets_publish

# Test i18n aliases
sw_i18n_extract
# sw_i18n_translate  # Only if API key configured

# Test utility aliases
sw_health
sw_php --version
```

## Phase 5: Error Handling & Edge Cases

### 5.1 Service Unavailability
```bash
# Scale down a service and test exec
docker service scale willow_mysql=0

# Try database operations (should fail gracefully)
./manage_swarm.sh --db-backup

# Scale back up
docker service scale willow_mysql=1

# Wait for service to be ready
sleep 30
```

### 5.2 Invalid Input Handling
```bash
# Test invalid service names
./manage_swarm.sh --logs nonexistent-service

# Test invalid scaling
./manage_swarm.sh --scale nonexistent-service 2

# Test invalid exec
./manage_swarm.sh --exec nonexistent-service bash
```

### 5.3 Confirmation Prompts
```bash
# Test removal confirmation (say 'n' to cancel)
./manage_swarm.sh --remove

# Test interactive menu removals
./manage_swarm.sh
# Choose: 1) Docker/Swarm
# Choose: 2) Remove Stack (cancel with 'n')
```

## Phase 6: Data Integrity & Backup Tests

### 6.1 Database Operations
```bash
# Create test data
./manage_swarm.sh --exec willowcms "bin/cake migrations migrate"

# Backup database
./manage_swarm.sh --db-backup

# Create a small test dump file for restore testing
echo "SELECT 1;" > test_restore.sql

# Test restore (with small file)
# Interactive menu: Data -> DB Restore -> test_restore.sql

# Clean up test file
rm test_restore.sql
```

### 6.2 Log Checksums
```bash
# Ensure some log files exist
./manage_swarm.sh --exec willowcms "touch logs/test.log logs/app.log"

# Generate checksums via menu
./manage_swarm.sh
# Data -> Gen Log Checksums

# Verify checksums via menu
./manage_swarm.sh  
# Data -> Verify Log Checksums

# Test checksum via CLI
./manage_swarm.sh --exec willowcms "cat logs/checksums/latest.sha256"
```

### 6.3 File Backup Operations
```bash
# Test file backup
./manage_swarm.sh
# Data -> Files Backup

# Verify backup created
ls -la backups/files_*.tar.gz

# Test file restore (interactive)
# Data -> Files Restore -> [select backup file]
```

## Phase 7: Multi-Service Integration

### 7.1 Service Communication
```bash
# Test database connectivity from app
./manage_swarm.sh --exec willowcms "bin/cake migrations status"

# Test web service accessibility
curl -f http://localhost:7770 || echo "Web service not accessible"
curl -f http://localhost:7771 || echo "phpMyAdmin not accessible"
curl -f http://localhost:7773 || echo "Mailpit not accessible"
```

### 7.2 Network Connectivity
```bash
# Test internal service communication
./manage_swarm.sh --exec willowcms "ping -c 3 mysql"
./manage_swarm.sh --exec willowcms "ping -c 3 redis"
./manage_swarm.sh --exec willowcms "ping -c 3 mailpit"
```

## Phase 8: Performance & Resource Tests

### 8.1 Resource Usage
```bash
# Check resource consumption
docker stats --no-stream

# Check service resource allocation
docker service inspect willow_willowcms --format '{{json .Spec.TaskTemplate.Resources}}'
```

### 8.2 Concurrent Operations
```bash
# Test multiple concurrent log views (in separate terminals)
# Terminal 1: sw_logs_app
# Terminal 2: sw_logs_mysql  
# Terminal 3: ./manage_swarm.sh --logs mailpit

# Test concurrent exec operations
# Terminal 1: sw_shell
# Terminal 2: sw_cake --version
```

## Phase 9: Environment & Configuration Tests

### 9.1 Environment File Loading
```bash
# Test env file precedence
echo "TEST_VAR=local" > .env.swarm.local
echo "TEST_VAR=general" > .env.swarm
echo "TEST_VAR=fallback" > .env

./manage_swarm.sh --exec willowcms "echo \$TEST_VAR"
# Should show: local

# Clean up
rm .env.swarm.local .env.swarm .env
```

### 9.2 Stack Name Configuration
```bash
# Test custom stack name
STACK_NAME=test-willow ./manage_swarm.sh --status

# Test CLI override
./manage_swarm.sh --stack-name custom-stack --status
```

## Phase 10: Cleanup & Stack Removal

### 10.1 Service Cleanup
```bash
# Test stack removal
./manage_swarm.sh --remove
# Confirm with 'y'

# Verify removal
docker stack ls
docker service ls | grep willow || echo "Services successfully removed"
```

### 10.2 System Cleanup
```bash
# Optional: System cleanup
./manage_swarm.sh --deploy  # Redeploy for next tests

# Test system prune via menu
./manage_swarm.sh
# Docker/Swarm -> Prune (system/images/volumes)
# Be cautious - this removes unused resources
```

## Test Results Documentation

### Expected Outcomes

✅ **Core functionality**: Help, status, debug modes work correctly
✅ **Stack management**: Deploy, remove, status operations succeed  
✅ **Service operations**: Logs, scaling, updates function properly
✅ **Interactive menus**: All menu options work without errors
✅ **CLI commands**: Direct command execution works correctly
✅ **Error handling**: Graceful failures with informative messages
✅ **Data operations**: Backup, restore, checksums function correctly
✅ **Multi-service**: Service communication and networking work
✅ **Environment**: Configuration loading and precedence work
✅ **Cleanup**: Stack removal and cleanup operations succeed

### Common Issues & Solutions

❌ **"No running container found"**: Service not running on current node
   - Solution: Check `docker service ps STACK_SERVICE`
   - Solution: Scale service up: `docker service scale STACK_SERVICE=1`

❌ **Permission denied**: Script not executable
   - Solution: `chmod +x manage_swarm.sh`

❌ **Swarm not active**: Docker Swarm not initialized  
   - Solution: `docker swarm init`

❌ **Port conflicts**: Stack ports already in use
   - Solution: Modify `willow-swarm-stack.yml` ports
   - Solution: Stop conflicting services

❌ **Environment variables not loaded**: Env files not found
   - Solution: Check `.env.swarm.example` and create appropriate env files

### Performance Notes

- Stack deployment typically takes 30-60 seconds
- Service scaling operations complete within 10-15 seconds  
- Database backup time depends on data volume
- Container exec operations have ~1-2 second latency

### Security Considerations

- Database credentials should be in environment files, not hardcoded
- Log files may contain sensitive information
- Backup files should be secured appropriately
- Service ports should be firewalled in production

## Final Validation

Run this comprehensive test sequence to validate the complete system:

```bash
# Quick validation sequence
source dev_aliases_swarm.txt
sw_help
sw_deploy
sleep 30
sw_status  
sw_logs_app &
sleep 5
kill %1
sw_db_backup
sw_shell 
# (exit shell)
sw_health
./manage_swarm.sh --remove
```

If all tests pass, the Docker Swarm management system is ready for production use.
