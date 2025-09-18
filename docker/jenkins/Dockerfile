FROM jenkins/jenkins:lts

# Switch to root user to install additional packages
USER root

# Configure apt to use reliable mirrors and handle network issues
RUN echo 'Acquire::Retries "3";' > /etc/apt/apt.conf.d/80-retries && \
    echo 'Acquire::http::Timeout "60";' >> /etc/apt/apt.conf.d/80-retries && \
    echo 'Acquire::https::Timeout "60";' >> /etc/apt/apt.conf.d/80-retries

# Setup apt sources with working mirrors (fix DNS resolution issues)
RUN if [ -f /etc/apt/sources.list ]; then \
        cp /etc/apt/sources.list /etc/apt/sources.list.backup; \
    fi && \
    echo "deb http://deb.debian.org/debian bookworm main" > /etc/apt/sources.list && \
    echo "deb http://deb.debian.org/debian bookworm-updates main" >> /etc/apt/sources.list && \
    echo "deb http://deb.debian.org/debian-security bookworm-security main" >> /etc/apt/sources.list

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

# Add Docker apt repository with retry logic
RUN for i in 1 2 3; do \
        curl -fsSLo /usr/share/keyrings/docker-archive-keyring.asc \
        https://download.docker.com/linux/debian/gpg && break || sleep 5; \
    done

RUN echo "deb [arch=$(dpkg --print-architecture) \
    signed-by=/usr/share/keyrings/docker-archive-keyring.asc] \
    https://download.docker.com/linux/debian \
    $(lsb_release -cs) stable" > /etc/apt/sources.list.d/docker.list
    
# Update package list and install Docker CLI with retry
RUN for i in 1 2 3; do \
        apt-get update && apt-get install -y docker-ce-cli && break || sleep 10; \
    done

# Switch back to jenkins user
USER jenkins

# Copy the list of plugins to install
COPY docker/jenkins/plugins.txt /usr/share/jenkins/ref/plugins.txt

# Install plugins
RUN jenkins-plugin-cli -f /usr/share/jenkins/ref/plugins.txt

# Set up environment variables for Jenkins Configuration as Code
ENV CASC_JENKINS_CONFIG=/var/jenkins_home/jenkins.yaml

# Copy the Jenkins Configuration as Code YAML file
COPY docker/jenkins/jenkins.yaml /var/jenkins_home/jenkins.yaml

# Skip the initial setup wizard
ENV JAVA_OPTS -Djenkins.install.runSetupWizard=false