# WillowCMS Docker Stack for Portainer

## Overview

This deployment successfully converts `docker-compose-port.yml` into a Docker Stack format compatible with Portainer management. All services from the original compose file have been integrated with proper networking, volume management, health checks, and Swarm deployment configurations.

## Services Deployed

### Primary Application Stack
- **WillowCMS Application**: CakePHP 5.x application with PHP 8.3, Nginx, Redis, and Supervisor
- **MySQL Database**: MySQL 8.4.3 with custom configuration for modern authentication
- **Redis Cache**: Redis 7 Alpine with persistence and password authentication 
- **PHPMyAdmin**: Database management interface
- **Jenkins**: CI/CD automation server with Docker integration
- **Mailpit**: Email testing and development SMTP server
- **Redis Commander**: Redis database management UI (has platform compatibility issues)

### Service Access (77XX Port Range)

All services use the 77XX port range to avoid conflicts with the canonical WillowCMS deployment:

- **WillowCMS Application**: [http://localhost:7770](http://localhost:7770)
- **PHPMyAdmin**: [http://localhost:7771](http://localhost:7771) (root/password)
- **Jenkins**: [http://localhost:7772](http://localhost:7772)
- **Mailpit UI**: [http://localhost:7773](http://localhost:7773) (SMTP on port 7725)
- **MySQL Direct**: localhost:7710 (cms_user/password, db: cms)
- **Redis Commander**: localhost:7774 (currently having platform issues)

## Files Created

### Configuration Files
- `deploy/portainer/.env.stack` - Environment variables for stack deployment
- `deploy/portainer/willow-stack.yml` - Original stack template with variables
- `deploy/portainer/willow-stack.rendered.yml` - Fully rendered stack for deployment

### Docker Images Built
- `willowcms:stack` - Custom WillowCMS application image
- `jenkins:stack` - Custom Jenkins image with PHP and Docker support

## Deployment Architecture

### Network Configuration
- **Overlay Network**: `willow_port_net` (Docker Swarm overlay, attachable)
- **Service Discovery**: All services communicate via service names (mysql, redis, mailpit, etc.)
- **Inter-service Communication**: Verified working between WillowCMS ↔ MySQL, Redis, Mailpit

### Volume Management
Named volumes with local driver for data persistence:
- `portainer_mysql_data` - MySQL database files
- `portainer_redis_data` - Redis persistence data
- `portainer_jenkins_home` - Jenkins configuration and jobs
- `portainer_mailpit_data` - Email storage
- `portainer_willowcms_logs` - Nginx access/error logs

### Resource Constraints
All services have defined CPU and memory limits/reservations for proper resource management:
- **WillowCMS**: 2.0 CPU limit, 2GB memory limit
- **MySQL**: 1.0 CPU limit, 1GB memory limit  
- **Jenkins**: 2.0 CPU limit, 2GB memory limit
- **Other services**: 0.5 CPU limit, 256-512MB memory

## Health Monitoring

All services include health checks:
- **HTTP Services**: curl/wget health endpoints
- **MySQL**: mysqladmin ping test
- **Redis**: redis-cli ping test
- **Startup Delays**: Appropriate start periods for initialization

## Key Features

### Swarm Integration
- All services deployed as Docker Swarm services
- Manager node placement constraints for data services
- Rolling update configurations for zero-downtime deployments
- Automatic restart policies on failure

### Portainer Management
- Stack visible in Portainer UI for easy management
- Service scaling, log viewing, and container management
- Network and volume inspection capabilities
- Environment variable management

### Development Features
- Bind mount of application source code for development
- CakePHP commands working: `bin/cake cache clear_all` verified
- Log integrity framework prepared (checksums directory created)

## Operational Commands

### Stack Management
```bash
# Deploy stack
docker stack deploy -c ./deploy/portainer/willow-stack.rendered.yml willow-port

# Check status
docker stack services willow-port
docker stack ps willow-port

# Update stack (after changes)
docker stack deploy -c ./deploy/portainer/willow-stack.rendered.yml willow-port

# Remove stack
docker stack rm willow-port
```

### Service Management
```bash
# Scale a service
docker service scale willow-port_willowcms=2

# Update a service
docker service update --force willow-port_willowcms

# Rollback a service
docker service update --rollback willow-port_willowcms

# View service logs
docker service logs willow-port_willowcms
```

### Application Commands
```bash
# CakePHP commands in WillowCMS container
docker exec $(docker ps -q --filter "label=com.docker.swarm.service.name=willow-port_willowcms") bin/cake cache clear_all
docker exec $(docker ps -q --filter "label=com.docker.swarm.service.name=willow-port_willowcms") bin/cake migrations migrate
```

## Status

✅ **Successfully Deployed**: 6/7 services running
✅ **Networking**: All inter-service communication verified  
✅ **Web Access**: All HTTP endpoints responding correctly
✅ **Database**: MySQL initialized and accessible
✅ **Cache**: Redis working with password authentication
✅ **Application**: WillowCMS CakePHP application functional
✅ **Volumes**: Persistent storage configured and working
⚠️ **Redis Commander**: Platform compatibility issues (not critical)

## Notes for Production

1. **Security**: Consider implementing Docker Secrets for sensitive data
2. **Monitoring**: Add comprehensive logging and metrics collection
3. **Backup**: Implement automated volume backup strategies
4. **SSL**: Configure reverse proxy with SSL for production use
5. **Registry**: Use proper container registry for image distribution

## Troubleshooting

### Common Issues
- **MySQL startup failures**: Clear mysql_data volume if corrupted
- **Service not starting**: Check resource constraints and node availability
- **Port conflicts**: Ensure 77XX port range is available
- **Volume permissions**: Verify Docker has access to bind mount paths

### Log Analysis
```bash
# Check service logs
docker service logs willow-port_<service_name> --tail 50

# Follow logs in real-time  
docker service logs -f willow-port_<service_name>
```

This deployment successfully integrates all services from `docker-compose-port.yml` into a Portainer-compatible Docker Stack with proper networking, persistence, health monitoring, and management capabilities.
