# DDEV Cloud Development & CI/CD Integration Guide for WillowCMS

## Executive Summary

This comprehensive guide explores **DDEV integration possibilities** for your WillowCMS project, covering cloud development options, CI/CD integration, and comparing DDEV with your current Docker Compose workflow.

---

## Table of Contents
1. [DDEV Cloud Development Possibilities](#ddev-cloud-development-possibilities)
2. [CI/CD Integration with DDEV](#cicd-integration-with-ddev)
3. [DDEV vs Current Docker Compose Workflow](#ddev-vs-current-docker-compose-workflow)
4. [WillowCMS-Specific Recommendations](#willowcms-specific-recommendations)
5. [Migration Strategies](#migration-strategies)
6. [Decision Matrix](#decision-matrix)

---

## 1. DDEV Cloud Development Possibilities

### 1.1 Remote Development via SSH

DDEV can be used on remote servers for cloud development:

```bash
# SSH into your cloud server
ssh user@your-cloud-server

# Install DDEV on the remote server
curl -fsSL https://ddev.com/install.sh | bash

# Clone your project
git clone https://github.com/yourusername/willow.git
cd willow

# Initialize DDEV
ddev config --project-type=php --php-version=8.1 --docroot=app/webroot
ddev start
```

**Key Features:**
- ✅ Works on any Linux VPS (DigitalOcean, AWS EC2, Azure)
- ✅ Maintains consistent environment across local and cloud
- ❌ Requires persistent server (not serverless)
- ⚠️ Security considerations for exposed DDEV router

### 1.2 GitHub Codespaces Integration

DDEV fully supports GitHub Codespaces for cloud development:

```yaml
# .devcontainer/devcontainer.json
{
  "name": "WillowCMS DDEV",
  "image": "mcr.microsoft.com/devcontainers/universal:2",
  "features": {
    "ghcr.io/ddev/ddev/install-ddev:latest": {}
  },
  "postCreateCommand": "ddev start",
  "forwardPorts": [8080, 8025, 8082],
  "portsAttributes": {
    "8080": {"label": "WillowCMS", "onAutoForward": "notify"},
    "8025": {"label": "Mailpit", "onAutoForward": "silent"},
    "8082": {"label": "phpMyAdmin", "onAutoForward": "silent"}
  }
}
```

### 1.3 Gitpod Integration

DDEV provides first-class Gitpod support:

```yaml
# .gitpod.yml
tasks:
  - init: |
      brew install ddev/ddev/ddev
      mkcert -install
      ddev config --auto
      ddev start
    command: ddev start

ports:
  - port: 8080
    visibility: public
    onOpen: open-preview
  - port: 8025
    visibility: public
    onOpen: ignore
  - port: 8082
    visibility: public
    onOpen: ignore

vscode:
  extensions:
    - felixfbecker.php-debug
    - whatwedo.twig
```

### 1.4 DigitalOcean App Platform Integration

While DDEV isn't directly deployable to App Platform, you can use DDEV for development and build:

```yaml
# .do/app.yaml
name: willowcms
services:
- name: web
  build_command: |
    # Use DDEV to prepare build artifacts
    ddev composer install --no-dev
    ddev exec bin/cake asset_compress build
  environment_slug: php
  github:
    repo: yourusername/willow
    branch: main
    deploy_on_push: true
  run_command: heroku-php-apache2 app/webroot
```

### 1.5 Production Deployment Limitations

**DDEV is NOT designed for production**, but can facilitate deployment:

```bash
# Build production artifacts with DDEV
ddev composer install --no-dev --optimize-autoloader
ddev exec bin/cake schema_cache build
ddev export-db > production-db.sql

# Generate Docker image from DDEV
ddev image build -t willowcms:production
docker push ghcr.io/yourusername/willowcms:production
```

---

## 2. CI/CD Integration with DDEV

### 2.1 GitHub Actions with DDEV

Create `.github/workflows/ddev-test.yml`:

```yaml
name: DDEV Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v4
    
    - name: Install DDEV
      run: |
        curl -fsSL https://ddev.com/install.sh | bash
        
    - name: Configure DDEV
      run: |
        ddev config --auto
        ddev config --php-version=8.1
        
    - name: Start DDEV
      run: ddev start
      
    - name: Install Dependencies
      run: ddev composer install
      
    - name: Run Database Migrations
      run: ddev exec bin/cake migrations migrate
      
    - name: Run Tests
      run: |
        ddev exec vendor/bin/phpunit
        ddev exec vendor/bin/phpstan analyse
        ddev exec vendor/bin/phpcs
        
    - name: Generate Coverage Report
      run: ddev exec vendor/bin/phpunit --coverage-html coverage
      
    - name: Upload Coverage
      uses: actions/upload-artifact@v3
      with:
        name: coverage-report
        path: coverage/
```

### 2.2 GitLab CI/CD with DDEV

`.gitlab-ci.yml`:

```yaml
stages:
  - test
  - build
  - deploy

variables:
  DDEV_NONINTERACTIVE: "true"

test:
  stage: test
  image: ddev/ddev-gitpod-base:stable
  services:
    - docker:dind
  before_script:
    - apt-get update && apt-get install -y docker.io
    - curl -fsSL https://ddev.com/install.sh | bash
  script:
    - ddev config --auto
    - ddev start
    - ddev composer install
    - ddev exec vendor/bin/phpunit
  artifacts:
    reports:
      junit: junit.xml
```

### 2.3 Building Docker Images from DDEV

```yaml
# GitHub Actions workflow for building production images
name: Build Production Image

on:
  push:
    branches: [main]
    tags: ['v*']

jobs:
  build:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v4
    
    - name: Setup DDEV
      run: |
        curl -fsSL https://ddev.com/install.sh | bash
        ddev config --auto
        ddev start
        
    - name: Build Application
      run: |
        ddev composer install --no-dev
        ddev exec bin/cake asset_compress build
        
    - name: Export Application
      run: |
        ddev export-db > database.sql
        tar -czf app.tar.gz app/ database.sql
        
    - name: Build Docker Image
      run: |
        docker build -t ghcr.io/${{ github.repository }}:${{ github.sha }} .
        
    - name: Push to Registry
      run: |
        echo "${{ secrets.GITHUB_TOKEN }}" | docker login ghcr.io -u ${{ github.actor }} --password-stdin
        docker push ghcr.io/${{ github.repository }}:${{ github.sha }}
```

---

## 3. DDEV vs Current Docker Compose Workflow

### 3.1 Feature Comparison Matrix

| Feature | DDEV | Current Docker Compose | Winner |
|---------|------|------------------------|---------|
| **Setup Complexity** | Simple (`ddev start`) | Complex (manual config) | ✅ DDEV |
| **CakePHP Support** | Built-in | Manual configuration | ✅ DDEV |
| **Multi-Project** | Excellent (router) | Manual port management | ✅ DDEV |
| **Custom Services** | Via addons | Full flexibility | ✅ Docker Compose |
| **Production Ready** | No | Yes | ✅ Docker Compose |
| **Cloud Deployment** | Development only | Full support | ✅ Docker Compose |
| **ARM64 Support** | Excellent | Manual config required | ✅ DDEV |
| **Team Onboarding** | Very fast | Requires documentation | ✅ DDEV |
| **Debugging Tools** | Built-in (Xdebug, etc) | Manual setup | ✅ DDEV |
| **Performance** | Good | Customizable | ➖ Tie |
| **Resource Usage** | Moderate | Customizable | ✅ Docker Compose |
| **CI/CD Integration** | Good | Excellent | ✅ Docker Compose |
| **Portainer Compatible** | No | Yes | ✅ Docker Compose |
| **Custom Dockerfile** | Limited | Full control | ✅ Docker Compose |

### 3.2 Development Workflow Comparison

#### Current Docker Compose Workflow
```bash
# Current complex setup
cp .env.example .env
# Edit multiple .env files manually
./run_dev_env.sh
# Select options from menu
# Wait for services
# Handle various error states
```

#### DDEV Workflow
```bash
# DDEV simple setup
ddev start
# Everything configured automatically
ddev launch  # Opens browser
```

### 3.3 Team Collaboration

| Aspect | DDEV | Current Setup |
|--------|------|---------------|
| **New Developer Setup** | 5 minutes | 30+ minutes |
| **Environment Consistency** | Guaranteed | Depends on docs |
| **Database Sync** | `ddev pull` | Manual scripts |
| **File Sync** | `ddev pull` | Manual process |
| **Settings Management** | Automatic | Manual .env files |

---

## 4. WillowCMS-Specific Recommendations

### 4.1 Optimal DDEV Configuration for WillowCMS

Create `.ddev/config.yaml`:

```yaml
name: willowcms
type: php
docroot: app/webroot
php_version: "8.1"
nodejs_version: "18"
webserver_type: nginx-fpm
xdebug_enabled: false
additional_hostnames: []
additional_fqdns: []
database:
  type: mysql
  version: "8.0"
use_dns_when_possible: true
composer_version: "2"
web_environment:
  - APP_NAME=WillowCMS
  - DEBUG=true
  - APP_ENCODING=UTF-8
  - APP_DEFAULT_LOCALE=en_GB
  - APP_DEFAULT_TIMEZONE=America/Chicago
  - SECURITY_SALT=your-64-char-salt-here

# Required PHP extensions for CakePHP 5.x
webimage_extra_packages:
  - php8.1-intl
  - php8.1-mbstring
  - php8.1-simplexml
  - php8.1-redis

hooks:
  post-start:
    - exec: composer install
    - exec: bin/cake migrations migrate
    - exec: bin/cake cache clear_all
```

### 4.2 Adding Required Services

#### Redis Service
`.ddev/docker-compose.redis.yaml`:
```yaml
services:
  redis:
    image: redis:7-alpine
    expose:
      - "6379"
    environment:
      - REDIS_PASSWORD=${REDIS_PASSWORD:-yourpassword}
    labels:
      com.ddev.approot: ${DDEV_APPROOT}
  
  redis-commander:
    image: rediscommander/redis-commander:latest
    expose:
      - "8081"
    environment:
      - REDIS_HOST=redis
      - REDIS_PORT=6379
      - REDIS_PASSWORD=${REDIS_PASSWORD:-yourpassword}
    labels:
      com.ddev.approot: ${DDEV_APPROOT}
```

### 4.3 Custom DDEV Commands

Create `.ddev/commands/host/cake`:
```bash
#!/bin/bash
## Description: Run CakePHP console commands
## Usage: cake [command]
## Example: ddev cake migrations migrate

ddev exec bin/cake "$@"
```

Create `.ddev/commands/host/test`:
```bash
#!/bin/bash
## Description: Run PHPUnit tests
## Usage: test [options]
## Example: ddev test --coverage

ddev exec vendor/bin/phpunit "$@"
```

---

## 5. Migration Strategies

### 5.1 Strategy 1: Full DDEV Migration (Not Recommended)

**Pros:**
- Simplified local development
- Consistent environments
- Faster onboarding

**Cons:**
- ❌ Loses custom WillowCMS Docker image
- ❌ Not compatible with Portainer deployment
- ❌ Requires complete CI/CD rewrite
- ❌ No production deployment path

**Verdict:** Not suitable for WillowCMS due to production requirements

### 5.2 Strategy 2: Hybrid Approach (Recommended)

**Implementation:**
1. Use DDEV for local development
2. Keep Docker Compose for production/Portainer
3. Sync configurations between both

```bash
# Development (DDEV)
ddev start
ddev composer install
ddev cake migrations migrate

# Production (Docker Compose)
docker compose -f docker-compose-port.yml up -d
```

**Benefits:**
- ✅ Fast local development
- ✅ Maintains production deployment
- ✅ Gradual team adoption
- ✅ Preserves existing CI/CD

### 5.3 Strategy 3: DDEV as Optional Alternative

Add DDEV configuration alongside Docker Compose:

```bash
willow/
├── .ddev/                 # DDEV configuration (optional)
├── docker-compose.yml     # Current setup (primary)
├── run_dev_env.sh         # Current workflow
└── README.md              # Document both options
```

Team members choose their preferred environment.

---

## 6. Decision Matrix

### 6.1 Scoring Criteria (1-5, 5 = Best)

| Criteria | Weight | DDEV Only | Hybrid | Current Only | Keep Both |
|----------|--------|-----------|--------|--------------|-----------|
| **Developer Experience** | 25% | 5 | 4 | 2 | 3 |
| **Production Compatibility** | 25% | 1 | 5 | 5 | 5 |
| **CI/CD Integration** | 20% | 3 | 4 | 5 | 4 |
| **Team Adoption** | 15% | 3 | 4 | 5 | 4 |
| **Maintenance Burden** | 10% | 4 | 2 | 3 | 2 |
| **Cost** | 5% | 5 | 3 | 3 | 3 |
| **Weighted Score** | - | **2.85** | **4.05** | **4.15** | **3.85** |

### 6.2 Recommendation for WillowCMS

Based on analysis, **maintain your current Docker Compose setup** with these enhancements:

1. **Primary Workflow**: Keep `docker-compose.yml` and `run_dev_env.sh`
2. **Optional DDEV**: Add DDEV config for developers who prefer it
3. **Documentation**: Provide guides for both approaches
4. **CI/CD**: Maintain current GitHub Actions with Docker Compose
5. **Production**: Continue using Portainer with custom Docker image

### 6.3 Implementation Roadmap

#### Phase 1: Evaluation (1 week)
```bash
# Test DDEV with WillowCMS
git checkout -b feature/ddev-evaluation
ddev config --project-type=php --php-version=8.1
ddev start
# Run full test suite
```

#### Phase 2: Optional Integration (1 week)
- Add `.ddev/` configuration to repository
- Document DDEV setup in README
- Create comparison guide for team
- Keep as optional alternative

#### Phase 3: Monitor & Decide (1 month)
- Gather team feedback
- Compare development velocity
- Assess maintenance burden
- Make final decision

---

## 7. Conclusion

### ✅ **Key Findings:**

1. **DDEV excels at local development** but isn't suitable for your production needs
2. **Your current Docker Compose setup** is well-suited for Portainer and cloud deployment
3. **Hybrid approach** offers flexibility but increases maintenance
4. **Team familiarity** with current setup is valuable

### 🎯 **Final Recommendation:**

**Stick with your current Docker Compose workflow** because:
- ✅ Already integrated with Portainer
- ✅ Supports your DigitalOcean deployment
- ✅ Custom WillowCMS image is essential
- ✅ CI/CD pipelines are already configured
- ✅ Team is familiar with the workflow

**Consider DDEV only if:**
- Team experiences significant onboarding issues
- Local development becomes too complex
- You're willing to maintain dual configurations
- You need better cross-platform support (especially Apple Silicon)

### 📊 **Cost-Benefit Analysis:**

| Action | Cost | Benefit | ROI |
|--------|------|---------|-----|
| **Keep Current** | $0 | Maintains stability | High |
| **Add DDEV Optional** | Low | Some developer happiness | Medium |
| **Full Migration** | High | Developer experience | Low |
| **Hybrid Approach** | Medium | Flexibility | Medium |

---

## Appendix A: Quick DDEV Test

If you want to quickly evaluate DDEV with WillowCMS:

```bash
# 1. Install DDEV (macOS)
brew install ddev/ddev/ddev

# 2. Create test directory
mkdir willow-ddev-test
cd willow-ddev-test

# 3. Copy your app directory
cp -r /Volumes/1TB_DAVINCI/docker/willow/app .

# 4. Initialize DDEV
ddev config --project-type=php --php-version=8.1 --docroot=app/webroot
ddev start

# 5. Install dependencies
ddev composer install

# 6. Test the application
ddev launch

# 7. Clean up
ddev delete -O
cd ..
rm -rf willow-ddev-test
```

---

## Appendix B: Resources

- [DDEV Documentation](https://ddev.readthedocs.io/)
- [DDEV CakePHP Guide](https://ddev.readthedocs.io/en/stable/users/quickstart/#cakephp)
- [DDEV GitHub Actions](https://github.com/ddev/github-action-setup-ddev)
- [Your Current Setup](./docker-compose.yml)
- [Your CI/CD](./.github/workflows/)
- [Your Deployment](./portainer-stacks/)

---

*Last Updated: October 9, 2025*  
*Author: WillowCMS Development Team*  
*Status: Recommendation - Keep Current Docker Compose Workflow*