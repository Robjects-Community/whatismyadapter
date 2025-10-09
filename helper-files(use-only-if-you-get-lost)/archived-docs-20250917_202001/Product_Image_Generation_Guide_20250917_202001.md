# Product Image Generation Feature

This document describes the Product Image Generation feature that has been added to WillowCMS, providing AI-powered image generation specifically for products.

## Overview

The Product Image Generation system automatically creates high-quality product images using AI services when products are added or updated without existing images. This feature helps maintain visual consistency across product listings and reduces the manual effort required for product catalog management.

## Architecture

### Core Components

1. **ProductImageGenerationJob** (`src/Job/ProductImageGenerationJob.php`)
   - Background job that handles AI image generation for individual products
   - Integrates with the existing queue system for asynchronous processing
   - Downloads and saves generated images locally with proper validation

2. **ProductImageDetectionTrait** (`src/Model/Behavior/ProductImageDetectionTrait.php`)
   - Provides methods to detect which products need image generation
   - Includes configurable filtering based on product type, category, and content quality
   - Supports batch querying with pagination for large product catalogs

3. **BatchProductImageGenerationCommand** (`src/Command/BatchProductImageGenerationCommand.php`)
   - CLI command for batch processing multiple products
   - Includes comprehensive filtering options and dry-run capabilities
   - Provides detailed statistics and progress reporting

4. **ImageGenerationService Updates** (`src/Service/Api/ImageGenerationService.php`)
   - Extended with `generateProductImage()` method for product-specific prompts
   - Includes commercial-focused image generation styles
   - Optimized for product photography and e-commerce use cases

5. **ProductsTable Integration** (`src/Model/Table/ProductsTable.php`)
   - Enhanced with ProductImageDetectionTrait
   - QueueableImageBehavior configured for automatic job queuing
   - Seamless integration with existing product workflows

## Features

### Automatic Image Generation
- **Trigger-based**: Automatically queues image generation when products are created or updated
- **Content-aware**: Uses product title, description, and manufacturer information
- **Quality filtering**: Only processes products with sufficient content for meaningful image generation

### Smart Detection
- **Existing Image Check**: Verifies multiple image sources before generating
- **Content Requirements**: Ensures products have adequate information for generation
- **Category Filtering**: Excludes product types that don't benefit from images (services, digital downloads, etc.)
- **Status-based Filtering**: Only processes published and active products

### Batch Processing
- **CLI Command**: Comprehensive batch processing with multiple filtering options
- **Rate Limiting**: Built-in delays between batches to avoid overwhelming AI services
- **Progress Tracking**: Detailed statistics and progress reporting
- **Dry Run Mode**: Test processing without actually generating images

### Flexible Configuration
- **Style Options**: Multiple image styles (commercial, lifestyle, technical, minimalist, artistic)
- **Provider Support**: Works with OpenAI DALL-E, Anthropic, and stock photo APIs
- **Priority Queuing**: Configurable job priorities for queue management
- **Custom Exclusions**: Configurable category and type exclusions

## Usage

### Automatic Processing
The system automatically processes new products when they are created or existing products when key fields are updated:

```php
// Creating a product automatically triggers image generation if needed
$product = $productsTable->newEmptyEntity();
$product = $productsTable->patchEntity($product, [
    'title' => 'Dell Monitor 24-inch',
    'description' => 'High-resolution professional monitor',
    'manufacturer' => 'Dell',
    // ... other fields
]);
$productsTable->save($product); // Image generation job automatically queued
```

### CLI Batch Processing

#### Basic Usage
```bash
# Process all products needing images
bin/cake batch_product_image_generation

# Show statistics only
bin/cake batch_product_image_generation --stats-only

# Dry run to see what would be processed
bin/cake batch_product_image_generation --limit 100 --dry-run
```

#### Advanced Filtering
```bash
# Process only electronics products
bin/cake batch_product_image_generation --category electronics --verbose

# Process specific manufacturer products
bin/cake batch_product_image_generation --manufacturer Dell --batch-size 25

# Force regeneration of existing images
bin/cake batch_product_image_generation --force-regenerate --queue-priority high

# Custom batch configuration
bin/cake batch_product_image_generation --batch-size 10 --delay 3 --limit 50
```

### Programmatic Usage
```php
// Check if a product needs an image
if ($productsTable->productNeedsImage($product)) {
    // Queue image generation
    $queueManager->enqueue(ProductImageGenerationJob::class, [
        'id' => $product->id,
        'title' => $product->title,
        'regenerate' => false
    ]);
}

// Find products needing images
$query = $productsTable->findProductsNeedingImages();
$count = $productsTable->countProductsNeedingImages();

// Get products in batches
$batch = $productsTable->getProductsNeedingImagesBatch(50, 0);
```

## Configuration

### Image Generation Settings
```php
// In app_local.php or config/image_generation.php
return [
    'ImageGeneration' => [
        'enabled' => true,
        'providers' => [
            'openai' => [
                'enabled' => true,
                'api_key' => env('OPENAI_API_KEY'),
                'model' => 'dall-e-3'
            ]
        ],
        'excludedCategories' => [
            'service',
            'software-license',
            'digital-download',
            'consultation'
        ],
        'excludedTypes' => [
            'virtual',
            'digital'
        ]
    ]
];
```

### Queue Configuration
```php
// QueueableImageBehavior configuration in ProductsTable
$this->addBehavior('QueueableImage', [
    'jobClass' => 'ProductImageGenerationJob',
    'triggerFields' => ['title', 'description', 'manufacturer'],
    'imageField' => 'image',
    'queueOnCreate' => true,
    'queueOnUpdate' => true,
    'priority' => 'normal'
]);
```

## Image Generation Process

1. **Trigger Detection**: System detects when a product needs an image
2. **Job Queuing**: ProductImageGenerationJob is queued with product details
3. **Content Analysis**: Job analyzes product information to build appropriate prompt
4. **AI Generation**: Calls configured AI provider with product-specific prompt
5. **Image Download**: Downloads generated image and validates format/quality
6. **File Processing**: Saves image to appropriate directory with unique filename
7. **Database Update**: Creates image record and associates with product
8. **Metadata Storage**: Stores generation metadata including provider and prompt information

## Image Styles

### Commercial (Default)
- Professional product photography style
- Clean white background
- Studio lighting
- Suitable for e-commerce listings

### Lifestyle
- Products shown in real-world contexts
- Natural lighting and settings
- Demonstrates product usage

### Technical
- Engineering-style illustrations
- Detailed product specifications visible
- Clean, technical aesthetic

### Minimalist
- Simple composition
- Clean backgrounds
- Modern aesthetic with plenty of white space

### Artistic
- Creative lighting and composition
- Visually appealing artistic interpretation
- Unique visual perspective

## Quality Assurance

### Image Validation
- **Format Verification**: Ensures downloaded images are valid formats (JPEG, PNG, WebP)
- **Size Validation**: Verifies reasonable dimensions (minimum 100x100 pixels)
- **Content Validation**: Uses getimagesize() to validate image integrity
- **File Size Checks**: Ensures downloaded files meet minimum size requirements

### Content Requirements
- **Title Requirements**: Products must have titles with at least 3 characters
- **Additional Content**: Must have either description (>10 chars) or manufacturer (>2 chars)
- **Placeholder Detection**: Excludes products with placeholder content (Lorem ipsum, etc.)
- **Template Detection**: Excludes test products and templates

### Error Handling
- **Graceful Degradation**: Failed generations don't prevent product saves
- **Retry Logic**: Built-in fallback to stock photo APIs
- **Comprehensive Logging**: All generation attempts and failures are logged
- **Cleanup**: Failed downloads are automatically cleaned up

## Performance Considerations

### Queue Management
- **Asynchronous Processing**: All image generation happens in background jobs
- **Priority Queuing**: Configurable job priorities for queue management
- **Rate Limiting**: Built-in delays to respect API limits
- **Batch Processing**: Efficient batch processing with configurable delays

### Storage Optimization
- **Organized Storage**: Images stored in organized directory structure
- **Unique Naming**: UUID-based naming prevents conflicts
- **Metadata Tracking**: Rich metadata for generated images
- **Cleanup Handling**: Automatic cleanup of failed downloads

### Monitoring
- **Comprehensive Logging**: All generation activities are logged
- **Statistics Tracking**: Built-in statistics for batch operations
- **Error Reporting**: Detailed error reporting and handling
- **Progress Tracking**: Real-time progress updates during batch processing

## Integration Points

### Existing Systems
- **QueueableImageBehavior**: Integrates with existing image processing queue system
- **ImageGenerationService**: Extends existing AI image generation infrastructure  
- **ProductsTable**: Seamlessly integrates with existing product management
- **Admin Interface**: Compatible with existing admin image generation controls

### Database Schema
The system works with the existing database schema and doesn't require additional tables:
- Uses existing `images` table for generated image storage
- Leverages existing product-image associations
- Stores generation metadata in existing `metadata` JSON fields

## Testing

### Unit Tests
Create tests for key components:
```php
// Test product image detection
public function testProductNeedsImage()
{
    $product = $this->Products->newEntity(['title' => 'Test Product']);
    $this->assertTrue($this->Products->productNeedsImage($product));
}

// Test job execution
public function testProductImageGenerationJob()
{
    $job = new ProductImageGenerationJob();
    $result = $job->execute($this->getTestMessage());
    $this->assertEquals(Processor::ACK, $result);
}
```

### Integration Tests
Test the full workflow from product creation to image generation completion.

## Troubleshooting

### Common Issues

#### No Images Generated
- Verify AI service configuration and API keys
- Check that image generation is enabled in settings
- Ensure products meet content requirements
- Review logs for specific error messages

#### Queue Jobs Not Processing
- Verify queue worker is running
- Check job queue configuration
- Ensure database connectivity for queue
- Review queue job logs

#### Poor Image Quality
- Adjust image generation prompts
- Try different AI providers or models  
- Review product content quality
- Consider manual image upload for critical products

### Debugging
```bash
# Check queue status
bin/cake queue status

# View recent logs
tail -f logs/debug.log | grep -i "image generation"

# Test individual product
bin/cake batch_product_image_generation --limit 1 --verbose --dry-run
```

## Future Enhancements

### Planned Features
- **Image Variation Generation**: Generate multiple image options per product
- **Style Templates**: Predefined style templates for different product categories
- **Manual Regeneration**: Admin interface for manually triggering regeneration
- **Bulk Style Changes**: Change generation style for existing images
- **A/B Testing**: Generate multiple variants for performance testing

### Integration Opportunities
- **SEO Optimization**: Automatic alt-text generation based on product details
- **Multi-language Support**: Generate images with localized text overlays
- **Brand Guidelines**: Enforce brand-specific styling and layouts
- **Analytics Integration**: Track image performance and conversion rates

## Conclusion

The Product Image Generation feature provides a comprehensive, scalable solution for maintaining high-quality product images across your catalog. With its flexible configuration, robust error handling, and seamless integration with existing systems, it significantly reduces the manual effort required for product catalog management while maintaining consistent visual quality.

The system is designed to scale with your product catalog and can be easily customized to meet specific business requirements through configuration options and extensible architecture.