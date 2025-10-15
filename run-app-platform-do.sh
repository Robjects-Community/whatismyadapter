#!/bin/bash

# WillowCMS DigitalOcean App Platform Docker Setup
# This script helps deploy WillowCMS using DigitalOcean managed MySQL database

set -e

COMPOSE_FILE="docker-compose-app-platform-do.yml"
CA_CERT_PATH="./app/config/certs/digitalocean-ca.crt"

echo "🚀 WillowCMS DigitalOcean App Platform Setup"
echo "============================================="

# Check if CA certificate exists
if [ ! -f "$CA_CERT_PATH" ]; then
    echo "❌ ERROR: CA certificate not found at $CA_CERT_PATH"
    echo "   Please download the ca-certificate.crt from DigitalOcean and place it at:"
    echo "   ./app/config/certs/digitalocean-ca.crt"
    echo "   "
    echo "   You can do this with:"
    echo "   cp ~/Downloads/ca-certificate.crt ./app/config/certs/digitalocean-ca.crt"
    exit 1
fi

echo "✅ CA certificate found: $CA_CERT_PATH"

# Check if docker-compose file exists
if [ ! -f "$COMPOSE_FILE" ]; then
    echo "❌ ERROR: Docker compose file not found: $COMPOSE_FILE"
    exit 1
fi

echo "✅ Docker compose file found: $COMPOSE_FILE"

# Function to show usage
show_usage() {
    echo ""
    echo "Usage: $0 [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  up           Start the services"
    echo "  down         Stop the services"
    echo "  build        Build the images"
    echo "  restart      Restart the services"
    echo "  logs         Show logs"
    echo "  shell        Open shell in willowcms container"
    echo "  test-db      Test all database connections"
    echo "  test-local   Test local database connections (default, test)"
    echo "  test-do      Test DigitalOcean database connections (digitalocean, digitalocean_test)"
    echo "  migrate      Run database migrations"
    echo ""
}

# Function to test database connectivity
test_db() {
    echo "🔗 Testing database connectivity..."
    
    echo "Testing local default database connection..."
    docker compose -f "$COMPOSE_FILE" exec willowcms php -r "
        use Cake\Datasource\ConnectionManager;
        try {
            \$connection = ConnectionManager::get('default');
            \$result = \$connection->execute('SELECT 1 as test')->fetchAll();
            echo '✅ Local default database connection successful' . PHP_EOL;
        } catch (Exception \$e) {
            echo '❌ Local default database connection failed: ' . \$e->getMessage() . PHP_EOL;
        }
    "
    
    echo "Testing local test database connection..."
    docker compose -f "$COMPOSE_FILE" exec willowcms php -r "
        use Cake\Datasource\ConnectionManager;
        try {
            \$connection = ConnectionManager::get('test');
            \$result = \$connection->execute('SELECT 1 as test')->fetchAll();
            echo '✅ Local test database connection successful' . PHP_EOL;
        } catch (Exception \$e) {
            echo '❌ Local test database connection failed: ' . \$e->getMessage() . PHP_EOL;
        }
    "
    
    echo "Testing DigitalOcean database connection..."
    docker compose -f "$COMPOSE_FILE" exec willowcms php -r "
        use Cake\Datasource\ConnectionManager;
        try {
            \$connection = ConnectionManager::get('digitalocean');
            \$result = \$connection->execute('SELECT 1 as test')->fetchAll();
            echo '✅ DigitalOcean database connection successful' . PHP_EOL;
        } catch (Exception \$e) {
            echo '❌ DigitalOcean database connection failed: ' . \$e->getMessage() . PHP_EOL;
        }
    "
    
    echo "Testing DigitalOcean test database connection..."
    docker compose -f "$COMPOSE_FILE" exec willowcms php -r "
        use Cake\Datasource\ConnectionManager;
        try {
            \$connection = ConnectionManager::get('digitalocean_test');
            \$result = \$connection->execute('SELECT 1 as test')->fetchAll();
            echo '✅ DigitalOcean test database connection successful' . PHP_EOL;
        } catch (Exception \$e) {
            echo '❌ DigitalOcean test database connection failed: ' . \$e->getMessage() . PHP_EOL;
        }
    "
}

# Function to test local databases only
test_local_db() {
    echo "🔗 Testing local database connectivity..."
    
    echo "Testing local default database connection..."
    docker compose -f "$COMPOSE_FILE" exec willowcms php -r "
        use Cake\Datasource\ConnectionManager;
        try {
            \$connection = ConnectionManager::get('default');
            \$result = \$connection->execute('SELECT 1 as test')->fetchAll();
            echo '✅ Local default database connection successful' . PHP_EOL;
        } catch (Exception \$e) {
            echo '❌ Local default database connection failed: ' . \$e->getMessage() . PHP_EOL;
        }
    "
    
    echo "Testing local test database connection..."
    docker compose -f "$COMPOSE_FILE" exec willowcms php -r "
        use Cake\Datasource\ConnectionManager;
        try {
            \$connection = ConnectionManager::get('test');
            \$result = \$connection->execute('SELECT 1 as test')->fetchAll();
            echo '✅ Local test database connection successful' . PHP_EOL;
        } catch (Exception \$e) {
            echo '❌ Local test database connection failed: ' . \$e->getMessage() . PHP_EOL;
        }
    "
}

# Function to test DigitalOcean databases only
test_do_db() {
    echo "🔗 Testing DigitalOcean database connectivity..."
    
    echo "Testing DigitalOcean database connection..."
    docker compose -f "$COMPOSE_FILE" exec willowcms php -r "
        use Cake\Datasource\ConnectionManager;
        try {
            \$connection = ConnectionManager::get('digitalocean');
            \$result = \$connection->execute('SELECT 1 as test')->fetchAll();
            echo '✅ DigitalOcean database connection successful' . PHP_EOL;
        } catch (Exception \$e) {
            echo '❌ DigitalOcean database connection failed: ' . \$e->getMessage() . PHP_EOL;
        }
    "
    
    echo "Testing DigitalOcean test database connection..."
    docker compose -f "$COMPOSE_FILE" exec willowcms php -r "
        use Cake\Datasource\ConnectionManager;
        try {
            \$connection = ConnectionManager::get('digitalocean_test');
            \$result = \$connection->execute('SELECT 1 as test')->fetchAll();
            echo '✅ DigitalOcean test database connection successful' . PHP_EOL;
        } catch (Exception \$e) {
            echo '❌ DigitalOcean test database connection failed: ' . \$e->getMessage() . PHP_EOL;
        }
    "
}

# Function to run migrations
run_migrations() {
    echo "🔄 Running database migrations..."
    docker compose -f "$COMPOSE_FILE" exec willowcms bin/cake migrations migrate
    echo "✅ Migrations completed"
}

# Parse command
case "${1:-}" in
    "up")
        echo "🚀 Starting services..."
        docker compose -f "$COMPOSE_FILE" up -d
        echo "✅ Services started"
        echo "📱 Application: http://localhost:8080"
        echo "📧 Mailpit: http://localhost:8025"
        ;;
    "down")
        echo "🛑 Stopping services..."
        docker compose -f "$COMPOSE_FILE" down
        echo "✅ Services stopped"
        ;;
    "build")
        echo "🔨 Building images..."
        docker compose -f "$COMPOSE_FILE" build
        echo "✅ Images built"
        ;;
    "restart")
        echo "🔄 Restarting services..."
        docker compose -f "$COMPOSE_FILE" restart
        echo "✅ Services restarted"
        ;;
    "logs")
        echo "📋 Showing logs..."
        docker compose -f "$COMPOSE_FILE" logs -f
        ;;
    "shell")
        echo "💻 Opening shell..."
        docker compose -f "$COMPOSE_FILE" exec willowcms bash
        ;;
    "test-db")
        test_db
        ;;
    "test-local")
        test_local_db
        ;;
    "test-do")
        test_do_db
        ;;
    "migrate")
        run_migrations
        ;;
    *)
        show_usage
        exit 1
        ;;
esac