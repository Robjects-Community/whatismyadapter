# WhatIsMyAdaptor - Willow CMS

A modern, AI-powered Content Management System built with CakePHP 5.x, featuring Docker containerization and advanced content processing capabilities.

## 🚀 Quick Start

```bash
# Clone the repository
git clone https://github.com/Robjects-Community/WhatIsMyAdaptor.git
cd WhatIsMyAdaptor

# Copy environment configuration (optional)
cp docker-compose.override.yml.example docker-compose.override.yml

# Start all services with Docker Compose
docker compose up -d

# Access the application
open http://localhost:8080
```

## 📋 Features

- **Modern PHP Stack**: CakePHP 5.x with PHP 8.1+
- **AI Integration**: Anthropic Claude API for content processing
- **Containerized**: Full Docker Compose setup with nginx, MySQL, Redis, and Mailpit
- **Queue System**: Redis-based job queuing for background processing
- **Multi-Theme**: Plugin-based theme architecture
- **Testing**: Comprehensive PHPUnit test suite (292+ tests)
- **Translation**: Google Translate API integration
- **Developer Friendly**: Includes development aliases, code quality tools, and comprehensive documentation

## 🛠️ Technology Stack

- **Backend**: PHP 8.1+, CakePHP 5.x
- **Database**: MySQL 8.0+
- **Cache/Queue**: Redis 7+
- **Web Server**: Nginx
- **Mail**: Mailpit (development)
- **Container**: Docker & Docker Compose

## 📚 Documentation

- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Comprehensive deployment guide and platform compatibility
- **[CLOUDFLARE_FAQ.md](CLOUDFLARE_FAQ.md)** - Why Cloudflare Workers fails and what to do about it
- **[CHANGELOG.md](CHANGELOG.md)** - Version history and changes
- **[cakephp/README.md](cakephp/README.md)** - Application directory structure
- **[.github/workflows/README.md](.github/workflows/README.md)** - CI/CD workflows documentation
- **[docs/](docs/)** - Additional documentation (if available)

## ⚠️ Important: Cloudflare Workers Compatibility

**This application CANNOT be deployed to Cloudflare Workers.**

Cloudflare Workers is a serverless JavaScript/WebAssembly platform. This project is a PHP-based CakePHP application that requires:
- PHP 8.1+ runtime
- MySQL database
- Redis for caching and queues
- Writable filesystem
- Long-running background processes

The `wrangler.toml` file exists to prevent automatic deployment attempts by the Cloudflare Workers GitHub integration. **Deployment failures are intentional and expected.**

👉 **See [DEPLOYMENT.md](DEPLOYMENT.md) for proper deployment instructions.**  
👉 **See [CLOUDFLARE_FAQ.md](CLOUDFLARE_FAQ.md) for details about the Cloudflare Workers situation.**

## 🐳 Docker Services

The application runs with the following services:

- **willowcms**: Main PHP application (nginx + PHP-FPM)
- **mysql**: MySQL 8.0 database
- **mailpit**: Email testing interface
- **redis** (if configured): Cache and queue backend

## 🔧 Development

### Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- 2GB RAM minimum (4GB recommended)

### Setup Development Environment

```bash
# Run the setup script
./setup_dev_env.sh

# Install development aliases (optional)
./setup_dev_aliases.sh
source ~/.bashrc  # or ~/.zshrc
```

### Useful Commands

```bash
# Start services
docker compose up -d

# View logs
docker compose logs -f willowcms

# Access PHP container shell
docker compose exec willowcms /bin/sh

# Run tests
docker compose exec willowcms php /var/www/html/vendor/bin/phpunit

# Run migrations
docker compose exec willowcms /var/www/html/bin/cake migrations migrate

# Stop services
docker compose down
```

### With Development Aliases

After running `./setup_dev_aliases.sh`:

```bash
phpunit                    # Run all tests
cake_shell migrations migrate  # Run migrations
cake_queue_worker_verbose  # Start queue worker
phpcs_sniff               # Check code standards
phpcs_fix                 # Auto-fix code style issues
```

## 🧪 Testing

The project includes comprehensive testing:

```bash
# Run all tests
docker compose exec willowcms php /var/www/html/vendor/bin/phpunit

# Run specific test file
docker compose exec willowcms php /var/www/html/vendor/bin/phpunit tests/TestCase/Controller/UsersControllerTest.php

# Generate coverage report
docker compose exec willowcms php /var/www/html/vendor/bin/phpunit --coverage-html webroot/coverage/
# Access at http://localhost:8080/coverage/
```

## 📦 Project Structure

```
.
├── cakephp/              # Main CakePHP application
│   ├── src/              # Application source code
│   ├── config/           # Configuration files
│   ├── plugins/          # Theme plugins
│   ├── tests/            # Test suite
│   └── webroot/          # Public web assets
├── docker/               # Docker configuration
│   ├── willowcms/        # Application container config
│   └── mysql/            # Database container config
├── docker-compose.yml    # Docker Compose configuration
├── DEPLOYMENT.md         # Deployment documentation
├── CHANGELOG.md          # Version history
└── README.md            # This file
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Quality

The project uses:
- **PHPUnit**: Unit and integration testing
- **PHP CodeSniffer**: Code style checking
- **PHPStan**: Static analysis (Level 5)

```bash
# Check code standards
docker compose exec willowcms ./vendor/bin/phpcs

# Auto-fix style issues
docker compose exec willowcms ./vendor/bin/phpcbf

# Run static analysis
docker compose exec willowcms ./vendor/bin/phpstan analyse
```

## 📄 License

See [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **[CakePHP](https://cakephp.org)** - The robust PHP framework powering this CMS
- **[Anthropic](https://anthropic.com)** - AI capabilities via Claude API
- **[Google Cloud](https://cloud.google.com)** - Translation services
- Community contributors and maintainers

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/Robjects-Community/WhatIsMyAdaptor/issues)
- **Discussions**: [GitHub Discussions](https://github.com/Robjects-Community/WhatIsMyAdaptor/discussions)

---

**Built with ❤️ by the Robjects Community**
