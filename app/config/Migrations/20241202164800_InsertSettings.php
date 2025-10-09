<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\AbstractMigration;

class InsertSettings extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $this->table('settings')
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 7,
                'category' => 'AI',
                'key_name' => 'articleSummaries',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Automatically generate concise and compelling summaries for your articles and pages. When enabled, the system will analyze the content and create a brief synopsis that captures the key points. These summaries will appear on the article index page and other areas where a short overview is preferable to displaying the full text.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 6,
                'category' => 'ImageSizes',
                'key_name' => 'tiny',
                'value' => '100',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'The width for the tiny image size.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 4,
                'category' => 'AI',
                'key_name' => 'articleSEO',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Optimize your articles and pages for search engines and social media by automatically generating SEO metadata. When enabled, the system will create a meta title, meta description, meta keywords, and tailored descriptions for Facebook, LinkedIn, Instagram, and Twitter.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 5,
                'category' => 'Translations',
                'key_name' => 'el_GR',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Greek',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 4,
                'category' => 'ImageSizes',
                'key_name' => 'medium',
                'value' => '300',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'The width for the medium image size.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 6,
                'category' => 'AI',
                'key_name' => 'articleTags',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Automatically generate relevant tags for your articles and pages based on their content. When you save an article or page, the system will analyze the text and create tags that best represent the main topics and keywords. These tags will then be automatically linked to the corresponding article or page, making it easier for readers to find related content on your website.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 0,
                'category' => 'PagesAndArticles',
                'key_name' => 'additionalImages',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable additional image uploads on your Articles and Pages.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 7,
                'category' => 'ImageSizes',
                'key_name' => 'teeny',
                'value' => '50',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'The width for the teeny image size.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 20,
                'category' => 'Translations',
                'key_name' => 'ru_RU',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Russian',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'Users',
                'key_name' => 'registrationEnabled',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Turn this on to enable users to register accounts on the site.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'Translations',
                'key_name' => 'bg_BG',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Bulgarian',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'Comments',
                'key_name' => 'articlesEnabled',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Turn this on to enable logged in users to comment on your articles.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 6,
                'category' => 'SEO',
                'key_name' => 'siteName',
                'value' => 'Willow CMS',
                'value_type' => 'text',
                'value_obscure' => false,
                'description' => 'This field represents the official name of your website. It is typically displayed in the title bar of web browsers and is used in various places throughout the site to identify your brand or organization. Ensure that the name is concise and accurately reflects the purpose or identity of your site.',
                'data' => null,
                'column_width' => 4,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 0,
                'category' => 'i18n',
                'key_name' => 'locale',
                'value' => 'en_GB',
                'value_type' => 'select',
                'value_obscure' => false,
                'description' => 'This setting determines the default language for the admin area, allowing users to select languages such as French or German.',
                'data' => "{\n  \"de_DE\": \"German\",\n  \"fr_FR\": \"French\",\n  \"es_ES\": \"Spanish\",\n  \"it_IT\": \"Italian\",\n  \"pt_PT\": \"Portuguese\",\n  \"nl_NL\": \"Dutch\",\n  \"pl_PL\": \"Polish\",\n  \"ru_RU\": \"Russian\",\n  \"sv_SE\": \"Swedish\",\n  \"da_DK\": \"Danish\",\n  \"fi_FI\": \"Finnish\",\n  \"no_NO\": \"Norwegian\",\n  \"el_GR\": \"Greek\",\n  \"tr_TR\": \"Turkish\",\n  \"cs_CZ\": \"Czech\",\n  \"hu_HU\": \"Hungarian\",\n  \"ro_RO\": \"Romanian\",\n  \"sk_SK\": \"Slovak\",\n  \"sl_SI\": \"Slovenian\",\n  \"bg_BG\": \"Bulgarian\",\n  \"hr_HR\": \"Croatian\",\n  \"et_EE\": \"Estonian\",\n  \"lv_LV\": \"Latvian\",\n  \"lt_LT\": \"Lithuanian\",\n  \"uk_UA\": \"Ukrainian\",\n  \"en_GB\": \"British English\"\n}",
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'RateLimit',
                'key_name' => 'numberOfSeconds',
                'value' => '60',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'This field complements the "Rate Limit: Number Of Requests" setting by specifying the time window in which the request limit is enforced. It determines the duration, in seconds, for which the rate limit is applied. For example, if you set the "Rate Limit: Number Of Requests" to 100 and the "Rate Limit: Number Of Seconds" to 60, it means that an IP address can make a maximum of 100 requests within a 60-second window. If an IP address exceeds this limit within the specified time frame, they will be blocked for a certain period to prevent further requests and protect your server from potential abuse or overload.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'Google',
                'key_name' => 'tagManagerHead',
                'value' => '<!-- Google tag (gtag.js) -->' . PHP_EOL .
                            '<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>' . PHP_EOL .
                            '<script>' . PHP_EOL .
                            '  window.dataLayer = window.dataLayer || [];' . PHP_EOL .
                            '  function gtag(){dataLayer.push(arguments);}' . PHP_EOL .
                            '  gtag(\'js\', new Date());' . PHP_EOL .
                            '  gtag(\'config\', \'G-XXXXXXXXXX\');' . PHP_EOL .
                            '</script>',
                'value_type' => 'textarea',
                'value_obscure' => true,
                'description' => 'The Google Tag Manager <head> tag is a JavaScript snippet placed in the <head> section that loads the GTM container and enables tag management without direct code modifications.',
                'data' => null,
                'column_width' => 8,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'SEO',
                'key_name' => 'siteMetakeywords',
                'value' => 'Default site meta keywords',
                'value_type' => 'textarea',
                'value_obscure' => false,
                'description' => 'Metakeywords are a set of keywords or phrases that describe the content of your website. These keywords are used by search engines to index your site and improve its visibility in search results. Enter relevant and specific keywords that accurately represent the topics and themes of your site content.',
                'data' => null,
                'column_width' => 4,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 13,
                'category' => 'Translations',
                'key_name' => 'lt_LT',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Lithuanian',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 0,
                'category' => 'i18n',
                'key_name' => 'provider',
                'value' => 'google',
                'value_type' => 'select',
                'value_obscure' => false,
                'description' => 'This setting is used for updating the built-in translations for the Willow CMS interface. Options include Google or Anthropic, with Google generally providing better translations. For auto translation of your website content, see the Translations section to enable languages.',
                'data' => "{\n  \"google\": \"Google\",\n  \"anthropic\": \"Anthropic\"\n}",
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 3,
                'category' => 'AI',
                'key_name' => 'tagTranslations',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Automatically translate your tags into any of the 25 languages enabled in the translations settings. When you publish a page or article, the system will generate high-quality translations.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 3,
                'category' => 'SEO',
                'key_name' => 'siteStrapline',
                'value' => 'Welcome to Willow CMS',
                'value_type' => 'textarea',
                'value_obscure' => false,
                'description' => 'The site strapline is a brief, catchy phrase or slogan that complements your site name. It provides additional context or a memorable tagline that encapsulates the essence of your website. This strapline is often displayed alongside the site name in headers or footers.',
                'data' => null,
                'column_width' => 4,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 3,
                'category' => 'Translations',
                'key_name' => 'da_DK',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Danish',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 2,
                'category' => 'Translations',
                'key_name' => 'cs_CZ',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Czech',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 8,
                'category' => 'Translations',
                'key_name' => 'fi_FI',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Finnish',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 2,
                'category' => 'SEO',
                'key_name' => 'siteMetaDescription',
                'value' => 'Default site meta description',
                'value_type' => 'textarea',
                'value_obscure' => false,
                'description' => 'The site meta description is a brief summary of your website\'s content and purpose. It appears in search engine results below the page title and URL, providing potential visitors with a snapshot of what your site offers. Craft a compelling and informative description to encourage clicks and improve search engine optimization (SEO).',
                'data' => null,
                'column_width' => 4,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 6,
                'category' => 'Translations',
                'key_name' => 'es_ES',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Spanish',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 21,
                'category' => 'Translations',
                'key_name' => 'sk_SK',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Slovak',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 10,
                'category' => 'Translations',
                'key_name' => 'hr_HR',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Croatian',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 17,
                'category' => 'Translations',
                'key_name' => 'pl_PL',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Polish',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 23,
                'category' => 'Translations',
                'key_name' => 'sv_SE',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Swedish',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 3,
                'category' => 'Google',
                'key_name' => 'translateApiKey',
                'value' => 'your-api-key-here',
                'value_type' => 'text',
                'value_obscure' => true,
                'description' => 'This field is used to store your Google API key, which is required to access and utilize the Google Cloud Translation API. The Google Cloud Translation API allows you to integrate machine translation capabilities into your content management system, enabling automatic translation of your website content into different languages.',
                'data' => null,
                'column_width' => 12,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 4,
                'category' => 'Google',
                'key_name' => 'youtubeApiKey',
                'value' => 'your-api-key-here',
                'value_type' => 'text',
                'value_obscure' => true,
                'description' => 'This field is used to store your YouTube API key, which is required to access your videos to insert into post and page content.',
                'data' => null,
                'column_width' => 12,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 5,
                'category' => 'Google',
                'key_name' => 'youtubeChannelId',
                'value' => 'your-api-key-here',
                'value_type' => 'text',
                'value_obscure' => true,
                'description' => 'This field is used to store your YouTube Channel ID, which is required to allow you to filter videos to just your own.',
                'data' => null,
                'column_width' => 12,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 2,
                'category' => 'AI',
                'key_name' => 'articleTranslations',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Automatically translate your articles into any of the 25 languages enabled in the translations settings. When you publish a page or article, the system will generate high-quality translations.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 0,
                'category' => 'Email',
                'key_name' => 'reply_email',
                'value' => 'noreply@example.com',
                'value_type' => 'text',
                'value_obscure' => false,
                'description' => 'The "Reply Email" field allows you to specify the email address that will be used as the "Reply-To" address for outgoing emails sent from Willow CMS. When a recipient receives an email from your website and chooses to reply to it, their response will be directed to the email address specified in this field.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'ImageSizes',
                'key_name' => 'massive',
                'value' => '800',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'The width for the massive image size.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 5,
                'category' => 'ImageSizes',
                'key_name' => 'small',
                'value' => '200',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'The width for the small image size.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 8,
                'category' => 'ImageSizes',
                'key_name' => 'micro',
                'value' => '10',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'The width for the micro image size.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'AI',
                'key_name' => 'enabled',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Harness the power of artificial intelligence to enhance your content creation process. By enabling AI features, you gain access to a range of powerful tools, such as automatic article summarization, SEO metadata generation, and multilingual translation.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 5,
                'category' => 'AI',
                'key_name' => 'tagSEO',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Optimize your tags for search engines and social media by automatically generating SEO metadata. When enabled, the system will create a meta title, meta description, meta keywords, and tailored descriptions for Facebook, LinkedIn, Instagram, and Twitter.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 3,
                'category' => 'ImageSizes',
                'key_name' => 'large',
                'value' => '400',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'The width for the large image size.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 14,
                'category' => 'Translations',
                'key_name' => 'lv_LV',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Latvian',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 22,
                'category' => 'Translations',
                'key_name' => 'sl_SI',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Slovenian',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 0,
                'category' => 'Anthropic',
                'key_name' => 'apiKey',
                'value' => 'your-api-key-here',
                'value_type' => 'text',
                'value_obscure' => true,
                'description' => 'This field is used to store your Anthropic API key, which grants access to a range of AI-powered features and services provided by Anthropic. These features are designed to enhance your content management system and streamline various tasks. Some of the key functionalities include auto tagging, SEO text generation, image alt text & keyword generation.',
                'data' => null,
                'column_width' => 12,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 12,
                'category' => 'Translations',
                'key_name' => 'it_IT',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Italian',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 25,
                'category' => 'Translations',
                'key_name' => 'uk_UA',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Ukrainian',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 16,
                'category' => 'Translations',
                'key_name' => 'no_NO',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Norwegian',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 11,
                'category' => 'Translations',
                'key_name' => 'hu_HU',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable translations in Hungarian',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 2,
                'category' => 'SitePages',
                'key_name' => 'mainMenuShow',
                'value' => "root",
                'value_type' => "select",
                "value_obscure" => false,
                "description" => "Should the main menu show all root pages or only selected pages?",
                "data" => "{\n  \"root\": \"Top Level Pages\",\n  \"selected\": \"Selected Pages\"\n}",
                "column_width" => 2
            ])
            ->insert([
                "id" => Text::uuid(),
                "ordering" => 2,
                "category" => "ImageSizes",
                "key_name" => "extraLarge",
                "value" => "500",
                "value_type" => "numeric",
                "value_obscure" => false,
                "description" => "The width for the extra-large image size.",
                "data" => null,
                "column_width" => 2
            ])
            ->insert([
                "id" => Text::uuid(),
                "ordering" => 24,
                "category" => "Translations",
                "key_name" => "tr_TR",
                "value" => "0",
                "value_type" => "bool",
                "value_obscure" => false,
                "description" => "Enable translations in Turkish",
                "data" => null,
                "column_width" => 2
            ])
            ->insert([
                "id" => Text::uuid(),
                "ordering" => 19,
                "category" => "Translations",
                "key_name" => "ro_RO",
                "value" => "0",
                "value_type" => "bool",
                "value_obscure" => false,
                "description" => "Enable translations in Romanian",
                "data" => null,
                "column_width" => 2
            ])
            ->insert([
                "id" => Text::uuid(),
                "ordering" => 7,
                "category" => "Translations",
                "key_name" => "et_EE",
                "value" => "0",
                "value_type" => "bool",
                "value_obscure" => false,
                "description" => "Enable translations in Estonian",
                "data" => null,
                "column_width" => 2
            ])
            ->insert([
                "id" => Text::uuid(),
                "ordering" => 4,
                "category" => "Translations",
                "key_name" => "de_DE",
                "value" => "0",
                "value_type" => "bool",
                "value_obscure" => false,
                "description" => "Enable translations in German",
                "data" => null,
                "column_width" => 2
            ])
            ->insert([
                "id" => Text::uuid(),
                "ordering" => 9,
                "category" => "Translations",
                "key_name" => "fr_FR",
                "value" => "0",
                "value_type" => "bool",
                "value_obscure" => false,
                "description" => "Enable translations in French",
                "data" => null,
                "column_width" => 2
            ])
            ->insert([
                "id" => Text::uuid(),
                "ordering" => 15,
                "category" => "Translations",
                "key_name" => "nl_NL",
                "value" => "0",
                "value_type" => "bool",
                "value_obscure" => false,
                "description" => "Enable translations in Dutch",
                "data" => null,
                "column_width" => 2
            ])
            ->insert([
                "id" => Text::uuid(),
                "ordering" => 2,
                "category" => "Comments",
                "key_name" => "pagesEnabled",
                "value" => "0",
                "value_type" => "bool",
                "value_obscure" => false,
                "description" => "Turn this on to enable logged in users to comment on your pages.",
                "data" => null,
                "column_width" => 2
            ])
            ->insert([
                "id" => Text::uuid(),
                "ordering" => 1,
                "category" => "SitePages",
                "key_name" => "privacyPolicy",
                "value" => "None",
                "value_type" => "select-page",
                "value_obscure" => false,
                "description" => "Choose which page to show as your site Privacy Policy.",
                "data" => "",
                "column_width" => 2
            ])
            ->insert([
                "id" => Text::uuid(),
                "ordering" => 0,
                "category" => "Blog",
                "key_name" => "articleDisplayMode",
                "value" => "summary",
                "value_type" => "select",
                "value_obscure" => false,
                "description" => "This setting controls if articles on the blog index show their Summary or Body text.",
                "data" => "{\n  \"summary\": \"Summary\",\n  \"lede\": \"Lede\",\n  \"body\": \"Body\"\n}",
                "column_width" => 2
            ])
            ->insert([
                "id" =>  Text::uuid(),
                "ordering" => 18,
                "category" => "Translations",
                "key_name" => "pt_PT",
                "value" => "0",
                "value_type" => "bool",
                "value_obscure" => false,
                "description" => "Enable translations in Portuguese",
                "data" => null,
                "column_width" =>  2
            ])
            ->insert([
                "id" =>  Text::uuid(),
                "ordering" =>  8,
                "category" => "AI",
                "key_name" => "imageAnalysis",
                "value" => "0",
                "value_type" => "bool",
                "value_obscure" => false,
                "description" => "Enable or disable the automatic image analysis feature to enhance your content's accessibility. When activated, the system will examine each images to generate relevant keywords and descriptive alt text. This functionality ensures that images are appropriately tagged, improving SEO and providing a better experience for users who rely on screen readers.",
                "data" => null,
                "column_width" => 2
            ])
            ->insert([
                "id" => Text::uuid(),
                "ordering" => 0,
                "category" => "Editing",
                "key_name" => "editor",
                "value" => "trumbowyg",
                "value_type" => "select",
                "value_obscure" => false,
                "description" => "Chose your default editor for posts and pages content. Trumbowyg is good for HTML whilst Markdown-It supports Markdown.",
                "data" => "{\n  \"trumbowyg\": \"Trumbowyg\",\n  \"markdownit\": \"Markdown-It\"\n}",
                "column_width" => 2
            ])
            ->insert([
                "id" => Text::uuid(),
                "ordering" => 3,
                "category" => "SitePages",
                "key_name" => "mainTagMenuShow",
                "value" => "root",
                "value_type" => "select",
                "value_obscure" => false,
                "description" => "Should the main tag menu show all root tags or only selected tags?",
                "data" => "{\n  \"root\": \"Top Level Tags\",\n  \"selected\": \"Selected Tags\"\n}",
                "column_width" => 2
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'Security',
                'key_name' => 'trustProxy',
                'value' => 0,
                'value_type' => 'bool',
                'value_obscure' => 0,
                'description' => 'Enable this setting if Willow CMS is deployed behind a proxy or load balancer that modifies request headers. When enabled, the application will trust the `X-Forwarded-For` and `X-Real-IP` headers to determine the original client IP address. Use this setting with caution, as it can expose Willow CMS to IP spoofing if untrusted proxies are allowed.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 2,
                'category' => 'RateLimit',
                'key_name' => 'numberOfRequests',
                'value' => 30,
                'value_type' => 'numeric',
                'value_obscure' => 0,
                'description' => 'The maximum number of requests allowed per minute for sensitive routes such as login and registration.',
                'data' => null,
                'column_width' => 2,
            ])
            ->save();
    }
}
