#!/bin/bash

# WillowCMS Docker Swarm Deployment Script
# Make sure to update passwords in willow-swarm-stack.yml before running!

echo "🐳 WillowCMS Docker Swarm Deployment"
echo "======================================"

# Step 1: Verify Docker Swarm is initialized
echo "📋 Step 1: Checking Docker Swarm status..."
docker info | grep -q "Swarm: active" && echo "✅ Docker Swarm is active" || {
    echo "❌ Docker Swarm not active. Initializing..."
    docker swarm init
}

# Step 2: Build required images (if not already built)
echo "📋 Step 2: Checking required images..."
if ! docker images | grep -q "willowcms.*portainer"; then
    echo "🔨 Building WillowCMS Portainer image..."
    docker build -f docker/willowcms/Dockerfile.portainer -t willowcms:portainer .
fi

# Step 3: Deploy the stack
echo "📋 Step 3: Deploying WillowCMS stack..."
docker stack deploy -c willow-swarm-stack.yml willowcms-prod

echo "⏳ Waiting for services to start..."
sleep 30

# Step 4: Check service status
echo "📋 Step 4: Checking service status..."
docker service ls

echo ""
echo "📋 Step 5: Checking service health..."
for service in willowcms mysql redis mailpit jenkins phpmyadmin; do
    replicas=$(docker service ls --filter "name=willowcms-prod_$service" --format "{{.Replicas}}")
    echo "  $service: $replicas"
done

echo ""
echo "🌐 Application URLs:"
echo "  WillowCMS:     http://localhost:7770"
echo "  PHPMyAdmin:    http://localhost:7771"
echo "  Jenkins:       http://localhost:7772"  
echo "  Mailpit UI:    http://localhost:7773"
echo "  Redis Cmd:     http://localhost:7774"
echo ""
echo "🔐 Database Ports:"
echo "  MySQL:         localhost:7710"
echo "  Redis:         localhost:7776"
echo "  SMTP:          localhost:7725"
echo ""
echo "✅ Deployment completed!"
echo "📝 Use 'docker service logs willowcms-prod_<service>' to view logs"
echo "🛠️  Use 'docker service ps willowcms-prod_<service>' to troubleshoot"
