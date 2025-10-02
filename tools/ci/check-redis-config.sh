#!/usr/bin/env sh
# CI check for Redis configuration compliance
set -e

echo "🔍 Checking Redis configuration compliance..."

# Check that REDIS_TAG is set in .env
if ! grep -q "^REDIS_TAG=" .env; then
    echo "❌ REDIS_TAG not set in .env file"
    exit 1
fi

# Check that no service uses image: redis:latest
if grep -q "image:.*redis:latest" docker-compose.yml; then
    echo "❌ Found 'redis:latest' in docker-compose.yml - version must be pinned"
    exit 1
fi

# Check that Redis service builds from our Dockerfile
if ! grep -q "dockerfile:.*docker/redis/Dockerfile" docker-compose.yml; then
    echo "❌ Redis service must build from docker/redis/Dockerfile"
    exit 1
fi

# Check that Redis password is not hardcoded (should not contain 'requirepass')
if grep -q "^requirepass" docker/redis/redis.conf; then
    echo "❌ Found hardcoded 'requirepass' in redis.conf - should be set via bootguard.sh"
    exit 1
fi

# Verify bootguard script exists and is executable
if [ ! -x "tools/redis/bootguard.sh" ]; then
    echo "❌ Bootguard script missing or not executable"
    exit 1
fi

echo "✅ All Redis configuration checks passed!"
echo "💡 To upgrade Redis:"
echo "   1. Update REDIS_TAG in .env"
echo "   2. Run: docker compose build redis"
echo "   3. Run: docker compose up -d redis"
echo "   4. Monitor: docker compose logs -f redis"