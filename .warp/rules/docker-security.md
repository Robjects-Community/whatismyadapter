# Docker Security Rule

## Description
Hide all network details, subnets, IP addresses, secrets, passwords, and vulnerability-related code from Docker Compose files by utilizing .env or stack.env files for environment variables to improve security and avoid hardcoding sensitive information.

## Security Requirements

### Environment Variable Usage
- Set up all `*.yml` Docker Compose files to integrate variables from `.env` or `stack.env` files
- Eliminate hardcoded passwords, secrets, and vulnerabilities
- Use variable substitution for all sensitive data

### Volume Configuration
- Implement volumes that support both Docker-managed volumes and host-mounted volumes
- Ensure maximum compatibility across different deployment environments
- Never hardcode absolute paths in compose files

## Implementation Examples

### Environment Variables
```yaml
# docker-compose.yml - GOOD
environment:
  MYSQL_DATABASE: ${DB_NAME}
  MYSQL_USER: ${DB_USER} 
  MYSQL_PASSWORD: ${DB_PASSWORD}
  MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}

# docker-compose.yml - BAD (avoid)
environment:
  MYSQL_DATABASE: cms
  MYSQL_USER: willowuser
  MYSQL_PASSWORD: hardcodedpassword123
```

### Volume Configuration
```yaml
# Flexible volume mapping - GOOD
volumes:
  - ${HOST_DATA_PATH:-./data}:/var/lib/mysql
  - ${HOST_CONFIG_PATH:-./config}:/etc/mysql/conf.d

# Hardcoded paths - BAD (avoid)
volumes:
  - /volume1/docker/willow/data:/var/lib/mysql
```

### Network Security
```yaml
# Use environment variables - GOOD
networks:
  default:
    name: ${NETWORK_NAME:-willow_network}
    
# Hardcoded network - BAD (avoid)
networks:
  default:
    name: willow_production_network_192_168_1_0
```

## Apply This Rule
- Always use environment variables for sensitive data
- Never commit hardcoded secrets or passwords
- Use `.env` files for local development
- Use `stack.env` or similar for production deployments
- Implement flexible volume mappings
- Hide network topology details