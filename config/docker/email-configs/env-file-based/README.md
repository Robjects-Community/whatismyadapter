# Environment File-Based Email Configuration

This directory contains docker-compose.yml files that use the `env_file` directive to load variables from `./cakephp/config/.env`.

## Files

- `docker-compose-env-file.yml` - Earlier version using env_file approach
- `docker-compose-env-file-gmail-[timestamp].yml` - Current version with Gmail SMTP via env_file

## Characteristics

These configurations feature:
- Variables loaded from `./cakephp/config/.env`
- Credentials kept out of docker-compose.yml
- Better security (credentials not in version control)
- Clean separation of configuration and deployment

## Structure
```yaml
willowcms:
  env_file:
    - ./cakephp/config/.env
  environment:
    # Only essential Docker networking overrides
    - DB_HOST=mysql
    - TEST_DB_HOST=mysql
    - REDIS_HOST=willowcms
```

## Environment File

Variables are defined in `./cakephp/config/.env`:
```bash
EMAIL_HOST=smtp.gmail.com
EMAIL_USERNAME=mike.mail.tester@gmail.com
EMAIL_PASSWORD=cvvpbxlnpaxguthd
# ... other variables
```

## Advantages

- ✅ Credentials hidden from version control
- ✅ Easy to switch between environments
- ✅ Centralized configuration management
- ✅ Better security practices
- ✅ Cleaner docker-compose.yml file

This is the **recommended approach** for production deployments.