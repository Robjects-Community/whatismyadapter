#!/bin/bash

# Add Redis password secret to GitHub repository
# This creates a simple Redis password for the production environment

echo "🔐 Adding REDIS_PASSWORD secret to GitHub repository..."

# Generate a secure Redis password
REDIS_PASSWORD=$(openssl rand -base64 32 | tr -d '=/+' | cut -c1-25)

echo "Generated Redis password: $REDIS_PASSWORD"

# Use GitHub CLI to set the secret
gh secret set REDIS_PASSWORD --body "$REDIS_PASSWORD" --repo "Robjects-Community/WhatIsMyAdaptor"

echo "✅ REDIS_PASSWORD secret added successfully!"
echo ""
echo "🔍 You can verify all secrets with:"
echo "gh secret list --repo Robjects-Community/WhatIsMyAdaptor"