# GitHub Actions Self-Hosted Runner Setup

This document describes how to set up a self-hosted GitHub Actions runner for the WillowCMS project.

## Overview

The GitHub Actions runner binaries have been removed from the repository to reduce file size and improve security. Instead, install the runner directly on your host system.

## Installation

### 1. Download the Runner

Visit the [GitHub Actions Runner releases page](https://github.com/actions/runner/releases) and download the appropriate version for your system.

For macOS (ARM64):
```bash
# Create runner directory
mkdir -p ~/actions-runner && cd ~/actions-runner

# Download latest runner
curl -o actions-runner-osx-arm64-2.328.0.tar.gz -L https://github.com/actions/runner/releases/download/v2.328.0/actions-runner-osx-arm64-2.328.0.tar.gz

# Extract
tar xzf ./actions-runner-osx-arm64-2.328.0.tar.gz
```

### 2. Configure the Runner

```bash
# Configure the runner (requires token from GitHub)
./config.sh --url https://github.com/Robjects-Community/whatismyadapter --token YOUR_TOKEN

# Install as a service (optional)
sudo ./svc.sh install

# Start the service
sudo ./svc.sh start
```

### 3. Integration with Docker

If you need to run Docker commands in your GitHub Actions:

```bash
# Add the runner user to the docker group
sudo usermod -aG docker actions-runner
```

## Security Considerations

- Never commit runner tokens or credentials to the repository
- Use environment variables for sensitive configuration
- Regularly update the runner software
- Monitor runner activity and logs
- Implement appropriate firewall rules

## Troubleshooting

### Common Issues

1. **Permission denied errors**: Ensure the runner has appropriate permissions
2. **Docker access issues**: Verify the runner user is in the docker group
3. **Network connectivity**: Check firewall and proxy settings

### Logs

Runner logs are typically located at:
- Service logs: `/var/log/actions-runner/`
- Application logs: `~/actions-runner/_diag/`

## Maintenance

- Regularly check for runner updates
- Monitor disk space and clean up old logs
- Review and update security configurations
- Test runner functionality periodically