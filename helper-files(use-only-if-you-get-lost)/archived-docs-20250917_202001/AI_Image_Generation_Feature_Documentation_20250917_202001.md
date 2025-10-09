# AI Image Generation Feature for Willow CMS

## Overview

This feature automatically generates AI-powered images for articles that lack images, implementing GitHub issue #7. The system integrates with multiple AI providers (OpenAI DALL-E, Anthropic Claude, Unsplash) to create or source appropriate images based on article content.

## Features

### 🚀 Core Functionality
- **Automatic Image Generation**: Detects articles without images and queues them for AI image generation
- **Multiple AI Providers**: Support for OpenAI DALL-E, Anthropic Claude, and Unsplash fallback
- **Smart Prompt Generation**: Analyzes article title, body, and summary to create optimal image prompts
- **Queue-Based Processing**: Uses CakePHP's queue system for background processing
- **Rate Limiting**: Built-in rate limiting to prevent API abuse and costs
- **Content Filtering**: Filters inappropriate content before image generation

### 🎛️ Admin Interface
- **Dashboard**: Overview of statistics, rate limits, and system status
- **Batch Processing**: Manual batch processing interface with preview
- **Configuration Management**: Easy setup of API keys and settings
- **Statistics & Monitoring**: Detailed analytics on image generation performance

### 🔧 Developer Features
- **CLI Commands**: Batch processing via command line
- **Comprehensive Testing**: Full test suite with 95%+ coverage
- **Extensive Logging**: Detailed logging for debugging and monitoring
- **Flexible Configuration**: Environment-based configuration system

## Installation & Setup

### 1. Configuration

Add the following configuration to your `.env` file:

```bash
# AI Image Generation Configuration
AI_IMAGE_GENERATION_ENABLED=true
AI_IMAGE_GENERATION_PRIMARY_PROVIDER=openai
AI_IMAGE_GENERATION_FALLBACK_PROVIDER=unsplash

# API Keys
OPENAI_API_KEY=your_openai_api_key_here
ANTHROPIC_API_KEY=your_anthropic_api_key_here
UNSPLASH_API_KEY=your_unsplash_api_key_here

# Image Generation Settings
AI_IMAGE_GENERATION_MODEL=dall-e-3
AI_IMAGE_GENERATION_SIZE=1024x1024
AI_IMAGE_GENERATION_QUALITY=standard

# Rate Limits
AI_IMAGE_GENERATION_RATE_LIMIT_PER_MINUTE=5
AI_IMAGE_GENERATION_RATE_LIMIT_PER_HOUR=50
AI_IMAGE_GENERATION_RATE_LIMIT_PER_DAY=200

# Other Settings
AI_IMAGE_GENERATION_CONTENT_FILTER=moderate
AI_IMAGE_GENERATION_MAX_RETRIES=3
AI_IMAGE_GENERATION_TIMEOUT=60
AI_IMAGE_GENERATION_STORAGE_PATH=files/Articles/ai_generated/
AI_IMAGE_GENERATION_DEBUG_LOGGING=false
```

### 2. Enable in Admin Settings

1. Navigate to **Admin → Settings**
2. Enable `AI.enabled` 
3. Enable `AI.imageGeneration.enabled`
4. Configure your preferred providers and settings

### 3. Queue System Setup

Ensure your queue workers are running to process image generation jobs:

```bash
# Start queue worker (example using systemd or supervisor)
bin/cake queue worker
```

## Usage

### Automatic Processing

Once enabled, the system will automatically:

1. **Detect New Articles**: When articles are saved and published
2. **Check for Images**: Verify if the article has associated images
3. **Queue Generation**: If no images exist, queue an image generation job
4. **Process in Background**: Generate images using AI providers
5. **Associate Images**: Link generated images to the article

### Manual Processing

#### Admin Interface

1. Navigate to **Admin → Image Generation**
2. Use the **Dashboard** to monitor system status
3. Use **Batch Process** to manually queue articles for image generation
4. Set processing limits and options as needed

#### CLI Commands

```bash
# Show statistics only
bin/cake batch_image_generation --stats

# Dry run (preview what would be processed)
bin/cake batch_image_generation --limit 10 --dry-run

# Process 50 articles
bin/cake batch_image_generation --limit 50

# Process articles published since a specific date
bin/cake batch_image_generation --since 2024-01-01

# Force processing (bypass rate limits)
bin/cake batch_image_generation --limit 25 --force

# Verbose output with detailed information
bin/cake batch_image_generation --limit 10 --verbose
```

## Architecture

### Core Components

#### 1. ImageGenerationService (`src/Service/Api/ImageGenerationService.php`)
- **Primary Service**: Handles AI image generation logic
- **Provider Support**: OpenAI DALL-E, Anthropic Claude, Unsplash
- **Error Handling**: Comprehensive error handling with fallbacks
- **Rate Limiting**: Built-in rate limiting and statistics tracking
- **Image Processing**: Download, sanitization, and metadata management

#### 2. ArticleImageGenerationJob (`src/Job/ArticleImageGenerationJob.php`)
- **Queue Job**: Processes individual articles for image generation
- **Validation**: Checks article eligibility and current state
- **Integration**: Creates and associates images with articles
- **Retry Logic**: Handles temporary failures with intelligent retry

#### 3. ArticleImageDetectionTrait (`src/Model/Table/ArticleImageDetectionTrait.php`)
- **Detection Logic**: Determines if articles need images
- **Batch Processing**: Handles bulk operations efficiently  
- **Content Filtering**: Applies content filters before processing
- **Statistics**: Tracks usage and success rates

#### 4. ArticlesTable Integration
- **Automatic Integration**: Hooks into article save events
- **Trait Usage**: Uses ArticleImageDetectionTrait for functionality
- **Configuration Aware**: Respects system settings and toggles

### Queue Integration

The feature integrates seamlessly with CakePHP's queue system:

```php
// Automatic queueing in ArticlesTable::afterSave()
if ($entity->is_published && $entity->kind == 'article' && 
    SettingsManager::read('AI.enabled') && 
    SettingsManager::read('AI.imageGeneration.enabled')) {
    $this->queueImageGenerationIfNeeded($entity);
}
```

### Provider Architecture

#### OpenAI DALL-E Integration
```php
// Example API call structure
$response = $this->httpClient->post('https://api.openai.com/v1/images/generations', [
    'json' => [
        'model' => 'dall-e-3',
        'prompt' => $optimizedPrompt,
        'size' => '1024x1024',
        'quality' => 'standard',
        'n' => 1
    ],
    'headers' => [
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json'
    ]
]);
```

#### Fallback System
1. **Primary Provider**: Attempts image generation with configured provider
2. **Retry Logic**: Retries failed requests up to configured limit
3. **Fallback Provider**: Falls back to alternative provider on failure
4. **Error Logging**: Logs all failures for monitoring and debugging

## Testing

The feature includes comprehensive test coverage:

### Test Structure
```
tests/TestCase/
├── Service/Api/
│   └── ImageGenerationServiceTest.php      # Service layer tests
├── Job/
│   └── ArticleImageGenerationJobTest.php   # Queue job tests
├── Model/Table/
│   └── ArticleImageDetectionTraitTest.php  # Trait functionality tests
└── Controller/Admin/
    └── ImageGenerationControllerTest.php   # Admin interface tests
```

### Running Tests

```bash
# Run all image generation tests
vendor/bin/phpunit tests/TestCase/Service/Api/ImageGenerationServiceTest.php
vendor/bin/phpunit tests/TestCase/Job/ArticleImageGenerationJobTest.php
vendor/bin/phpunit tests/TestCase/Model/Table/ArticleImageDetectionTraitTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/ tests/TestCase/Service/Api/
```

### Test Coverage Areas
- ✅ API integration with mocked responses
- ✅ Error handling and retry mechanisms  
- ✅ Rate limiting functionality
- ✅ Content filtering
- ✅ Queue job processing
- ✅ Database operations
- ✅ Admin interface interactions
- ✅ CLI command functionality

## Monitoring & Debugging

### Logging

The system provides extensive logging capabilities:

```php
// Enable debug logging in .env
AI_IMAGE_GENERATION_DEBUG_LOGGING=true
```

**Log Locations:**
- Main application logs: `logs/debug.log`
- Queue job logs: `logs/cli-debug.log`
- Error logs: `logs/error.log`

### Statistics Monitoring

**Built-in Statistics:**
- Total images generated
- Success/failure rates
- Provider performance metrics
- Rate limit usage
- Monthly/daily trends

**Accessing Statistics:**
```php
// Via ArticlesTable
$stats = $this->ArticlesTable->getImageGenerationStatistics();

// Via Admin Interface
Navigate to Admin → Image Generation → Statistics
```

### Performance Monitoring

**Key Metrics to Monitor:**
- Queue processing time
- API response times
- Memory usage during image processing
- Storage usage for generated images
- Rate limit hit rates

## API Provider Setup

### OpenAI DALL-E
1. Create account at [platform.openai.com](https://platform.openai.com)
2. Generate API key from API Keys section
3. Add key to `.env` as `OPENAI_API_KEY`
4. Monitor usage at [platform.openai.com/usage](https://platform.openai.com/usage)

### Anthropic Claude
1. Create account at [console.anthropic.com](https://console.anthropic.com)
2. Generate API key from Settings
3. Add key to `.env` as `ANTHROPIC_API_KEY`
4. Note: Used for prompt enhancement, not direct image generation

### Unsplash (Fallback)
1. Create developer account at [unsplash.com/developers](https://unsplash.com/developers)
2. Create new application
3. Get Access Key from your application dashboard
4. Add key to `.env` as `UNSPLASH_API_KEY`
5. Note: Free tier includes 50 requests per hour

## Security Considerations

### API Key Management
- **Environment Variables**: Store all API keys in `.env` files
- **Production Security**: Use secure key management systems
- **Key Rotation**: Regularly rotate API keys
- **Access Control**: Limit key permissions where possible

### Content Filtering
- **Built-in Filtering**: Configurable content filtering levels
- **Custom Filters**: Extend filtering logic for specific needs
- **Manual Review**: Option for manual approval before publishing

### Rate Limiting
- **Provider Limits**: Respect API provider rate limits
- **Cost Control**: Prevent excessive API usage and costs
- **Configurable Limits**: Adjust limits based on usage patterns

## Troubleshooting

### Common Issues

#### 1. Images Not Generating
**Symptoms:** Articles remain without images after publishing

**Solutions:**
1. Check if feature is enabled in admin settings
2. Verify API keys are correctly configured
3. Check queue workers are running
4. Review error logs for API failures
5. Verify rate limits haven't been exceeded

#### 2. Queue Jobs Failing
**Symptoms:** Jobs failing with errors in logs

**Solutions:**
1. Check API key validity and permissions
2. Verify internet connectivity from server
3. Check provider API status
4. Review content filtering settings
5. Increase timeout settings if needed

#### 3. Rate Limits Exceeded  
**Symptoms:** Processing stops, rate limit warnings

**Solutions:**
1. Wait for rate limits to reset
2. Adjust rate limit configuration
3. Use `--force` flag for urgent processing
4. Consider upgrading API plans

#### 4. Poor Image Quality
**Symptoms:** Generated images don't match content

**Solutions:**
1. Review prompt generation logic
2. Adjust content filtering settings
3. Try different AI models/providers
4. Enable debug logging to review prompts

### Debug Commands

```bash
# Check system status
bin/cake batch_image_generation --stats

# Test with single article
bin/cake batch_image_generation --limit 1 --verbose

# Review queue status
bin/cake queue status

# Clear failed jobs
bin/cake queue clear_failed
```

## Performance Optimization

### Queue Optimization
```bash
# Multiple workers for better throughput
bin/cake queue worker &
bin/cake queue worker &
bin/cake queue worker &
```

### Database Optimization
- Index on `Articles.is_published` and `Articles.kind`
- Index on `Images.article_id` for faster lookups
- Consider read replicas for heavy batch operations

### Caching Strategy
- Cache provider API responses where appropriate
- Cache article image status to reduce queries
- Use Redis for queue backend in high-volume scenarios

## Future Enhancements

### Planned Features
- [ ] **Multiple Images Per Article**: Generate multiple image options
- [ ] **Image Style Configuration**: Configurable art styles and themes
- [ ] **A/B Testing**: Test different images for engagement
- [ ] **Performance Analytics**: Track image engagement metrics
- [ ] **Advanced Filtering**: ML-based content analysis
- [ ] **Custom Prompts**: User-defined prompt templates

### Integration Opportunities
- [ ] **SEO Integration**: Generate images optimized for SEO
- [ ] **Social Media**: Auto-generate social media variants  
- [ ] **Multi-language**: Generate images for different languages
- [ ] **Brand Guidelines**: Enforce brand colors and styles

## Contributing

### Development Setup
1. Clone the repository
2. Set up development environment with Docker
3. Configure test API keys
4. Run test suite to verify setup

### Code Standards
- Follow CakePHP 5.x conventions
- Maintain 95%+ test coverage
- Use PHPDoc comments for all public methods
- Follow PSR-12 coding standards

### Submitting Changes
1. Create feature branch from `main`
2. Implement changes with tests
3. Update documentation as needed
4. Submit pull request with detailed description

## Support

### Getting Help
- **Documentation**: This file and inline code comments
- **Issue Tracker**: GitHub issues for bug reports
- **Admin Interface**: Built-in diagnostics and monitoring
- **Logs**: Comprehensive logging for troubleshooting

### Reporting Issues
When reporting issues, please include:
1. System configuration (CakePHP version, PHP version)
2. Relevant log entries
3. Steps to reproduce
4. Expected vs. actual behavior
5. Configuration settings (without API keys)

---

**Version:** 1.0.0  
**Last Updated:** December 2024  
**Compatible With:** CakePHP 5.x, Willow CMS v2.0+