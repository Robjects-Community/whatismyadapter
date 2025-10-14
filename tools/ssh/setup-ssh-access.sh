#!/usr/bin/env bash

# WillowCMS SSH Access Setup Script
# Sets up whatismyadapter user (UID 1034, GID 100) with key-only SSH access
# Supports: Digital Ocean droplet, local Docker services, long-term services

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$(dirname "$SCRIPT_DIR")")"
ENV_FILE="${PROJECT_ROOT}/.env"
STACK_ENV_FILE="${PROJECT_ROOT}/stack.env"

# User configuration from rules
SSH_USER="whatismyadapter"
SSH_UID="1034"
SSH_GID="100"
SSH_GROUP="users"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root (needed for user creation)
check_root() {
    if [ "$EUID" -ne 0 ]; then
        log_error "This script must be run as root for user creation and SSH configuration"
        echo "Usage: sudo $0"
        exit 1
    fi
}

# Create whatismyadapter user if it doesn't exist
create_user() {
    log "Checking if user '$SSH_USER' exists..."
    
    if id "$SSH_USER" &>/dev/null; then
        log_success "User '$SSH_USER' already exists"
        
        # Verify UID/GID
        current_uid=$(id -u "$SSH_USER")
        current_gid=$(id -g "$SSH_USER")
        
        if [ "$current_uid" != "$SSH_UID" ] || [ "$current_gid" != "$SSH_GID" ]; then
            log_warning "User exists but has wrong UID/GID (current: $current_uid:$current_gid, expected: $SSH_UID:$SSH_GID)"
            read -p "Fix UID/GID? [y/N] " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                usermod -u "$SSH_UID" "$SSH_USER"
                groupmod -g "$SSH_GID" "$SSH_GROUP" || true
                log_success "Updated UID/GID for user '$SSH_USER'"
            fi
        fi
    else
        log "Creating user '$SSH_USER' with UID $SSH_UID, GID $SSH_GID..."
        
        # Create group if it doesn't exist
        if ! getent group "$SSH_GROUP" &>/dev/null; then
            groupadd -g "$SSH_GID" "$SSH_GROUP"
            log_success "Created group '$SSH_GROUP' with GID $SSH_GID"
        fi
        
        # Create user
        useradd -u "$SSH_UID" -g "$SSH_GID" -G docker -m -s /bin/bash "$SSH_USER"
        log_success "Created user '$SSH_USER' with UID $SSH_UID, GID $SSH_GID"
        
        # Add to docker group for Docker service management
        usermod -aG docker "$SSH_USER"
        log_success "Added user '$SSH_USER' to docker group"
    fi
}

# Configure user permissions for Docker and WillowCMS
configure_user_permissions() {
    log "Configuring user permissions for Docker and WillowCMS..."
    
    # Ensure user can manage Docker
    if ! groups "$SSH_USER" | grep -q docker; then
        usermod -aG docker "$SSH_USER"
        log_success "Added user '$SSH_USER' to docker group"
    fi
    
    # Create necessary directories and set ownership
    local user_home="/home/$SSH_USER"
    
    # Set ownership of project directory
    chown -R "$SSH_UID:$SSH_GID" "$PROJECT_ROOT" 2>/dev/null || {
        log_warning "Could not change ownership of entire project root (some files may be protected)"
    }
    
    # Ensure critical directories have correct ownership
    local directories=(
        "$PROJECT_ROOT/tools"
        "$PROJECT_ROOT/docs"
        "$PROJECT_ROOT/app/logs"
        "$PROJECT_ROOT/app/tmp"
        "$PROJECT_ROOT/.backups"
    )
    
    for dir in "${directories[@]}"; do
        if [ -d "$dir" ]; then
            chown -R "$SSH_UID:$SSH_GID" "$dir"
            log_success "Set ownership of $dir to $SSH_USER"
        else
            mkdir -p "$dir"
            chown -R "$SSH_UID:$SSH_GID" "$dir"
            log_success "Created and set ownership of $dir to $SSH_USER"
        fi
    done
    
    # Create WillowCMS management sudoers rule
    cat > /etc/sudoers.d/willow-$SSH_USER << EOF
# WillowCMS management permissions for $SSH_USER
$SSH_USER ALL=(root) NOPASSWD: /usr/bin/systemctl restart ssh
$SSH_USER ALL=(root) NOPASSWD: /usr/bin/systemctl restart sshd  
$SSH_USER ALL=(root) NOPASSWD: /usr/bin/systemctl status ssh
$SSH_USER ALL=(root) NOPASSWD: /usr/bin/systemctl status sshd
$SSH_USER ALL=(root) NOPASSWD: /bin/chown -R $SSH_UID:$SSH_GID $PROJECT_ROOT/*
EOF
    
    chmod 0440 /etc/sudoers.d/willow-$SSH_USER
    log_success "Created sudoers rules for $SSH_USER"
}

# Generate SSH keys for multi-environment access
generate_ssh_keys() {
    local user_home="/home/$SSH_USER"
    local ssh_dir="$user_home/.ssh"
    
    log "Setting up SSH keys for multi-environment access..."
    
    # Create .ssh directory
    sudo -u "$SSH_USER" mkdir -p "$ssh_dir"
    chmod 700 "$ssh_dir"
    
    # Generate keys for different environments
    local key_configs=(
        "willow_droplet:Digital Ocean droplet access"
        "willow_local:Local Docker services"
        "willow_services:Long-term service management"
        "willow_main:Main deployment key"
    )
    
    for key_config in "${key_configs[@]}"; do
        IFS=':' read -r key_name key_desc <<< "$key_config"
        local key_path="$ssh_dir/$key_name"
        
        if [ ! -f "$key_path" ]; then
            log "Generating SSH key: $key_name ($key_desc)"
            sudo -u "$SSH_USER" ssh-keygen -t ed25519 -f "$key_path" -C "$SSH_USER@willowcms-$key_name" -N ""
            chmod 600 "$key_path"
            chmod 644 "$key_path.pub"
            log_success "Generated SSH key: $key_name"
        else
            log "SSH key already exists: $key_name"
        fi
    done
    
    # Set ownership
    chown -R "$SSH_UID:$SSH_GID" "$ssh_dir"
}

# Create SSH client configuration
create_ssh_config() {
    local user_home="/home/$SSH_USER"
    local ssh_dir="$user_home/.ssh"
    local config_file="$ssh_dir/config"
    
    log "Creating SSH client configuration..."
    
    # Create SSH config with environment variables support
    cat > "$config_file" << 'EOF'
# WillowCMS SSH Configuration
# Generated by setup-ssh-access.sh

# Global settings
Host *
    ServerAliveInterval 60
    ServerAliveCountMax 3
    StrictHostKeyChecking ask
    VerifyHostKeyDNS yes
    
# Digital Ocean Droplet (Production)
Host willow-droplet willow-production
    HostName ${DROPLET_HOST}
    User whatismyadapter
    Port ${DROPLET_SSH_PORT:-22}
    IdentityFile ~/.ssh/willow_droplet
    IdentitiesOnly yes
    PreferredAuthentications publickey
    ForwardAgent no
    
# Local Docker Host (Development)
Host willow-local localhost-docker
    HostName localhost
    User whatismyadapter  
    Port ${LOCAL_SSH_PORT:-22}
    IdentityFile ~/.ssh/willow_local
    IdentitiesOnly yes
    PreferredAuthentications publickey
    ForwardAgent yes
    
# Long-term Services Host
Host willow-services
    HostName ${SERVICES_HOST:-localhost}
    User whatismyadapter
    Port ${SERVICES_SSH_PORT:-22}
    IdentityFile ~/.ssh/willow_services
    IdentitiesOnly yes
    PreferredAuthentications publickey
    ForwardAgent no
    
# Main deployment host (configurable)
Host willow-main
    HostName ${MAIN_HOST}
    User whatismyadapter
    Port ${MAIN_SSH_PORT:-22}
    IdentityFile ~/.ssh/willow_main
    IdentitiesOnly yes
    PreferredAuthentications publickey
    ForwardAgent no
EOF

    chmod 600 "$config_file"
    chown "$SSH_UID:$SSH_GID" "$config_file"
    
    log_success "Created SSH client configuration"
}

# Update environment files with SSH configuration
update_env_files() {
    log "Updating environment files with SSH configuration..."
    
    # Add SSH configuration to main .env file
    if [ ! -f "$ENV_FILE" ]; then
        touch "$ENV_FILE"
    fi
    
    # SSH Configuration for .env
    cat >> "$ENV_FILE" << EOF

# SSH Configuration (added by setup-ssh-access.sh)
SSH_USER=whatismyadapter
SSH_UID=1034
SSH_GID=100

# SSH Hosts (configure these values)
DROPLET_HOST=your-droplet-ip
DROPLET_SSH_PORT=22
LOCAL_SSH_PORT=22
SERVICES_HOST=localhost
SERVICES_SSH_PORT=22
MAIN_HOST=your-main-host
MAIN_SSH_PORT=22

# SSH Key Paths
SSH_KEY_DROPLET=/home/whatismyadapter/.ssh/willow_droplet
SSH_KEY_LOCAL=/home/whatismyadapter/.ssh/willow_local
SSH_KEY_SERVICES=/home/whatismyadapter/.ssh/willow_services
SSH_KEY_MAIN=/home/whatismyadapter/.ssh/willow_main
EOF

    # Create stack.env for Portainer deployment
    if [ ! -f "$STACK_ENV_FILE" ]; then
        cp "$ENV_FILE" "$STACK_ENV_FILE"
    else
        # Append SSH config to existing stack.env
        grep -v "^SSH_\|^DROPLET_\|^LOCAL_\|^SERVICES_\|^MAIN_" "$STACK_ENV_FILE" > "$STACK_ENV_FILE.tmp" || true
        cat "$STACK_ENV_FILE.tmp" >> "$STACK_ENV_FILE.new"
        cat >> "$STACK_ENV_FILE.new" << EOF

# SSH Configuration (added by setup-ssh-access.sh)
SSH_USER=whatismyadapter
SSH_UID=1034
SSH_GID=100
DROPLET_HOST=
DROPLET_SSH_PORT=22
LOCAL_SSH_PORT=22
SERVICES_HOST=localhost
SERVICES_SSH_PORT=22
MAIN_HOST=
MAIN_SSH_PORT=22
EOF
        mv "$STACK_ENV_FILE.new" "$STACK_ENV_FILE"
        rm -f "$STACK_ENV_FILE.tmp"
    fi
    
    # Set secure permissions
    chmod 600 "$ENV_FILE" "$STACK_ENV_FILE"
    chown "$SSH_UID:$SSH_GID" "$ENV_FILE" "$STACK_ENV_FILE"
    
    log_success "Updated environment files with SSH configuration"
}

# Configure SSH server for key-only access
configure_ssh_server() {
    log "Configuring SSH server for key-only access..."
    
    # Backup existing sshd_config
    cp /etc/ssh/sshd_config /etc/ssh/sshd_config.backup.$(date +%Y%m%d_%H%M%S)
    
    # Create SSH configuration
    cat > /etc/ssh/sshd_config.d/willow-security.conf << EOF
# WillowCMS SSH Security Configuration
# Generated by setup-ssh-access.sh

# Enable public key authentication
PubkeyAuthentication yes
AuthorizedKeysFile %h/.ssh/authorized_keys

# Disable password authentication
PasswordAuthentication no
ChallengeResponseAuthentication no
UsePAM no

# Security settings
PermitRootLogin no
MaxAuthTries 3
LoginGraceTime 30
ClientAliveInterval 300
ClientAliveCountMax 2

# Disable unnecessary features
X11Forwarding no
PermitTunnel no
AllowTcpForwarding local
GatewayPorts no

# Specify allowed users
AllowUsers whatismyadapter
EOF

    log_success "Created SSH server security configuration"
}

# Create backup and documentation
create_documentation() {
    log "Creating backup and documentation..."
    
    local docs_dir="$PROJECT_ROOT/docs/ssh"
    local backup_dir="$PROJECT_ROOT/tools/ssh/backups"
    
    # Create directories
    mkdir -p "$docs_dir" "$backup_dir"
    
    # Create documentation
    cat > "$docs_dir/ssh-setup.md" << 'EOF'
# WillowCMS SSH Configuration

## Overview
This document describes the SSH setup for the WillowCMS project with the `whatismyadapter` user.

## User Configuration
- **User**: whatismyadapter
- **UID**: 1034
- **GID**: 100
- **Group**: users (also member of docker group)

## SSH Keys Generated
1. **willow_droplet** - Digital Ocean droplet access
2. **willow_local** - Local Docker services  
3. **willow_services** - Long-term service management
4. **willow_main** - Main deployment key

## SSH Hosts Configured
- `willow-droplet` / `willow-production` - Production droplet
- `willow-local` / `localhost-docker` - Local development
- `willow-services` - Services host
- `willow-main` - Main deployment host

## Environment Variables
All SSH configuration is managed via `.env` and `stack.env` files:

```bash
SSH_USER=whatismyadapter
SSH_UID=1034
SSH_GID=100
DROPLET_HOST=your-droplet-ip
DROPLET_SSH_PORT=22
# ... etc
```

## Usage Examples

### Connect to droplet
```bash
sudo -u whatismyadapter ssh willow-droplet
```

### Connect to local Docker host
```bash
sudo -u whatismyadapter ssh willow-local
```

### Deploy via SSH key
```bash
sudo -u whatismyadapter ssh-add ~/.ssh/willow_droplet
sudo -u whatismyadapter ssh willow-droplet 'docker compose up -d'
```

## Security Features
- Key-only authentication (passwords disabled)
- User-specific authorized_keys
- Limited sudo permissions for service management
- Environment variable driven configuration
- No hardcoded secrets or IPs

## Key Rotation
To rotate SSH keys:
1. Generate new keys: `./tools/ssh/rotate-keys.sh`
2. Update authorized_keys on target hosts
3. Test new key access
4. Remove old keys

## Troubleshooting

### Connection refused
- Check SSH service status: `sudo systemctl status sshd`
- Verify SSH configuration: `sudo sshd -t`
- Check user permissions: `ls -la /home/whatismyadapter/.ssh/`

### Permission denied
- Verify key is loaded: `ssh-add -l`
- Check authorized_keys on target host
- Verify SSH config: `ssh -v willow-droplet`

### Environment variables not working
- Source environment: `set -a; source .env; set +a`
- Check variable values: `echo $DROPLET_HOST`
- Verify .env file permissions
EOF

    # Create key rotation script
    cat > "$PROJECT_ROOT/tools/ssh/rotate-keys.sh" << 'EOF'
#!/usr/bin/env bash
# SSH Key Rotation Script for WillowCMS
set -euo pipefail

SSH_USER="whatismyadapter"
SSH_DIR="/home/$SSH_USER/.ssh"

echo "Rotating SSH keys for $SSH_USER..."

# Backup existing keys
backup_dir="$SSH_DIR/backup-$(date +%Y%m%d_%H%M%S)"
mkdir -p "$backup_dir"
cp "$SSH_DIR"/willow_* "$backup_dir/" 2>/dev/null || true

# Generate new keys
key_names=("willow_droplet" "willow_local" "willow_services" "willow_main")

for key_name in "${key_names[@]}"; do
    echo "Generating new key: $key_name"
    sudo -u "$SSH_USER" ssh-keygen -t ed25519 -f "$SSH_DIR/$key_name" -C "$SSH_USER@willowcms-$key_name-$(date +%Y%m%d)" -N ""
    chmod 600 "$SSH_DIR/$key_name"
    chmod 644 "$SSH_DIR/$key_name.pub"
done

echo "Key rotation complete. Update authorized_keys on target hosts with new public keys:"
for key_name in "${key_names[@]}"; do
    echo "=== $key_name.pub ==="
    cat "$SSH_DIR/$key_name.pub"
    echo
done
EOF

    chmod +x "$PROJECT_ROOT/tools/ssh/rotate-keys.sh"
    
    # Set ownership
    chown -R "$SSH_UID:$SSH_GID" "$docs_dir" "$backup_dir"
    chown "$SSH_UID:$SSH_GID" "$PROJECT_ROOT/tools/ssh/rotate-keys.sh"
    
    log_success "Created documentation and backup scripts"
}

# Integration checks
run_integration_checks() {
    log "Running integration checks..."
    
    local checks_passed=0
    local total_checks=7
    
    # Check 1: User exists with correct UID/GID
    if id "$SSH_USER" &>/dev/null; then
        local uid=$(id -u "$SSH_USER")
        local gid=$(id -g "$SSH_USER")
        if [ "$uid" = "$SSH_UID" ] && [ "$gid" = "$SSH_GID" ]; then
            log_success "✓ User $SSH_USER exists with correct UID/GID ($uid:$gid)"
            ((checks_passed++))
        else
            log_error "✗ User $SSH_USER has incorrect UID/GID ($uid:$gid, expected $SSH_UID:$SSH_GID)"
        fi
    else
        log_error "✗ User $SSH_USER does not exist"
    fi
    
    # Check 2: User is in docker group
    if groups "$SSH_USER" | grep -q docker; then
        log_success "✓ User $SSH_USER is in docker group"
        ((checks_passed++))
    else
        log_error "✗ User $SSH_USER is not in docker group"
    fi
    
    # Check 3: SSH directory exists with correct permissions
    local ssh_dir="/home/$SSH_USER/.ssh"
    if [ -d "$ssh_dir" ] && [ "$(stat -c %a "$ssh_dir")" = "700" ]; then
        log_success "✓ SSH directory exists with correct permissions"
        ((checks_passed++))
    else
        log_error "✗ SSH directory missing or incorrect permissions"
    fi
    
    # Check 4: SSH keys exist
    local keys_exist=true
    for key_name in willow_droplet willow_local willow_services willow_main; do
        if [ ! -f "$ssh_dir/$key_name" ]; then
            keys_exist=false
            break
        fi
    done
    if $keys_exist; then
        log_success "✓ All SSH keys generated"
        ((checks_passed++))
    else
        log_error "✗ Some SSH keys are missing"
    fi
    
    # Check 5: SSH config exists
    if [ -f "$ssh_dir/config" ]; then
        log_success "✓ SSH client configuration exists"
        ((checks_passed++))
    else
        log_error "✗ SSH client configuration missing"
    fi
    
    # Check 6: Environment files updated
    if grep -q "SSH_USER=whatismyadapter" "$ENV_FILE" 2>/dev/null; then
        log_success "✓ Environment files updated with SSH configuration"
        ((checks_passed++))
    else
        log_error "✗ Environment files not updated with SSH configuration"
    fi
    
    # Check 7: SSH server configuration
    if [ -f "/etc/ssh/sshd_config.d/willow-security.conf" ]; then
        log_success "✓ SSH server security configuration created"
        ((checks_passed++))
    else
        log_error "✗ SSH server security configuration missing"
    fi
    
    echo
    log "Integration check results: $checks_passed/$total_checks checks passed"
    
    if [ $checks_passed -eq $total_checks ]; then
        log_success "All integration checks passed! 🎉"
        echo
        echo "Next steps:"
        echo "1. Copy public keys to target hosts:"
        echo "   - Droplet: cat /home/$SSH_USER/.ssh/willow_droplet.pub"
        echo "   - Local: cat /home/$SSH_USER/.ssh/willow_local.pub"
        echo "   - Services: cat /home/$SSH_USER/.ssh/willow_services.pub"
        echo "   - Main: cat /home/$SSH_USER/.ssh/willow_main.pub"
        echo
        echo "2. Update .env file with actual host addresses"
        echo "3. Test SSH connections:"
        echo "   sudo -u $SSH_USER ssh willow-droplet"
        echo "   sudo -u $SSH_USER ssh willow-local"
        echo
        echo "4. Restart SSH service: sudo systemctl restart sshd"
        echo
        echo "5. View documentation: cat $PROJECT_ROOT/docs/ssh/ssh-setup.md"
    else
        log_warning "Some checks failed. Please review and fix issues before proceeding."
        return 1
    fi
}

# Main execution
main() {
    echo
    log "WillowCMS SSH Access Setup"
    log "Setting up whatismyadapter user (UID: $SSH_UID, GID: $SSH_GID)"
    log "Multi-environment support: droplet, local Docker, long-term services"
    echo
    
    # Perform setup steps
    check_root
    create_user
    configure_user_permissions
    generate_ssh_keys
    create_ssh_config
    update_env_files
    configure_ssh_server
    create_documentation
    
    echo
    log "Running final integration checks..."
    run_integration_checks
    
    echo
    log_success "SSH setup complete!"
    log "Please review the generated documentation at: $PROJECT_ROOT/docs/ssh/ssh-setup.md"
}

# Show usage if requested
if [[ "${1:-}" == "--help" ]] || [[ "${1:-}" == "-h" ]]; then
    echo "WillowCMS SSH Access Setup Script"
    echo
    echo "This script sets up the whatismyadapter user with SSH key-only access for:"
    echo "- Digital Ocean droplet communication"
    echo "- Local Docker service management"  
    echo "- Long-term service administration"
    echo
    echo "Usage: sudo $0"
    echo
    echo "The script will:"
    echo "1. Create/verify whatismyadapter user (UID 1034, GID 100)"
    echo "2. Configure Docker service permissions"
    echo "3. Generate SSH keys for different environments"
    echo "4. Create SSH client/server configuration"
    echo "5. Update .env files with SSH settings"
    echo "6. Create documentation and backup tools"
    echo "7. Run integration checks"
    echo
    exit 0
fi

# Run main function
main "$@"