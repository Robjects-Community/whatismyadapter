# Settings Configuration Guide

## Overview

This `settings.json` file contains the complete configuration for the Willow CMS (WhatIsMyAdaptor) application. It includes 116 settings organized into 18 categories that control various aspects of the application behavior.

## File Purpose

The `settings.json` file serves multiple purposes:

1. **Default Data**: Provides baseline settings for fresh installations
2. **Backup/Restore**: Can be used to backup and restore system configuration
3. **Import/Export**: Facilitates sharing configurations between environments
4. **Documentation**: Serves as a reference for available configuration options

## Usage

### Importing Settings

Use the CakePHP command to import settings:

```bash
cd cakephp
bin/cake default_data_import Settings
```

This will:
- Clear existing settings in the database
- Import all 116 settings from `default_data/settings.json`
- Set default values for all configuration options

### Exporting Settings

To export current settings from the database:

```bash
cd cakephp
bin/cake default_data_export Settings
```

This will create/update the `default_data/settings.json` file with current database values.

## Settings Categories

### 1. AI (14 settings)
Controls artificial intelligence features including:
- Article summaries and translations
- SEO metadata generation
- Tag auto-generation
- Image analysis
- Cost limits and metrics

**Key Settings:**
- `enabled`: Master switch for all AI features
- `hourlyLimit`: API call rate limiting (default: 100/hour)
- `dailyCostLimit`: Budget control (default: $2.50/day)

### 2. Anthropic (1 setting)
- `apiKey`: Your Anthropic API key for Claude AI features

### 3. Blog (1 setting)
- `articleDisplayMode`: Show summary or body text on blog index

### 4. Comments (2 settings)
- `articlesEnabled`: Enable comments on articles
- `pagesEnabled`: Enable comments on pages

### 5. Editing (1 setting)
- `editor`: Choose between Trumbowyg or TinyMCE editors

### 6. Email (1 setting)
- `reply_email`: Default reply-to address

### 7. Google (4 settings)
- `apiKey`: Google API key
- `tagManagerHead`: Google Tag Manager script
- `tagManagerBody`: Google Tag Manager noscript fallback
- `tagManagerId`: GTM container ID

### 8. HomepageFeeds (13 settings)
Controls what content appears on the homepage:
- Featured/latest articles
- Latest products
- Image galleries
- Popular tags
- Social links
- Development info

### 9. ImageSizes (8 settings)
Defines thumbnail sizes:
- teeny (50px)
- tiny (100px)
- small (200px)
- medium (300px)
- large (600px)
- extra_large (1200px)
- massive (2000px)

### 10. PagesAndArticles (1 setting)
- `additionalImages`: Enable multiple image uploads

### 11. Products (21 settings)
Comprehensive product management configuration:

**Core Settings:**
- `enabled`: Master switch for products system
- `userSubmissions`: Allow user-submitted products

**Form Settings:**
- `enable_public_submissions`: Public form access
- `required_fields`: Mandatory form fields
- `allowed_file_types`: Image upload restrictions
- `max_file_size`: Upload size limit (MB)

**Verification:**
- `aiVerificationEnabled`: AI-powered validation
- `peerVerificationEnabled`: Community verification
- `minVerificationScore`: Auto-approval threshold

**Quiz System:**
- `quiz_enabled`: Enable adapter finder quiz
- `quiz_config_json`: Quiz configuration
- `quiz_results_page`: Results redirect page

### 12. RateLimit (7 settings)
API rate limiting for different endpoints:
- Login attempts (5/minute)
- Admin area (40/minute)
- Password reset (3/5 minutes)
- Registration (5/5 minutes)

### 13. SEO (4 settings)
- `siteName`: Website name
- `siteMetaDescription`: Default meta description
- `siteMetaKeywords`: Default keywords
- `robotsTemplate`: robots.txt content

### 14. Security (6 settings)
Security and proxy configuration:
- `trustedProxies`: Trusted proxy IP list
- `blockOnNoIp`: Block when IP unavailable
- `enableRateLimiting`: Master rate limit switch
- `suspiciousRequestThreshold`: Block threshold
- `suspiciousWindowHours`: Detection window
- `suspiciousBlockHours`: Block duration

### 15. SitePages (4 settings)
Menu and page configuration:
- `privacyPolicy`: Privacy policy page
- `footerMenuShow`: Footer menu mode
- `mainMenuShow`: Main menu mode
- `mainTagMenuShow`: Tag menu mode

### 16. Translations (25 settings)
Enable/disable translation for 25 languages:
- European: German, French, Spanish, Italian, etc.
- Nordic: Swedish, Danish, Finnish, Norwegian
- Eastern European: Polish, Czech, Hungarian, etc.
- Other: Greek, Turkish, etc.

### 17. Users (1 setting)
- `selfRegistrationEnabled`: Allow new user signups

### 18. i18n (2 settings)
Internationalization configuration:
- `locale`: Default admin locale (en_GB)
- `provider`: Translation provider (Google/Anthropic)

## Setting Structure

Each setting has the following fields:

```json
{
    "ordering": "1",           // Display order within category
    "category": "AI",          // Grouping category
    "key_name": "enabled",     // Unique identifier
    "value": "0",              // Default value
    "value_type": "bool",      // Data type (bool, text, numeric, textarea, select, select-page)
    "value_obscure": "0",      // Whether to hide value in UI (for sensitive data)
    "description": "...",      // Human-readable description
    "data": null,              // Additional data (e.g., select options in JSON)
    "column_width": "2"        // UI column width (1-12)
}
```

## Value Types

- **bool**: Boolean (0 or 1)
- **text**: Short text string
- **numeric**: Number (integer or decimal)
- **textarea**: Multi-line text
- **select**: Dropdown with predefined options (stored in `data` field)
- **select-page**: Dropdown populated with pages from database

## Best Practices

1. **Backup Before Changes**: Always export settings before making bulk changes
2. **Version Control**: Consider tracking this file in version control for configuration history
3. **Environment-Specific**: Maintain separate settings files for dev/staging/production
4. **API Keys**: Never commit actual API keys - use environment variables or separate secure storage
5. **Testing**: Test import/export workflow in development before production use

## Validation

The settings file has been validated for:
- ✓ JSON syntax correctness
- ✓ Required fields presence
- ✓ Data type compatibility
- ✓ No duplicate keys within categories
- ✓ Complete descriptions
- ✓ Proper select field data

## Maintenance

When adding new settings:

1. Add them to the appropriate migration file
2. Re-export settings using `bin/cake default_data_export Settings`
3. Update this documentation if adding new categories
4. Validate the JSON before committing

## Support

For issues or questions:
- Check migration files in `cakephp/config/Migrations/`
- Review SETTINGS_README.md for detailed setting descriptions
- Consult the DeveloperGuide.md for integration details

## Version Information

- **Total Settings**: 116
- **Categories**: 18
- **Last Updated**: 2025-10-07
- **Format Version**: 1.0
