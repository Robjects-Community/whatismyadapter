#!/bin/bash

echo "🔍 Verifying Docker Swarm Stack App Configuration"
echo "=================================================="
echo

# Check if stack is running
echo "📋 Step 1: Checking Docker Swarm Stack Status"
docker stack ls | grep willowcms-swarm-test && echo "✅ Stack is running" || echo "❌ Stack not found"
echo

# Check WillowCMS service status
echo "📋 Step 2: Checking WillowCMS Service Status"
docker service ls | grep willowcms-swarm-test_willowcms
echo

# Get a container name from the running service
CONTAINER=$(docker ps --filter "name=willowcms-swarm-test_willowcms" --format "{{.Names}}" | head -1)

if [ -n "$CONTAINER" ]; then
    echo "📋 Step 3: Verifying App Structure in Container: $CONTAINER"
    echo "-------------------------------------------------------------"
    
    echo "📁 Application Root Directory:"
    docker exec $CONTAINER ls -la /var/www/html/ | head -10
    echo
    
    echo "📁 CakePHP Source Directory:"
    docker exec $CONTAINER ls -la /var/www/html/src/ | head -5
    echo
    
    echo "📁 Configuration Directory:"
    docker exec $CONTAINER ls -la /var/www/html/config/ | head -5
    echo
    
    echo "📁 Templates Directory:"
    docker exec $CONTAINER ls -la /var/www/html/templates/ | head -5
    echo
    
    echo "📁 Webroot Directory:"
    docker exec $CONTAINER ls -la /var/www/html/webroot/ | head -5
    echo
    
    echo "📋 Step 4: Verifying App Configuration"
    echo "--------------------------------------"
    echo "🔧 App Local Configuration:"
    docker exec $CONTAINER head -10 /var/www/html/config/app_local.php
    echo
    
    echo "🔧 Composer Configuration:"
    docker exec $CONTAINER head -5 /var/www/html/composer.json
    echo
    
    echo "📋 Step 5: Verifying Application Functionality"
    echo "----------------------------------------------"
    echo "🌐 HTTP Response Test:"
    curl -s -o /dev/null -w "WillowCMS: %{http_code}\n" http://localhost:7770
    echo
    
    echo "🔌 Database Connectivity:"
    docker exec $CONTAINER ping -c 1 mysql > /dev/null 2>&1 && echo "✅ MySQL connection: OK" || echo "❌ MySQL connection: Failed"
    
    echo "🔌 Redis Connectivity:"
    docker exec $CONTAINER ping -c 1 redis > /dev/null 2>&1 && echo "✅ Redis connection: OK" || echo "❌ Redis connection: Failed"
    echo
    
    echo "📋 Step 6: Application File Integrity Check"
    echo "-------------------------------------------"
    echo "📊 Total files in application:"
    docker exec $CONTAINER find /var/www/html -type f | wc -l
    
    echo "📊 PHP files count:"
    docker exec $CONTAINER find /var/www/html -name "*.php" | wc -l
    
    echo "📊 JavaScript files count:"  
    docker exec $CONTAINER find /var/www/html -name "*.js" | wc -l
    
    echo "📊 CSS files count:"
    docker exec $CONTAINER find /var/www/html -name "*.css" | wc -l
    echo
    
else
    echo "❌ No running WillowCMS containers found"
fi

echo "✅ Verification Complete!"
echo "========================"
