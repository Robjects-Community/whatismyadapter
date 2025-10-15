# DigitalOcean Database SSL Setup

This document explains how to configure WillowCMS to connect to DigitalOcean's managed MySQL database using SSL certificates.

## Overview

The configuration includes:
- **Four separate datasources:**
  - `default` - Local MySQL (for development)
  - `test` - Local MySQL test database
  - `digitalocean` - DigitalOcean production database with SSL
  - `digitalocean_test` - DigitalOcean test database with SSL
- SSL-encrypted connections to DigitalOcean managed MySQL
- CA certificate verification for secure connections
- Hybrid setup: Local MySQL for development, DigitalOcean for production data

## Files and Configuration

### Docker Compose File
**File:** `docker-compose-app-platform-do.yml`

This is a specialized Docker Compose configuration for connecting to DigitalOcean's managed services:
- CA certificate is included in the app directory at `./app/config/certs/digitalocean-ca.crt`
- Configures environment variables for both production and test databases
- Includes both local MySQL (for default/test datasources) and DigitalOcean (for digitalocean/digitalocean_test datasources)
- Includes Mailpit for local email testing

### Database Configuration
**File:** `infrastructure/docker/willowcms/config/app/cms_app_local.php`

Updated to support SSL connections with:
- `PDO::MYSQL_ATTR_SSL_CA` - Path to CA certificate
- `PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT` - Server certificate verification
- Environment variable support for all database parameters

### SSL Certificate
**File:** `./app/config/certs/digitalocean-ca.crt`

Downloaded from DigitalOcean dashboard:
1. Go to Databases → Your MySQL cluster → Settings
2. Download the CA certificate
3. Save as `digitalocean-ca.crt` in `./app/config/certs/`

```bash
# Copy from Downloads to app directory
cp ~/Downloads/ca-certificate.crt ./app/config/certs/digitalocean-ca.crt
```

## Database Credentials

### Local Development Databases

#### Default Database (`default`)
- **Host:** `mysql` (local Docker container)
- **Port:** `3306`
- **Database:** `cms`
- **Username:** `cms_user`
- **Password:** Configured in `.env`

#### Test Database (`test`)
- **Host:** `mysql` (local Docker container)
- **Port:** `3306`
- **Database:** `cms_test`
- **Username:** `cms_user_test`
- **Password:** Configured in `.env`

### DigitalOcean Managed Databases

#### Production Database (`digitalocean`)
- **Host:** `private-cms-mysql-test-do-user-25344929-0.e.db.ondigitalocean.com`
- **Port:** `25060`
- **Database:** `cms`
- **Username:** `cms_user`
- **Password:** `AVNS_CEqpRAxK445cA0Pmeny` (configured in docker-compose)
- **SSL:** Required with CA certificate

#### Test Database (`digitalocean_test`)
- **Host:** `private-cms-mysql-test-do-user-25344929-0.e.db.ondigitalocean.com`
- **Port:** `25060`
- **Database:** `cms_test`
- **Username:** `cms_user_test`
- **Password:** `AVNS_8ngOz-btvTbekIM4NvO` (configured in docker-compose)
- **SSL:** Required with CA certificate

## Usage

### Starting the Environment

```bash
# Using the helper script (recommended)
./run-app-platform-do.sh up

# Or using docker compose directly
docker compose -f docker-compose-app-platform-do.yml up -d
```

### Testing Database Connectivity

```bash
# Test all databases (local + DigitalOcean)
./run-app-platform-do.sh test-db

# Test only local databases
./run-app-platform-do.sh test-local

# Test only DigitalOcean databases
./run-app-platform-do.sh test-do

# Or manually test from inside container using CakePHP ConnectionManager
docker compose -f docker-compose-app-platform-do.yml exec willowcms bash
php -r "
use Cake\Datasource\ConnectionManager;

// Test DigitalOcean connection
\$conn = ConnectionManager::get('digitalocean');
\$result = \$conn->execute('SELECT 1 as test')->fetchAll();
echo 'DigitalOcean connection successful!\n';

// Test local connection
\$conn = ConnectionManager::get('default');
\$result = \$conn->execute('SELECT 1 as test')->fetchAll();
echo 'Local connection successful!\n';
"
```

### Running Migrations

```bash
# Run migrations on DigitalOcean database
./run-app-platform-do.sh migrate
```

### Accessing Services

- **Application:** http://localhost:8080
- **Mailpit (Email testing):** http://localhost:8025

## Security Considerations

### Certificate Management
- The CA certificate is mounted read-only from the host
- Certificate verification is enabled by default
- Store certificates outside the application directory to avoid accidental commits

### Credential Management
- Database credentials are configured in the Docker Compose environment section
- For production, use Docker secrets or environment variables
- Never commit actual credentials to version control

### SSL Configuration
```php
'flags' => [
    PDO::MYSQL_ATTR_SSL_CA => env('DB_SSL_CA', null),
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => filter_var(env('DB_SSL_VERIFY', true), FILTER_VALIDATE_BOOLEAN),
],
```

## Troubleshooting

### Common Issues

#### Certificate Not Found
```
ERROR: CA certificate not found at /Users/mikey/Downloads/ca-certificate.crt
```
**Solution:** Download the CA certificate from DigitalOcean dashboard and place it in `~/Downloads/`

#### SSL Connection Failed
```
SQLSTATE[HY000] [2002] SSL connection error
```
**Solutions:**
1. Verify the CA certificate is valid and not expired
2. Check that the certificate path is mounted correctly in the container
3. Ensure SSL verification is properly configured

#### Connection Refused
```
SQLSTATE[HY000] [2002] Connection refused
```
**Solutions:**
1. Verify the database host and port are correct
2. Check that your IP is whitelisted in DigitalOcean dashboard
3. Confirm the database cluster is running

#### Authentication Failed
```
SQLSTATE[HY000] [1045] Access denied for user
```
**Solutions:**
1. Verify username and password are correct
2. Check that the user has permission to access the specific database
3. Ensure the credentials haven't been rotated

### Debugging Commands

```bash
# Check container logs
docker compose -f docker-compose-app-platform-do.yml logs willowcms

# Verify certificate mount
docker compose -f docker-compose-app-platform-do.yml exec willowcms ls -la /var/www/html/config/certs/

# Test SSL connection with mysql client
docker compose -f docker-compose-app-platform-do.yml exec willowcms mysql \
  --host=private-cms-mysql-test-do-user-25344929-0.e.db.ondigitalocean.com \
  --port=25060 \
  --user=cms_user \
  --password=AVNS_CEqpRAxK445cA0Pmeny \
  --database=cms \
  --ssl-ca=/var/www/html/config/certs/do-ca.crt \
  --ssl-verify-server-cert

# Check PHP PDO SSL support
docker compose -f docker-compose-app-platform-do.yml exec willowcms php -m | grep openssl
```

## Team Setup Instructions

For other team members to set up this configuration:

1. **Download CA Certificate:**
   - Log into DigitalOcean dashboard
   - Navigate to Databases → MySQL cluster → Settings
   - Download CA certificate to `~/Downloads/ca-certificate.crt`

2. **Clone Repository:**
   ```bash
   git clone <repository>
   cd willow
   ```

3. **Start Services:**
   ```bash
   ./run-app-platform-do.sh up
   ```

4. **Verify Setup:**
   ```bash
   ./run-app-platform-do.sh test-db
   ```

## Production Deployment

For production deployment:

1. **Use Environment Variables:**
   ```yaml
   environment:
     DB_HOST: ${DB_HOST}
     DB_PORT: ${DB_PORT}
     DB_USERNAME: ${DB_USERNAME}
     DB_PASSWORD: ${DB_PASSWORD}
     DB_SSL_CA: ${DB_SSL_CA}
   ```

2. **Secure Certificate Storage:**
   - Store CA certificate in a secure location
   - Use Docker secrets for certificate management
   - Consider certificate rotation procedures

3. **Monitor SSL Connections:**
   - Enable SSL connection logging
   - Monitor certificate expiration dates
   - Set up alerts for connection failures

## Updating Credentials

When DigitalOcean rotates credentials:

1. **Update Environment Variables:**
   - Modify the `docker-compose-app-platform-do.yml` file
   - Or use Docker secrets/environment variable overrides

2. **Restart Services:**
   ```bash
   ./run-app-platform-do.sh restart
   ```

3. **Verify Connectivity:**
   ```bash
   ./run-app-platform-do.sh test-db
   ```

## Certificate Rotation

When DigitalOcean updates the CA certificate:

1. **Download New Certificate:**
   - Get updated certificate from DigitalOcean dashboard
   - Replace `~/Downloads/ca-certificate.crt`

2. **Restart Container:**
   ```bash
   ./run-app-platform-do.sh down
   ./run-app-platform-do.sh up
   ```

3. **Test Connectivity:**
   ```bash
   ./run-app-platform-do.sh test-db
   ```