<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\AbstractMigration;

class AddGalleryAiSettings extends AbstractMigration
{
    public function change(): void
    {
        $settings = [
            [
                'id' => Text::uuid(),
                'ordering' => 200,
                'category' => 'AI',
                'key_name' => 'gallerySEO',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable AI-powered SEO field generation for image galleries.',
                'data' => null,
                'column_width' => 2,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 201,
                'category' => 'AI',
                'key_name' => 'galleryTranslations',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable automatic translation of image galleries to all enabled languages.',
                'data' => null,
                'column_width' => 2,
            ],
        ];

        $this->table('settings')->insert($settings)->save();

        // Insert AI prompt for gallery SEO analysis
        $aiprompt = [
            'id' => Text::uuid(),
            'task_type' => 'gallery_seo_analysis',
            'system_prompt' => 'You are a gallery SEO optimization bot. Generate SEO metadata for image galleries based on the provided gallery name and description. Return ONLY a JSON object with these exact fields:

{
  "meta_title": "string, max 255 chars, concise gallery topic summary",
  "meta_description": "string, max 300 chars, SEO summary describing gallery content",
  "meta_keywords": "space-separated keywords, max 20 words, related to gallery theme",
  "facebook_description": "string, max 300 chars, engaging tone for social sharing",
  "linkedin_description": "string, max 700 chars, professional tone emphasizing visual content", 
  "twitter_description": "string, max 280 chars, concise and catchy for quick sharing",
  "instagram_description": "string, max 1500 chars, creative tone perfect for visual platform"
}

IMPORTANT:
- Focus on gallery name and description content
- Emphasize the gallery\'s unique theme or collection purpose
- Return ONLY valid JSON with no additional text
- Keep within character limits
- Ensure proper JSON escaping',
            'model' => 'claude-3-5-sonnet-20241022',
            'max_tokens' => 8000,
            'temperature' => 0,
        ];

        $this->table('aiprompts')->insert($aiprompt)->save();
    }
}
