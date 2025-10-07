# GitHub Actions Workflows

This directory contains GitHub Actions workflow configurations for the Willow CMS project.

## Active Workflows

### `tests.yml`
Comprehensive CI/CD pipeline that runs on every push to main and on all pull requests.

**Features:**
- Automated PHPUnit testing (292+ tests)
- Code coverage generation (HTML reports)
- PHPStan static analysis (level 5)
- PHP CodeSniffer (CakePHP coding standards)
- Docker-based testing environment
- Coverage artifact uploads (7-day retention)

**Triggers:**
- Push to `main` branch
- All pull requests
- Manual workflow dispatch

### `docker-publish.yml`
Docker image build and publish workflow for production deployments.

**Triggers:**
- Push to `main` branch
- Tagged releases (e.g., v1.4.0)
- Manual workflow dispatch

### `ci.yml`
Docker build test workflow for ci-test branch.

**Triggers:**
- Push to `ci-test` branch
- Manual workflow dispatch

## Backup Files

- `ci.yml.backup` - Original CI workflow (backed up on 2025-10-07)
- `docker-publish.yml.backup` - Original Docker publish workflow (backed up on 2025-10-07)

## Usage

### Running Tests Locally

To simulate the CI environment locally, use the provided scripts:

```bash
# Run full CI test suite
./tools/ci/local-ci-test.sh

# Generate coverage report only
./tools/ci/coverage-report.sh

# Run code quality checks only
./tools/ci/code-quality.sh
```

### Accessing Coverage Reports

After running tests with coverage:

```bash
# Generate coverage
phpunit-coverage

# Open in browser
coverage-open
```

Coverage reports are available at: `http://localhost:8080/coverage/`

### Downloading Coverage from GitHub Actions

1. Navigate to the Actions tab in GitHub
2. Select a completed workflow run
3. Scroll to the Artifacts section
4. Download "coverage-report"
5. Extract and open `index.html` in a browser

## Best Practices

1. **Always run tests locally** before pushing
2. **Ensure coverage doesn't decrease** with new changes
3. **Fix code quality issues** flagged by PHPStan/PHPCS
4. **Write tests for new features** (aim for 80%+ coverage)
5. **Review failed workflows** promptly and fix issues

## Troubleshooting

### Workflow Fails on Docker Compose

- Ensure `.env.example` is up to date
- Check Docker service health checks
- Review service logs: `docker compose logs`

### Tests Pass Locally but Fail in CI

- Check for environment-specific issues
- Verify database migrations are up to date
- Review GitHub Actions logs for details

### Coverage Reports Not Generating

- Ensure Xdebug is enabled (check `php.ini`)
- Verify `xdebug.mode=coverage` is set
- Check disk space in container

## Contributing

When modifying workflows:

1. Test changes in a feature branch first
2. Use `workflow_dispatch` for manual testing
3. Document changes in this README
4. Update related documentation in `docs/`

## Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [CakePHP Testing Guide](https://book.cakephp.org/5/en/development/testing.html)
