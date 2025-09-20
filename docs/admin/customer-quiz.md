# Admin Customer Quiz Configuration

## Overview

The Customer Quiz Configuration interface allows administrators to configure and manage the customer-facing product finder quiz system that helps users discover suitable products through an interactive questionnaire.

## Access

**URL**: `/admin/products/forms-customer-quiz`  
**Navigation**: Admin → Products → Forms Dashboard → Customer Quiz

## Features

### Analytics Dashboard
- **Total Quiz Sessions**: Number of customers who have started the quiz
- **Success Rate**: Percentage of quiz sessions that resulted in product recommendations
- **Average Questions Asked**: Average number of questions per quiz session
- **Average Time**: Average time spent completing the quiz

### Configuration Settings

#### Quiz Behavior
- **Enable Customer Quiz System**: Toggle to enable/disable the entire quiz feature
- **Maximum Results to Show**: Limit the number of product recommendations (1-20)
- **Confidence Threshold**: Minimum confidence percentage required for recommendations (0-100%)

#### Quiz Types
- **Akinator Mode**: AI-powered question system that narrows down products through yes/no questions
  - Maximum questions limit (3-50)
- **Comprehensive Form**: Traditional form-based approach with all questions at once
  - Number of steps (3-20)

#### Result Display
- **Show Alternatives**: Display alternative products even when confident matches are found
- **Display Mode**: Choose between inline results or redirecting to a dedicated page

#### AI Features
- **Enable AI Processing**: Use AI to enhance product matching and recommendations
- **AI Explanations**: Generate explanations for why specific products were recommended

#### Analytics & Tracking
- **Enable Analytics**: Track quiz usage and performance metrics
- **Track Sessions**: Store detailed session data for analysis

#### Performance Settings
- **Session Timeout**: How long quiz sessions remain active (5-3600 minutes)
- **Cache Duration**: How long to cache quiz data and questions (300-86400 seconds)

## Data Storage

Currently, quiz settings are stored in **CakePHP's Cache system** using the key `customer_quiz_settings`. This provides:
- Fast access to configuration data
- Automatic expiration handling
- Easy reset capabilities

**Future Enhancement**: Settings will be migrated to a dedicated Settings database table for:
- Persistent storage across cache clears
- Multi-instance environment support  
- Audit trail of configuration changes

## Reset to Defaults

Click the **"Reset to Defaults"** button to restore all settings to their original values:
- Quiz enabled: `true`
- Max results: `5`
- Confidence threshold: `60%`
- Akinator enabled with max 10 questions
- Comprehensive enabled with 6 steps
- AI features enabled
- Analytics enabled
- 30-minute session timeout
- 1-hour cache duration

## Troubleshooting

### Settings Not Saving
1. Check that the web server has write permissions to the cache directory
2. Verify the cache configuration in `config/app.php`
3. Try clearing the cache: `bin/cake cache clear_all`

### Default Values Not Loading
1. Clear the application cache
2. Check browser console for JavaScript errors
3. Verify the AdminTheme plugin is properly loaded

### Page Not Accessible
1. Ensure you're logged in as an admin user
2. Check that the route exists in `config/routes.php`
3. Verify the controller action `formsCustomerQuiz` exists

## Related Documentation

- [Forms Dashboard](./forms-dashboard.md) - Main forms management interface
- [Quiz Management](./forms-quiz.md) - Admin quiz template management
- [CakePHP 5.x AI Quiz Integration Guide](../../INTEGRATION_GUIDE.md) - Technical implementation details