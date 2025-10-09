#!/bin/bash

# Build WillowCMS image for Portainer deployment
# This script builds the Docker image that Portainer will use

set -e

echo "==> Building WillowCMS image for Portainer..."

# Navigate to project root
cd "$(dirname "$0")/.."

# Build the image
docker build \
  --build-arg UID=501 \
  --build-arg GID=20 \
  -t willowcms:portainer-test \
  -f infrastructure/docker/willowcms/Dockerfile \
  .

echo "==> Image built successfully!"
echo ""
echo "Image name: willowcms:portainer-test"
echo ""
echo "Now you can deploy this stack in Portainer using:"
echo "  - docker-compose-portainer.yml"
echo "  - stack-test.env for environment variables"
echo ""
echo "The image is now available locally for Portainer to use."
