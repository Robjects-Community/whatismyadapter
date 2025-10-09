# Docker Compose Configuration Rule

## Description
Utilize the docker-compose.yml file to find which services are utilized to edit the MySQL database or view the front end that is portrayed through CakePHP 5.x

## Configuration
- **Path to docker compose**: `/Users/mikey/Docs/git-repo-loc/docker-hub/adaptercms-beta/willow/docker-compose.yml`
- **Main Services**:
  - `willowcms` - Web application service (CakePHP 5.x)
  - `mysql` - Database service
  - `redis` - Cache service
  - `mailpit` - Email testing service

## Usage
- Use this file to identify available services
- Reference service names for docker compose commands
- Understand service dependencies and networking

## Environment
- Services are configured to work with `.env` files
- Database connection through Docker networking
- Frontend accessible via configured ports

## Related Commands
```bash
# View available services
docker compose ps

# Access database
docker compose exec mysql mysql -u root -p cms

# Access web application
docker compose exec willowcms bash

# View logs
docker compose logs -f willowcms
```

## Apply This Rule
- Always reference docker-compose.yml for service information
- Use service names when connecting between containers
- Ensure environment variables are properly configured