FROM jenkins/jenkins:lts-jdk17

USER root

# Install Docker CLI and Docker Compose
RUN apt-get update && apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release \
    && curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg \
    && echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/debian $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null \
    && apt-get update \
    && apt-get install -y docker-ce-cli docker-compose-plugin \
    && rm -rf /var/lib/apt/lists/*

# Install additional tools
RUN apt-get update && apt-get install -y \
    git \
    php8.1-cli \
    php8.1-curl \
    php8.1-mysql \
    composer \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

# Install necessary packages
RUN apt-get update && apt-get install -y lsb-release curl software-properties-common wget

# Add PHP 8.3 repository
RUN curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list

# Install PHP 8.3 and extensions
RUN apt-get update && apt-get install -y \
    php8.3 \
    php8.3-cli \
    php8.3-intl \
    php8.3-dom \
    php8.3-xml \
    php8.3-mbstring \
    php8.3-zip \
    php8.3-curl \
    php8.3-mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add Docker apt repository
RUN curl -fsSLo /usr/share/keyrings/docker-archive-keyring.asc \
    https://download.docker.com/linux/debian/gpg
RUN echo "deb [arch=$(dpkg --print-architecture) \
    signed-by=/usr/share/keyrings/docker-archive-keyring.asc] \
    https://download.docker.com/linux/debian \
    $(lsb_release -cs) stable" > /etc/apt/sources.list.d/docker.list
RUN apt-get update && apt-get install -y docker-ce-cli

# Switch back to jenkins user
USER jenkins

# Copy the list of plugins to install
COPY infrastructure/docker/jenkins/plugins.txt /usr/share/jenkins/ref/plugins.txt

# Install plugins
RUN jenkins-plugin-cli -f /usr/share/jenkins/ref/plugins.txt

# Set up environment variables for Jenkins Configuration as Code
ENV CASC_JENKINS_CONFIG=/var/jenkins_home/jenkins.yaml

# Copy the Jenkins Configuration as Code YAML file
COPY infrastructure/docker/jenkins/jenkins.yaml /var/jenkins_home/jenkins.yaml

# Skip the initial setup wizard
ENV JAVA_OPTS -Djenkins.install.runSetupWizard=false