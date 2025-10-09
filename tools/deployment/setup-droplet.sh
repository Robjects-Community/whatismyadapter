#!/bin/bash

# Setup and Harden DigitalOcean Droplet for WillowCMS
# This script implements security best practices for a production droplet

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${SCRIPT_DIR}/.env"

# Load environment variables
if [[ -f "$ENV_FILE" ]]; then
    source "$ENV_FILE"
else
    echo -e "${RED}Error: .env file not found at $ENV_FILE${NC}"
    echo -e "${YELLOW}Please copy .env.example to .env and configure your settings${NC}"
    exit 1
fi

# Validate required variables
if [[ -z "$DROPLET_IP" || -z "$DEPLOY_USER" ]]; then
    echo -e "${RED}Error: DROPLET_IP and DEPLOY_USER must be set in .env${NC}"
    exit 1
fi

echo -e "${BLUE}🔧 Setting up and hardening DigitalOcean Droplet${NC}"
echo -e "${BLUE}Target: ${DROPLET_IP}${NC}"
echo ""

# Function to execute commands on remote server as root
ssh_exec_root() {
    ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null \
        "root@${DROPLET_IP}" "$@"
}

# Function to execute commands on remote server as deploy user
ssh_exec_deploy() {
    ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null \
        "${DEPLOY_USER}@${DROPLET_IP}" "$@"
}

# Test root connection
echo -e "${YELLOW}Testing SSH connection...${NC}"
if ! ssh_exec_root "echo 'Root connection successful'"; then
    echo -e "${RED}Error: Cannot connect to droplet as root${NC}"
    exit 1
fi
echo -e "${GREEN}✓ SSH connection successful${NC}"

# Update system packages
echo -e "${YELLOW}Updating system packages...${NC}"
ssh_exec_root "
    apt-get update && apt-get upgrade -y
    apt-get install -y curl wget git ufw fail2ban unattended-upgrades htop
"
echo -e "${GREEN}✓ System packages updated${NC}"

# Create deploy user
echo -e "${YELLOW}Creating deploy user...${NC}"
ssh_exec_root "
    # Create user if it doesn't exist
    if ! id -u $DEPLOY_USER &>/dev/null; then
        adduser --disabled-password --gecos '' $DEPLOY_USER
        usermod -aG sudo $DEPLOY_USER
        echo '$DEPLOY_USER ALL=(ALL) NOPASSWD:ALL' > /etc/sudoers.d/$DEPLOY_USER
    fi
    
    # Create SSH directory for deploy user
    mkdir -p /home/$DEPLOY_USER/.ssh
    chmod 700 /home/$DEPLOY_USER/.ssh
    
    # Copy root's authorized_keys to deploy user
    cp /root/.ssh/authorized_keys /home/$DEPLOY_USER/.ssh/authorized_keys
    chmod 600 /home/$DEPLOY_USER/.ssh/authorized_keys
    chown -R $DEPLOY_USER:$DEPLOY_USER /home/$DEPLOY_USER/.ssh
"
echo -e "${GREEN}✓ Deploy user created and configured${NC}"

# Test deploy user connection
echo -e "${YELLOW}Testing deploy user connection...${NC}"
if ! ssh_exec_deploy "echo 'Deploy user connection successful'"; then
    echo -e "${RED}Error: Cannot connect as deploy user${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Deploy user connection successful${NC}"

# Configure SSH hardening
echo -e "${YELLOW}Configuring SSH security...${NC}"
ssh_exec_root "
    # Backup original SSH config
    cp /etc/ssh/sshd_config /etc/ssh/sshd_config.backup
    
    # Configure SSH security settings
    sed -i 's/#PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
    sed -i 's/#PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config
    sed -i 's/#PubkeyAuthentication yes/PubkeyAuthentication yes/' /etc/ssh/sshd_config
    sed -i 's/#AuthorizedKeysFile/AuthorizedKeysFile/' /etc/ssh/sshd_config
    
    # Add additional security settings
    echo '' >> /etc/ssh/sshd_config
    echo '# Security hardening' >> /etc/ssh/sshd_config
    echo 'Protocol 2' >> /etc/ssh/sshd_config
    echo 'MaxAuthTries 3' >> /etc/ssh/sshd_config
    echo 'MaxSessions 2' >> /etc/ssh/sshd_config
    echo 'ClientAliveInterval 300' >> /etc/ssh/sshd_config
    echo 'ClientAliveCountMax 2' >> /etc/ssh/sshd_config
    echo 'AllowUsers $DEPLOY_USER' >> /etc/ssh/sshd_config
    
    # Restart SSH service
    systemctl restart sshd
"
echo -e "${GREEN}✓ SSH security configured${NC}"

# Configure UFW firewall
echo -e "${YELLOW}Configuring firewall...${NC}"
ssh_exec_deploy "
    sudo ufw --force reset
    sudo ufw default deny incoming
    sudo ufw default allow outgoing
    
    # Allow SSH
    sudo ufw allow 22/tcp
    
    # Allow HTTP and HTTPS
    sudo ufw allow 80/tcp
    sudo ufw allow 443/tcp
    
    # Allow Docker Swarm ports (if needed later)
    # sudo ufw allow 2377/tcp
    # sudo ufw allow 7946/tcp
    # sudo ufw allow 4789/udp
    
    # Enable UFW
    sudo ufw --force enable
    
    # Show status
    sudo ufw status verbose
"
echo -e "${GREEN}✓ Firewall configured${NC}"

# Configure fail2ban
echo -e "${YELLOW}Configuring fail2ban...${NC}"
ssh_exec_deploy "
    sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
    
    # Configure fail2ban for SSH
    cat << 'EOF' | sudo tee /etc/fail2ban/jail.d/sshd.local
[sshd]
enabled = true
port = 22
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 3600
findtime = 600
EOF
    
    sudo systemctl enable fail2ban
    sudo systemctl restart fail2ban
"
echo -e "${GREEN}✓ Fail2ban configured${NC}"

# Configure automatic security updates
echo -e "${YELLOW}Configuring automatic security updates...${NC}"
ssh_exec_deploy "
    # Configure unattended-upgrades
    cat << 'EOF' | sudo tee /etc/apt/apt.conf.d/20auto-upgrades
APT::Periodic::Update-Package-Lists \"1\";
APT::Periodic::Unattended-Upgrade \"1\";
APT::Periodic::AutocleanInterval \"7\";
EOF

    # Configure which packages to auto-update
    sudo sed -i 's|//\s*\"\${distro_id}:\${distro_codename}-security\";|\"\${distro_id}:\${distro_codename}-security\";|g' /etc/apt/apt.conf.d/50unattended-upgrades
    
    sudo systemctl enable unattended-upgrades
    sudo systemctl start unattended-upgrades
"
echo -e "${GREEN}✓ Automatic security updates configured${NC}"

# Install and configure Docker
echo -e "${YELLOW}Installing Docker...${NC}"
ssh_exec_deploy "
    # Remove old versions
    sudo apt-get remove -y docker docker-engine docker.io containerd runc || true
    
    # Install Docker
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $DEPLOY_USER
    rm get-docker.sh
    
    # Install Docker Compose
    sudo curl -L \"https://github.com/docker/compose/releases/latest/download/docker-compose-\$(uname -s)-\$(uname -m)\" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
    
    # Configure Docker daemon
    sudo mkdir -p /etc/docker
    cat << 'EOF' | sudo tee /etc/docker/daemon.json
{
    \"log-driver\": \"json-file\",
    \"log-opts\": {
        \"max-size\": \"10m\",
        \"max-file\": \"3\"
    },
    \"live-restore\": true,
    \"userland-proxy\": false,
    \"no-new-privileges\": true
}
EOF
    
    sudo systemctl enable docker
    sudo systemctl restart docker
"
echo -e "${GREEN}✓ Docker installed and configured${NC}"

# Create application directories
echo -e "${YELLOW}Creating application directories...${NC}"
ssh_exec_deploy "
    sudo mkdir -p /opt/willow/{app,logs,config,backups/{db,files},ssl}
    sudo chown -R $DEPLOY_USER:$DEPLOY_USER /opt/willow
    chmod -R 755 /opt/willow
"
echo -e "${GREEN}✓ Application directories created${NC}"

# Configure log rotation
echo -e "${YELLOW}Configuring log rotation...${NC}"
ssh_exec_deploy "
    cat << 'EOF' | sudo tee /etc/logrotate.d/willow
/opt/willow/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    missingok
    notifempty
    create 644 $DEPLOY_USER $DEPLOY_USER
}
EOF
"
echo -e "${GREEN}✓ Log rotation configured${NC}"

# Install monitoring tools
echo -e "${YELLOW}Installing monitoring tools...${NC}"
ssh_exec_deploy "
    # Install htop, iotop, and other monitoring tools
    sudo apt-get install -y htop iotop nethogs ncdu tree
    
    # Install netdata for monitoring (optional)
    # bash <(curl -Ss https://my-netdata.io/kickstart.sh) --dont-wait
"
echo -e "${GREEN}✓ Monitoring tools installed${NC}"

# Configure timezone
echo -e "${YELLOW}Configuring timezone...${NC}"
ssh_exec_deploy "
    sudo timedatectl set-timezone America/Chicago
"
echo -e "${GREEN}✓ Timezone configured${NC}"

# Display system information
echo -e "${YELLOW}System information:${NC}"
ssh_exec_deploy "
    echo 'Hostname:' \$(hostname)
    echo 'OS:' \$(lsb_release -d | cut -f2)
    echo 'Kernel:' \$(uname -r)
    echo 'Docker:' \$(docker --version)
    echo 'Docker Compose:' \$(docker-compose --version)
    echo 'Disk usage:'
    df -h /
    echo 'Memory usage:'
    free -h
"

echo ""
echo -e "${GREEN}🎉 Droplet setup completed successfully!${NC}"
echo -e "${BLUE}Security features enabled:${NC}"
echo -e "  ✓ Root login disabled"
echo -e "  ✓ Password authentication disabled"
echo -e "  ✓ UFW firewall enabled"
echo -e "  ✓ Fail2ban configured"
echo -e "  ✓ Automatic security updates enabled"
echo -e "  ✓ Docker installed with security hardening"
echo -e "  ✓ Deploy user with sudo access created"
echo ""
echo -e "${YELLOW}Important notes:${NC}"
echo -e "  - You can now only SSH as the '${DEPLOY_USER}' user"
echo -e "  - Root login has been disabled for security"
echo -e "  - Use 'ssh ${DEPLOY_USER}@${DROPLET_IP}' to connect"
echo -e "  - Run your deployment script next: ./deploy-to-droplet.sh"
echo ""