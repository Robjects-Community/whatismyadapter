# Hardcoded Email Variables

This directory contains docker-compose.yml files where email configuration variables were directly embedded in the environment section.

## Files

- `docker-compose-gmail-hardcoded-20250917_111647.yml` - Gmail SMTP configuration with all variables hardcoded in docker-compose.yml

## Characteristics

These configurations include:
- Gmail SMTP settings directly in environment variables
- Credentials visible in the docker-compose file
- Less secure approach (credentials in version control)
- Easy to understand but not recommended for production

## Example Structure
```yaml
environment:
  - EMAIL_HOST=smtp.gmail.com
  - EMAIL_USERNAME=mike.mail.tester@gmail.com
  - EMAIL_PASSWORD=cvvpbxlnpaxguthd
  # ... other hardcoded variables
```

## Migration Path

These files were replaced with the env_file approach for better security and maintainability.