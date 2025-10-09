# WillowCMS Email Configuration

This document explains how to configure email functionality in WillowCMS, including Gmail SMTP integration.

## Overview

WillowCMS supports multiple email transports:
- **Gmail SMTP**: For production email delivery via Gmail
- **Mailpit**: For local development email testing

## Configuration

### Environment Variables

The following environment variables control email behavior:

| Variable | Default | Description |
|----------|---------|-------------|
| `EMAIL_DEFAULT_TRANSPORT` | `gmail` | Which email transport to use (`gmail` or `mailpit`) |
| `EMAIL_HOST` | `smtp.gmail.com` | SMTP server hostname |
| `EMAIL_PORT` | `587` | SMTP server port |
| `EMAIL_TLS` | `true` | Enable TLS encryption |
| `EMAIL_USERNAME` | - | Gmail username |
| `EMAIL_PASSWORD` | - | Gmail app-specific password |
| `EMAIL_FROM_ADDRESS` | - | Default sender email address |
| `EMAIL_FROM_NAME` | `WillowCMS` | Default sender name |
| `MAILPIT_HOST` | `mailpit` | Mailpit server hostname (for local development) |
| `MAILPIT_PORT` | `1025` | Mailpit SMTP port |

### Gmail SMTP Setup

1. **Enable 2FA**: Your Gmail account must have 2-factor authentication enabled.

2. **Generate App Password**: 
   - Go to Google Account Settings > Security > 2-Step Verification
   - Generate an "App password" for "Mail"
   - Copy the 16-character password (remove spaces)

3. **Configure Environment**: Set these variables in `cakephp/config/.env`:
   ```bash
   EMAIL_DEFAULT_TRANSPORT=gmail
   EMAIL_HOST=smtp.gmail.com
   EMAIL_PORT=587
   EMAIL_TLS=true
   EMAIL_USERNAME=your-email@gmail.com
   EMAIL_PASSWORD=your-app-password-no-spaces
   EMAIL_FROM_ADDRESS=your-email@gmail.com
   EMAIL_FROM_NAME=WillowCMS
   ```

   **Note**: The containers automatically load variables from `./cakephp/config/.env` using the `env_file` directive in docker-compose.yml.

### Local Development Setup

For local development, you can use Mailpit instead of Gmail:

1. **Switch to Mailpit**: Set `EMAIL_DEFAULT_TRANSPORT=mailpit` in `docker-compose.yml`

2. **Access Mailpit Web UI**: Visit http://localhost:8025 to view sent emails

## Testing Email Functionality

Use the built-in test command to verify your configuration:

```bash
# Send test email to default address (mike.mail.tester@gmail.com)
docker compose exec -T willowcms bash -lc 'cd /var/www/html && ./bin/cake send_test_email'

# Send test email to specific address
docker compose exec -T willowcms bash -lc 'cd /var/www/html && ./bin/cake send_test_email your-email@example.com'
```

## Switching Between Transports

To switch between Gmail and Mailpit:

1. **To Gmail**: Change `EMAIL_DEFAULT_TRANSPORT=gmail` in docker-compose.yml
2. **To Mailpit**: Change `EMAIL_DEFAULT_TRANSPORT=mailpit` in docker-compose.yml
3. **Restart containers**: `docker compose down && docker compose up -d`

## Troubleshooting

### Common Issues

1. **"Username and Password not accepted"**:
   - Verify 2FA is enabled on Gmail account
   - Ensure app password is correct and has no spaces
   - Check EMAIL_USERNAME matches Gmail address exactly

2. **Connection timeouts**:
   - Verify firewall allows outbound connections to smtp.gmail.com:587
   - Check container DNS resolution

3. **TLS/SSL errors**:
   - Ensure EMAIL_TLS=true for Gmail SMTP
   - Verify system time is synchronized

### Logs

Check CakePHP logs for email errors:

```bash
docker compose exec -T willowcms bash -lc 'cd /var/www/html && tail -f logs/error.log'
```

## File Locations

- **Configuration**: `/cakephp/config/app.php` (EmailTransport and Email sections)
- **Environment Variables**: `./cakephp/config/.env` (loaded via env_file in docker-compose.yml)
- **Docker Compose**: `docker-compose.yml` (uses env_file directive)
- **Test Command**: `/cakephp/src/Command/SendTestEmailCommand.php`
- **Logs**: `/cakephp/logs/error.log`
- **Backups**: `./helper-files(use-only-if-you-get-lost)/docker-backups/` (organized by configuration type)

## Security Notes

- Never commit real credentials to version control
- Use app-specific passwords, not your main Gmail password
- Regularly rotate app passwords
- Keep .env files out of version control