#!/bin/bash

# Quick Secrets Upload - Super Simple Version
# Usage: ./quick-secrets.sh [repository]

REPO=${1:-"Robjects-Community/WhatIsMyAdaptor"}
ENV_FILE=".env"

echo "🚀 Quick uploading secrets to $REPO"

# Simple one-liner approach
grep -E '^[A-Z_]+=.*' "$ENV_FILE" | while IFS='=' read -r key value; do
  # Clean up value (remove quotes)
  value=$(echo "$value" | sed 's/^"//;s/"$//' | sed "s/^'//;s/'$//")
  
  # Skip empty values
  [[ -z "$value" ]] && continue
  
  echo "📤 $key"
  gh secret set "$key" --body "$value" --repo "$REPO" >/dev/null 2>&1
done

echo "✅ Done! Check with: gh secret list -R $REPO"