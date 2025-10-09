#!/bin/bash

# RabbitMQ Setup Script for WillowCMS
# This script sets up RabbitMQ integration for ImageAnalysisJob processing

set -e

echo "🐰 Setting up RabbitMQ for WillowCMS..."

# Function to check if a service is ready
check_service() {
    local service=$1
    local max_attempts=30
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if docker compose ps $service | grep -q "Up"; then
            echo "✅ $service is ready"
            return 0
        fi
        echo "⏳ Waiting for $service to be ready... ($((attempt + 1))/$max_attempts)"
        sleep 2
        attempt=$((attempt + 1))
    done
    
    echo "❌ $service failed to start within timeout"
    return 1
}

echo "1️⃣ Installing RabbitMQ PHP dependencies..."
# Check if willowcms service is running
if ! docker compose ps willowcms | grep -q "Up"; then
    echo "Starting willowcms service..."
    docker compose up -d willowcms
    check_service willowcms
fi

# Install the AMQP transport
docker compose exec -T willowcms composer require enqueue/amqp-bunny:^0.10.19

echo "2️⃣ Starting RabbitMQ service..."
docker compose up -d rabbitmq

echo "3️⃣ Waiting for RabbitMQ to be healthy..."
check_service rabbitmq

echo "4️⃣ Starting image analysis worker..."
docker compose up -d --build image-analysis-worker

echo "5️⃣ Verifying services..."
docker compose ps

echo "6️⃣ Testing RabbitMQ connectivity..."
echo "Checking RabbitMQ Management UI access..."
if curl -f -s http://localhost:15672 > /dev/null; then
    echo "✅ RabbitMQ Management UI is accessible at http://localhost:15672"
    echo "   Username: guest"
    echo "   Password: guest"
else
    echo "⚠️  RabbitMQ Management UI is not yet accessible. Wait a moment and try again."
fi

echo "7️⃣ Testing worker connection..."
echo "Checking if image-analysis-worker is consuming from the queue..."
docker compose logs --tail=10 image-analysis-worker

echo ""
echo "🎉 RabbitMQ setup complete!"
echo ""
echo "📋 Summary:"
echo "   - RabbitMQ running on port 5672 (AMQP)"
echo "   - Management UI available at http://localhost:15672"
echo "   - Image Analysis Worker consuming from 'image_analysis' queue"
echo "   - ProcessImageJob still uses Redis (default)"
echo "   - ImageAnalysisJob now uses RabbitMQ"
echo ""
echo "🔧 Useful commands:"
echo "   - View RabbitMQ logs: docker compose logs -f rabbitmq"
echo "   - View worker logs: docker compose logs -f image-analysis-worker"
echo "   - Restart worker: docker compose restart image-analysis-worker"
echo "   - Scale worker: docker compose up -d --scale image-analysis-worker=2"
echo ""
echo "🧪 To test the setup:"
echo "   1. Upload an image via your CMS admin interface"
echo "   2. Check RabbitMQ Management UI → Queues → image_analysis"
echo "   3. Monitor worker logs for job processing"