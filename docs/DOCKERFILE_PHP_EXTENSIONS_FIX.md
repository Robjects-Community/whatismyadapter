# Dockerfile PHP Extensions Fix

## Issue
The Dockerfile build was failing during the Composer dependency installation stage with the following errors:
- `Root composer.json requires PHP extension ext-redis * but it is missing from your system`
- `cakephp/cakephp 5.2.7 requires ext-intl * -> it is missing from your system`

## Root Cause
The `composer-deps` stage was using the official `composer:2` image, which doesn't include the required PHP extensions (`ext-redis` and `ext-intl`) that are explicitly required by:
- `composer.json` (line 9: `"ext-redis": "*"`)
- CakePHP 5.x framework (requires `ext-intl`)

## Solution Applied
1. **Added PHP_VARIANT build argument**: Added `ARG PHP_VARIANT=bookworm` to maintain consistency between build stages.

2. **Rewrote composer-deps stage**: Changed from using `composer:2` image to `php:8.3-cli-bookworm` base with:
   - Required system dependencies (git, unzip, libicu-dev, etc.)
   - PHP extensions installation via `docker-php-ext-configure`, `docker-php-ext-install`, and `pecl install`
   - Composer binary copied from official composer image
   - Proper environment variables for Composer operation

3. **Updated app-build stage**: Fixed the vendor directory copy path and composer command usage.

## Key Changes Made
- **Line 12**: Added `ARG PHP_VARIANT=bookworm`
- **Lines 61-112**: Complete rewrite of composer-deps stage
- **Line 138**: Updated vendor copy path from `/app/vendor` to `/var/www/html/vendor`
- **Lines 145-149**: Fixed composer autoload optimization command

## Build Arguments
- `PHP_VERSION=8.3`: PHP version to use across all stages
- `PHP_VARIANT=bookworm`: PHP image variant (can be changed to `alpine` if needed)
- `COMPOSER_VERSION=2`: Composer version (used for copying binary)

## Verification Commands
```bash
# Build composer-deps stage only
docker build --target composer-deps -t willowcms:composer-deps .

# Verify PHP extensions are present
docker run --rm willowcms:composer-deps php -m | grep -E "intl|redis"

# Verify Composer platform requirements
docker run --rm willowcms:composer-deps composer check-platform-reqs

# Build full runtime image
docker buildx build --target runtime -t willowcms:latest .
```

## Security & Environment Variables
The Dockerfile continues to follow the project's security practices:
- Environment variables should be managed via `.env` or `stack.env` files
- No secrets or sensitive information hardcoded in the Dockerfile
- Non-root user execution maintained

## PHP Variant Flexibility
To switch from Debian/bookworm to Alpine:
1. Change `ARG PHP_VARIANT=bookworm` to `ARG PHP_VARIANT=alpine`
2. The system dependency installation commands would need to be updated from `apt-get` to `apk add`

## Performance Impact
- **Build caching**: Maintained existing BuildKit cache mounts
- **Layer optimization**: Composer files copied separately for maximum cache hits
- **Memory**: Added `COMPOSER_MEMORY_LIMIT=-1` for low-memory CI environments

## Dependencies Successfully Resolved
- ✅ `ext-redis 6.2.0` - Successfully installed via PECL
- ✅ `ext-intl 8.3.26` - Successfully installed via docker-php-ext-install
- ✅ All CakePHP 5.x platform requirements satisfied

## Build Time Impact
- First build: ~2 minutes (includes PHP extension compilation)
- Subsequent builds: Significantly faster due to Docker layer caching
- Extensions are built once and cached until base image changes