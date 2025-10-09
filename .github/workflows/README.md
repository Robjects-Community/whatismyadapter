# GitHub Workflows

This directory contains GitHub Actions workflows for CI/CD automation.

## Active Workflows

### 1. CI Workflow (`ci.yml`)

**Trigger**: Push to `ci-test` branch or manual workflow dispatch

**Purpose**: Continuous Integration testing with Docker Build Cloud

**Actions**:
- Checks out code
- Authenticates with Docker Hub
- Sets up Docker Buildx with cloud builder
- Builds and pushes Docker images

**Configuration**:
- Uses Docker Build Cloud for faster builds
- Requires `DOCKER_USER` variable and `DOCKER_PAT` secret

### 2. Docker Publish Workflow (`docker-publish.yml`)

**Trigger**: 
- Push to `main` branch
- Tag push matching `v1.4.0` pattern
- Manual workflow dispatch

**Purpose**: Build and publish Docker images to GitHub Container Registry (ghcr.io)

**Actions**:
- Checks out repository
- Installs cosign for image signing
- Sets up Docker Buildx
- Authenticates with GitHub Container Registry
- Extracts Docker metadata (tags, labels)
- Builds and publishes container images

**Security**:
- Signs container images with cosign
- Uses GitHub OIDC for identity verification
- Automatic vulnerability scanning

## Disabled/Incompatible Workflows

### ❌ Cloudflare Workers

**Status**: Intentionally disabled

**Reason**: This is a PHP CakePHP application that cannot run on Cloudflare Workers. Cloudflare Workers is a serverless JavaScript/WebAssembly platform, while this application requires:
- PHP 8.1+ runtime
- MySQL database
- Redis cache and queue system
- Writable filesystem
- Long-running background processes (queue workers)

**Configuration**: 
- `wrangler.toml` in repository root prevents automatic deployments
- `.cfignore` file excludes all files from Cloudflare builds
- See `DEPLOYMENT.md` for proper deployment instructions

**Integration**: If you see Cloudflare Workers deployment comments on pull requests, they can be safely ignored. The deployment will fail by design.

## Adding New Workflows

When adding new workflows:

1. **Test thoroughly** in a feature branch first
2. **Use secrets** for sensitive data (never hardcode credentials)
3. **Limit triggers** to appropriate branches/events
4. **Document** the workflow purpose and configuration in this README
5. **Consider cost** - some actions use billable minutes

## Workflow Best Practices

- ✅ Use specific action versions (not `@latest`)
- ✅ Cache dependencies when possible
- ✅ Fail fast for pull request checks
- ✅ Use matrix builds for multi-version testing
- ✅ Add status badges to repository README

## Required Secrets

Configure these in repository settings → Secrets and variables → Actions:

### Secrets
- `DOCKER_PAT`: Docker Hub Personal Access Token (for Docker builds)
- `GITHUB_TOKEN`: Automatically provided by GitHub Actions

### Variables
- `DOCKER_USER`: Docker Hub username
- `CLOUD_ENDPOINT`: Docker Build Cloud endpoint (optional)

## Debugging Workflows

To debug a failed workflow:

1. Check the workflow run logs in the Actions tab
2. Re-run failed jobs with debug logging:
   - Go to Actions → Select workflow run
   - Click "Re-run jobs" → "Enable debug logging"
3. Use `workflow_dispatch` to manually trigger with custom parameters
4. Test locally with [act](https://github.com/nektos/act) (limited compatibility)

## Platform-Specific Notes

### Docker Build Cloud
The CI workflow uses Docker Build Cloud for faster builds. This requires:
- Docker subscription (Team or Business plan)
- Configured cloud builder endpoint
- Appropriate credentials

### GitHub Container Registry
Images are published to `ghcr.io/robjects-community/whatismyadaptor`. Access requires:
- GitHub authentication
- Read permissions on public images (automatic)
- Write permissions for publishing (via `GITHUB_TOKEN`)

## Further Reading

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Docker Build GitHub Action](https://github.com/docker/build-push-action)
- [Cloudflare Workers (incompatible)](https://developers.cloudflare.com/workers/)

---

**Last Updated**: October 2025
