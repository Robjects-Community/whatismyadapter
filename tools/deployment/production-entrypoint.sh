#!/bin/bash

# Production entrypoint script for WillowCMS
# This script initializes the CakePHP application and starts services

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Starting WillowCMS Production Environment${NC}"

# Function to wait for service
wait_for_service() {
    local host=$1
    local port=$2
    local service_name=$3
    
    echo -e "${YELLOW}Waiting for ${service_name} at ${host}:${port}...${NC}"
    while ! nc -z "$host" "$port"; do
        sleep 1
    done
    echo -e "${GREEN}✓ ${service_name} is ready${NC}"
}

# Wait for database to be ready
wait_for_service "$DB_HOST" "$DB_PORT" "MySQL Database"

# Wait for Redis to be ready
wait_for_service "$REDIS_HOST" "$REDIS_PORT" "Redis Cache"

# Set proper permissions
echo -e "${YELLOW}Setting up permissions...${NC}"
chown -R nobody:nobody /var/www/html/tmp /var/www/html/logs /var/www/html/webroot/files 2>/dev/null || true
chmod -R 755 /var/www/html/tmp /var/www/html/logs /var/www/html/webroot/files 2>/dev/null || true
echo -e "${GREEN}✓ Permissions configured${NC}"

# Install Composer dependencies if needed
if [ ! -d "/var/www/html/vendor" ] || [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo -e "${YELLOW}Installing Composer dependencies...${NC}"
    cd /var/www/html
    composer install --no-dev --optimize-autoloader --no-interaction
    echo -e "${GREEN}✓ Dependencies installed${NC}"
else
    echo -e "${GREEN}✓ Dependencies already installed${NC}"
fi

# Run database migrations
echo -e "${YELLOW}Running database migrations...${NC}"
cd /var/www/html
if php bin/cake migrations migrate --no-interaction 2>/dev/null; then
    echo -e "${GREEN}✓ Database migrations completed${NC}"
else
    echo -e "${YELLOW}⚠ Migrations failed or not needed${NC}"
fi

# Run database seeds if tables are empty
echo -e "${YELLOW}Checking for initial data...${NC}"
if php bin/cake migrations seed --no-interaction 2>/dev/null; then
    echo -e "${GREEN}✓ Database seeded with initial data${NC}"
else
    echo -e "${GREEN}✓ Database already contains data${NC}"
fi

# Clear and warm up caches
echo -e "${YELLOW}Warming up caches...${NC}"
php bin/cake cache clear_all 2>/dev/null || true
php bin/cake orm_cache clear 2>/dev/null || true
echo -e "${GREEN}✓ Caches warmed up${NC}"

# Create admin user if not exists
echo -e "${YELLOW}Creating admin user...${NC}"
if [ -n "$WILLOW_ADMIN_USERNAME" ] && [ -n "$WILLOW_ADMIN_PASSWORD" ] && [ -n "$WILLOW_ADMIN_EMAIL" ]; then
    php bin/cake users create_admin "$WILLOW_ADMIN_USERNAME" "$WILLOW_ADMIN_EMAIL" "$WILLOW_ADMIN_PASSWORD" 2>/dev/null || echo "Admin user may already exist"
    echo -e "${GREEN}✓ Admin user configured${NC}"
fi

# Final health check
echo -e "${YELLOW}Performing final health checks...${NC}"

# Test database connection
if php bin/cake migrations status >/dev/null 2>&1; then
    echo -e "${GREEN}✓ Database connection OK${NC}"
else
    echo -e "${RED}✗ Database connection failed${NC}"
fi

# Test Redis connection  
if php -r "
try {
    \$redis = new Redis();
    \$redis->connect('$REDIS_HOST', $REDIS_PORT);
    \$redis->ping();
    echo 'Redis OK\n';
} catch (Exception \$e) {
    echo 'Redis Error: ' . \$e->getMessage() . '\n';
}
" | grep -q "Redis OK"; then
    echo -e "${GREEN}✓ Redis connection OK${NC}"
else
    echo -e "${YELLOW}⚠ Redis connection issue${NC}"
fi

echo -e "${GREEN}🎉 WillowCMS initialization complete!${NC}"
echo -e "${BLUE}Starting web services...${NC}"

# Start supervisord (which runs nginx and php-fpm)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf