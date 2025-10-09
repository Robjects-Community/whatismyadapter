#!/bin/bash

# SSH Agent Setup for WillowCMS Digital Ocean Deployment
# This script ensures your SSH key is loaded in the agent for password-free deployment

set -e

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

SSH_KEY="$HOME/.ssh/id_ed25519_digital_ocean_droplet"

echo -e "${BLUE}🔑 Setting up SSH Agent for WillowCMS Deployment${NC}"
echo "=================================================="

# Check if SSH agent is running
if [ -z "$SSH_AUTH_SOCK" ]; then
    echo -e "${YELLOW}Starting SSH agent...${NC}"
    eval "$(ssh-agent -s)"
else
    echo -e "${GREEN}✅ SSH agent is already running${NC}"
fi

# Check if key is already loaded
if ssh-add -l | grep -q "id_ed25519_digital_ocean_droplet"; then
    echo -e "${GREEN}✅ Digital Ocean SSH key is already loaded${NC}"
else
    echo -e "${YELLOW}Adding Digital Ocean SSH key to agent...${NC}"
    if [ -f "$SSH_KEY" ]; then
        ssh-add "$SSH_KEY"
        echo -e "${GREEN}✅ SSH key added successfully${NC}"
    else
        echo -e "${RED}❌ SSH key not found at $SSH_KEY${NC}"
        exit 1
    fi
fi

# Test connection
echo -e "${YELLOW}Testing connection to droplet...${NC}"
DROPLET_IP=$(doctl compute droplet get willowcms-prod --format PublicIPv4 --no-header 2>/dev/null || echo "")

if [ -n "$DROPLET_IP" ]; then
    if ssh -i "$SSH_KEY" -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@$DROPLET_IP "echo 'Connection successful'" 2>/dev/null; then
        echo -e "${GREEN}✅ SSH connection to droplet successful!${NC}"
    else
        echo -e "${YELLOW}⚠️  Could not connect to droplet (may be starting up)${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Droplet not found or doctl not configured${NC}"
fi

echo ""
echo -e "${BLUE}🚀 You can now run deployment scripts without password prompts!${NC}"
echo ""
echo "Commands you can use:"
echo -e "  ${GREEN}./deploy-final.sh${NC}      - Deploy WillowCMS"
echo -e "  ${GREEN}./manage-production.sh${NC} - Manage production services"
echo ""

# Create persistent SSH agent configuration
echo -e "${YELLOW}Setting up persistent SSH agent configuration...${NC}"

# Check if SSH agent configuration exists in shell profile
SHELL_CONFIG=""
if [ -f "$HOME/.zshrc" ]; then
    SHELL_CONFIG="$HOME/.zshrc"
elif [ -f "$HOME/.bashrc" ]; then
    SHELL_CONFIG="$HOME/.bashrc"
elif [ -f "$HOME/.bash_profile" ]; then
    SHELL_CONFIG="$HOME/.bash_profile"
fi

if [ -n "$SHELL_CONFIG" ]; then
    # Check if SSH agent config already exists
    if ! grep -q "SSH_AUTH_SOCK" "$SHELL_CONFIG" 2>/dev/null; then
        echo "" >> "$SHELL_CONFIG"
        echo "# SSH Agent for WillowCMS Digital Ocean Deployment" >> "$SHELL_CONFIG"
        echo "if [ -z \"\$SSH_AUTH_SOCK\" ]; then" >> "$SHELL_CONFIG"
        echo "    eval \"\$(ssh-agent -s)\" > /dev/null" >> "$SHELL_CONFIG"
        echo "fi" >> "$SHELL_CONFIG"
        echo "" >> "$SHELL_CONFIG"
        echo "# Auto-load Digital Ocean SSH key" >> "$SHELL_CONFIG"
        echo "if [ -f \"$SSH_KEY\" ] && ! ssh-add -l | grep -q \"id_ed25519_digital_ocean_droplet\" 2>/dev/null; then" >> "$SHELL_CONFIG"
        echo "    ssh-add \"$SSH_KEY\" 2>/dev/null || true" >> "$SHELL_CONFIG"
        echo "fi" >> "$SHELL_CONFIG"
        
        echo -e "${GREEN}✅ Added SSH agent configuration to $SHELL_CONFIG${NC}"
        echo -e "${YELLOW}Note: Restart your terminal or run 'source $SHELL_CONFIG' to apply changes${NC}"
    else
        echo -e "${GREEN}✅ SSH agent configuration already exists in $SHELL_CONFIG${NC}"
    fi
fi

echo ""
echo -e "${GREEN}🎉 SSH Agent setup complete!${NC}"