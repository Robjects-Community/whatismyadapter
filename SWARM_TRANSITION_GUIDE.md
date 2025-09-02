# Docker Compose to Swarm Transition Guide

This guide helps teams understand when and how to switch between Docker Compose (development) and Docker Swarm (production) workflows for WillowCMS.

## When to Use Each Approach

### Docker Compose (Development) 
- **Local development** and debugging
- **Single-node deployments**
- **Quick iteration** and testing
- **IDE integration** and debugging
- **File bind mounts** for live code editing

### Docker Swarm (Production)
- **Production deployments**
- **Multi-node clusters**  
- **Zero-downtime deployments**
- **Auto-scaling** and load balancing
- **High availability** requirements

## Key Differences

| Feature | Docker Compose | Docker Swarm |
|---------|----------------|--------------|
| **Deployment** | `docker compose up -d` | `docker stack deploy -c stack.yml STACK_NAME` |
| **Services** | `service_name` | `stack_name_service_name` |
| **Scaling** | `docker compose scale svc=3` | `docker service scale stack_svc=3` |
| **Logs** | `docker compose logs svc` | `docker service logs stack_svc` |
| **Shell Access** | `docker compose exec svc bash` | Find container → `docker exec -it CID bash` |
| **Configuration** | `docker-compose.yml` | `willow-swarm-stack.yml` |
| **Networks** | Bridge networks | Overlay networks |
| **Volumes** | Local bind mounts | Named volumes / NFS |

## Side-by-Side Command Comparison

### Basic Operations

```bash
# Docker Compose (Development)
docker compose -f /path/to/docker-compose.yml up -d
docker compose -f /path/to/docker-compose.yml down
docker compose -f /path/to/docker-compose.yml ps
docker compose -f /path/to/docker-compose.yml logs willowcms

# Docker Swarm (Production)  
./manage_swarm.sh --deploy
./manage_swarm.sh --remove
./manage_swarm.sh --status
./manage_swarm.sh --logs willowcms
```

### Application Tasks

```bash
# Docker Compose
docker compose exec willowcms bin/cake migrations migrate
docker compose exec willowcms composer install
docker compose exec willowcms bin/cake cache clear_all
docker compose exec willowcms bash

# Docker Swarm
./manage_swarm.sh --exec willowcms "bin/cake migrations migrate" 
./manage_swarm.sh --exec willowcms "composer install"
./manage_swarm.sh --exec willowcms "bin/cake cache clear_all"
./manage_swarm.sh --shell willowcms
```

### Data Management

```bash
# Docker Compose (manual commands)
docker compose exec mysql mysqldump -u root -p willow | gzip > backup.sql.gz
gunzip -c backup.sql.gz | docker compose exec -T mysql mysql -u root -p willow

# Docker Swarm (automated via manage_swarm.sh)
./manage_swarm.sh --db-backup
./manage_swarm.sh --db-restore backup.sql.gz
```

## Migration Workflow

### Development → Production Migration

1. **Prepare environment files**:
   ```bash
   # Copy and customize for production
   cp .env.swarm.example .env.swarm
   # Edit database credentials, API keys, etc.
   ```

2. **Review stack file**:
   ```bash
   # Check production-specific settings
   cat willow-swarm-stack.yml
   # Verify ports, replicas, resources
   ```

3. **Initialize Swarm** (if needed):
   ```bash
   docker swarm init
   # On additional nodes:
   # docker swarm join --token TOKEN MANAGER_IP:2377
   ```

4. **Deploy to Swarm**:
   ```bash
   source dev_aliases_swarm.txt
   sw_deploy
   sw_status
   ```

5. **Verify services**:
   ```bash
   sw_health
   sw_logs_app
   curl http://localhost:7770  # Check web interface
   ```

### Production → Development Migration

1. **Export data** (if needed):
   ```bash
   ./manage_swarm.sh --db-backup
   ./manage_swarm.sh --files-backup  
   ```

2. **Stop Swarm stack**:
   ```bash
   ./manage_swarm.sh --remove
   ```

3. **Switch to Compose**:
   ```bash
   source setup_dev_aliases.sh
   docker_up
   ```

4. **Import data** (if needed):
   ```bash
   # Restore database via manage.sh or direct commands
   gunzip -c backups/db_*.sql.gz | docker compose exec -T mysql mysql -u root -p willow
   ```

## Environment Configuration

### Compose Environment Files
```bash
.env                    # General environment
.env.local             # Local overrides (gitignored)
config/app_local.php   # CakePHP local config
```

### Swarm Environment Files
```bash
.env.swarm             # Swarm-specific config
.env.swarm.local       # Local swarm overrides (gitignored)  
.env                   # Fallback general config
```

### Key Environment Variables

| Variable | Compose Default | Swarm Default | Purpose |
|----------|----------------|---------------|---------|
| `STACK_NAME` | N/A | `willow` | Swarm stack name |
| `MYSQL_DATABASE` | `willow` | `willow` | Database name |
| `MYSQL_USER` | `root` | `root` | Database user |
| `MYSQL_PASSWORD` | `password` | `password` | Database password |
| `BACKUP_DIR` | `./backups` | `./backups` | Backup directory |

## Port Mappings

### Development Ports (Compose)
- WillowCMS: http://localhost:8080
- phpMyAdmin: http://localhost:8082  
- Jenkins: http://localhost:8081
- Mailpit: http://localhost:8025
- Redis Commander: http://localhost:8084

### Production Ports (Swarm)
- WillowCMS: http://localhost:7770
- phpMyAdmin: http://localhost:7771
- Jenkins: http://localhost:7772  
- Mailpit: http://localhost:7773
- Redis Commander: http://localhost:7774

## Troubleshooting

### Common Issues

**Service not accessible in Swarm**:
```bash
# Check service status
docker service ls
docker service ps willow_servicename

# Check if running on current node
docker ps --filter "label=com.docker.swarm.service.name=willow_servicename"
```

**Container exec failures in Swarm**:
```bash
# Service may be running on different node
docker service ps willow_servicename

# Set appropriate Docker context:
docker context use remote-node
# OR scale service to current node
docker service update --constraint-add 'node.id==CURRENT_NODE_ID' willow_servicename
```

**Port conflicts**:
```bash
# Check what's using the port
lsof -i :7770
netstat -tulpn | grep :7770

# Stop conflicting services or modify ports in stack file
```

**Environment variables not loading**:
```bash
# Check environment file loading order
DEBUG=1 ./manage_swarm.sh --status

# Verify file contents and permissions
cat .env.swarm
ls -la .env*
```

### Best Practices

1. **Keep Compose canonical**: Continue using Docker Compose as the reference for service definitions
2. **Environment parity**: Ensure Swarm environment matches Compose behavior
3. **Data persistence**: Use named volumes in Swarm, bind mounts in Compose
4. **Resource limits**: Set appropriate memory/CPU limits in Swarm stack
5. **Health checks**: Define health checks for production reliability
6. **Secrets management**: Use Docker secrets for sensitive data in Swarm
7. **Monitoring**: Implement logging and monitoring for Swarm deployments
8. **Backup strategy**: Regular automated backups for production data

### Quick Reference Commands

```bash
# Load aliases
source dev_aliases_swarm.txt        # Swarm aliases
source setup_dev_aliases.sh         # Compose aliases

# Essential Swarm commands
sw_help                             # Show all aliases
sw_deploy && sleep 30 && sw_status  # Deploy and check status
sw_shell                            # Get application shell
sw_db_backup                        # Backup database
sw_logs_app                         # View application logs
sw_health                           # Health check
./manage_swarm.sh                   # Interactive menu

# Essential Compose commands  
docker_up                           # Start development environment
willowcms_shell                     # Get application shell
cake_migrate                        # Run database migrations
docker_logs                         # View logs
docker_down                         # Stop environment
```

This guide should help your team confidently switch between development and production environments while maintaining consistency and reliability across both workflows.
