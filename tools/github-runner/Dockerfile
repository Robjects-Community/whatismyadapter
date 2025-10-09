# Multi-platform GitHub Actions Runner for WillowCMS
# Supports: linux/amd64, linux/arm64
FROM --platform=$BUILDPLATFORM ubuntu:22.04 as base

ARG TARGETPLATFORM
ARG BUILDPLATFORM
ARG RUNNER_VERSION=2.320.0

# Prevent interactive prompts during package installation
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=America/Chicago

# Install base dependencies
RUN apt-get update && apt-get install -y \
    curl \
    ca-certificates \
    gnupg \
    lsb-release \
    git \
    jq \
    build-essential \
    libssl-dev \
    libffi-dev \
    python3 \
    python3-venv \
    python3-dev \
    python3-pip \
    zip \
    unzip \
    wget \
    sudo \
    tzdata \
    && rm -rf /var/lib/apt/lists/*

# Install PHP 8.1 and extensions
RUN apt-get update && apt-get install -y \
    software-properties-common \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update && apt-get install -y \
    php8.1 \
    php8.1-cli \
    php8.1-common \
    php8.1-curl \
    php8.1-mbstring \
    php8.1-mysql \
    php8.1-xml \
    php8.1-zip \
    php8.1-bcmath \
    php8.1-gd \
    php8.1-intl \
    php8.1-redis \
    php8.1-sqlite3 \
    php8.1-xdebug \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js 18.x and npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && npm install -g yarn

# Install Docker CLI for Docker-in-Docker support
RUN curl -fsSL https://get.docker.com | sh

# Install GitHub CLI
RUN curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | gpg --dearmor -o /usr/share/keyrings/githubcli-archive-keyring.gpg \
    && echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" | tee /etc/apt/sources.list.d/github-cli.list > /dev/null \
    && apt-get update \
    && apt-get install -y gh \
    && rm -rf /var/lib/apt/lists/*

# Install Python packages for MkDocs (documentation)
RUN pip3 install --no-cache-dir \
    mkdocs \
    mkdocs-material \
    mkdocs-minify-plugin \
    mkdocs-git-revision-date-localized-plugin

# Install MySQL client for database operations
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Create runner user with UID 1000 and GID 1000
RUN groupadd -g 1000 runner \
    && useradd -m -u 1000 -g 1000 -s /bin/bash runner \
    && usermod -aG docker runner \
    && echo "runner ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Set up runner directory
WORKDIR /home/runner

# Download and extract GitHub Actions runner based on architecture
RUN ARCH=$(dpkg --print-architecture) && \
    if [ "$ARCH" = "amd64" ]; then \
        RUNNER_ARCH="x64"; \
    elif [ "$ARCH" = "arm64" ]; then \
        RUNNER_ARCH="arm64"; \
    else \
        echo "Unsupported architecture: $ARCH" && exit 1; \
    fi && \
    curl -o actions-runner-linux-${RUNNER_ARCH}-${RUNNER_VERSION}.tar.gz \
        -L https://github.com/actions/runner/releases/download/v${RUNNER_VERSION}/actions-runner-linux-${RUNNER_ARCH}-${RUNNER_VERSION}.tar.gz && \
    tar xzf ./actions-runner-linux-${RUNNER_ARCH}-${RUNNER_VERSION}.tar.gz && \
    rm -f ./actions-runner-linux-${RUNNER_ARCH}-${RUNNER_VERSION}.tar.gz && \
    ./bin/installdependencies.sh

# Copy entrypoint script
COPY --chown=runner:runner scripts/entrypoint.sh /home/runner/entrypoint.sh
RUN chmod +x /home/runner/entrypoint.sh

# Set up workspace and cache directories
RUN mkdir -p /home/runner/work /home/runner/.cache /home/runner/.composer \
    && chown -R runner:runner /home/runner

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD pgrep -f "Runner.Listener" > /dev/null || exit 1

# Switch to runner user
USER runner

# Set environment variables
ENV RUNNER_ALLOW_RUNASROOT=0
ENV DEBIAN_FRONTEND=dialog

# Labels
LABEL maintainer="WillowCMS Team"
LABEL description="Multi-platform GitHub Actions Runner for WillowCMS"
LABEL version="1.0.0"

ENTRYPOINT ["/home/runner/entrypoint.sh"]