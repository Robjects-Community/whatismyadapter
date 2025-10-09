# WillowCMS Email Templates

This document describes the email templates configured in the WillowCMS system.

## Overview

WillowCMS includes a comprehensive email template system that allows administrators to create, manage, and send various types of emails to users. The system supports both HTML and plain text formats, and includes variable substitution for personalization.

## Available Email Templates

### 1. Welcome Email for New Users
- **Template Identifier**: `welcome_email`
- **Subject**: Welcome to WillowCMS - Get Started Today!
- **Purpose**: Sent to new users when they register or create an account
- **Variables**: `{username}`, `{email}`
- **Features**: Introduces new users to the platform and provides next steps

### 2. Password Reset Request
- **Template Identifier**: `password_reset`
- **Subject**: Reset Your WillowCMS Password
- **Purpose**: Sent when users request a password reset
- **Variables**: `{username}`, `{email}`, `{reset_password_link}`
- **Features**: Secure password reset with expiring links

### 3. Email Address Verification
- **Template Identifier**: `email_verification`
- **Subject**: Please Verify Your Email Address
- **Purpose**: Email verification for new accounts
- **Variables**: `{username}`, `{email}`, `{confirm_email_link}`
- **Features**: Account verification with secure links

### 4. Monthly Newsletter
- **Template Identifier**: `monthly_newsletter`
- **Subject**: WillowCMS Monthly Update - Latest Features & News
- **Purpose**: Regular updates and news to engaged users
- **Variables**: `{username}`
- **Features**: Professional newsletter design with latest updates

### 5. Account Security Alert
- **Template Identifier**: `security_alert`
- **Subject**: Security Alert: Unusual Activity Detected
- **Purpose**: Notify users of suspicious account activity
- **Variables**: `{username}`, `{email}`, `{reset_password_link}`
- **Features**: Security-focused design with recommended actions

## Template Variables

The email templates support various variables that are automatically replaced when emails are sent:

- `{username}` - The recipient's username
- `{email}` - The recipient's email address
- `{confirm_email_link}` - Link for email confirmation
- `{reset_password_link}` - Link for password reset

## Administration

### Accessing Email Templates

1. Navigate to the admin panel: `http://localhost:8080/admin`
2. Go to "Email Templates" section
3. View, edit, or create new templates

### Creating New Templates

1. Click "New Email Template" (available in debug mode)
2. Fill in the required fields:
   - Template Identifier (optional but recommended)
   - Name (display name)
   - Subject line
   - HTML body content
   - Plain text body (auto-generated from HTML)

### Sending Test Emails

1. Go to "Send Email" in the email templates section
2. Select a template and recipient
3. The system will automatically substitute variables

## Technical Details

### Database Structure

Email templates are stored in the `email_templates` table with the following fields:
- `id` (UUID) - Primary key
- `template_identifier` - Unique identifier for code reference
- `name` - Display name for administrators
- `subject` - Email subject line
- `body_html` - HTML content
- `body_plain` - Plain text content (auto-generated)
- `created` - Creation timestamp
- `modified` - Last modification timestamp

### File Locations

- Controller: `src/Controller/Admin/EmailTemplatesController.php`
- Model: `src/Model/Table/EmailTemplatesTable.php`
- Entity: `src/Model/Entity/EmailTemplate.php`
- Admin Templates: `plugins/AdminTheme/templates/Admin/EmailTemplates/`

### Variable Substitution

Variables are processed in the `prepareEmailVariables()` method in the controller. The system automatically generates links for password reset and email confirmation when these variables are detected in the template content.

## Customization

Templates can be customized by:
1. Editing existing templates through the admin interface
2. Creating new templates with custom variables
3. Modifying the controller to support additional variables
4. Styling templates with CSS (inline styles recommended for email compatibility)

## Best Practices

1. **Use inline CSS** for email styling (better client compatibility)
2. **Test templates** across different email clients
3. **Keep plain text versions** for accessibility
4. **Use meaningful template identifiers** for code organization
5. **Regular backups** of custom templates
6. **Security considerations** when adding new variables

## Troubleshooting

### Common Issues

1. **Templates not displaying**: Check file permissions and paths
2. **Variables not substituting**: Verify variable names match exactly
3. **Styling issues**: Use inline CSS and test across email clients
4. **Delivery problems**: Check email server configuration

### Debugging

- Enable debug mode to access template creation/deletion
- Check application logs for email sending errors
- Use the built-in email testing feature to verify templates

## Support

For additional help with email templates:
1. Check the CakePHP documentation for email handling
2. Review the WillowCMS documentation
3. Test templates thoroughly before production use