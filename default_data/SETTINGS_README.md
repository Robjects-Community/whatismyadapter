# Settings Configuration Summary
Total settings: 116
Total categories: 18

## Categories Overview

- **AI**: 14 settings
- **Anthropic**: 1 settings
- **Blog**: 1 settings
- **Comments**: 2 settings
- **Editing**: 1 settings
- **Email**: 1 settings
- **Google**: 4 settings
- **HomepageFeeds**: 13 settings
- **ImageSizes**: 8 settings
- **PagesAndArticles**: 1 settings
- **Products**: 21 settings
- **RateLimit**: 7 settings
- **SEO**: 4 settings
- **Security**: 6 settings
- **SitePages**: 4 settings
- **Translations**: 25 settings
- **Users**: 1 settings
- **i18n**: 2 settings

## Detailed Settings by Category

### AI (14 settings)

**enabled** (`bool`)
- Default: `0`
- Harness the power of artificial intelligence to enhance your content creation process. By enabling AI features, you gain access to a range of powerful tools, such as automatic article summarization, SEO metadata generation, and multilingual translation.

**articleTranslations** (`bool`)
- Default: `0`
- Automatically translate your articles into any of the 25 languages enabled in the translations settings. When you publish a page or article, the system will generate high-quality translations.

**tagTranslations** (`bool`)
- Default: `0`
- Automatically translate your tags into any of the 25 languages enabled in the translations settings. When you publish a page or article, the system will generate high-quality translations.

**articleSEO** (`bool`)
- Default: `0`
- Optimize your articles and pages for search engines and social media by automatically generating SEO metadata. When enabled, the system will create a meta title, meta description, meta keywords, and tailored descriptions for Facebook, LinkedIn, Instagram, and Twitter.

**tagSEO** (`bool`)
- Default: `0`
- Optimize your tags for search engines and social media by automatically generating SEO metadata. When enabled, the system will create a meta title, meta description, meta keywords, and tailored descriptions for Facebook, LinkedIn, Instagram, and Twitter.

**articleTags** (`bool`)
- Default: `0`
- Automatically generate relevant tags for your articles and pages based on their content. When you save an article or page, the system will analyze the text and create tags that best represent the main topics and keywords. These tags will then be automatically linked to the corresponding article or page, making it easier for readers to find related content on your website.

**articleSummaries** (`bool`)
- Default: `0`
- Automatically generate concise and compelling summaries for your articles and pages. When enabled, the system will analyze the content and create a brief synopsis that captures the key points. These summaries will appear on the article index page and other areas where a short overview is preferable to displaying the full text.

**hourlyLimit** (`numeric`)
- Default: `100`
- Maximum number of AI API calls allowed per hour. This helps control costs and prevents runaway usage. Set to 0 for unlimited (not recommended for production).

**dailyCostLimit** (`numeric`)
- Default: `2.50`
- Maximum daily cost threshold in USD for AI operations. When this limit is reached, AI features will be temporarily disabled until the next day. This prevents unexpected billing charges.

**enableMetrics** (`bool`)
- Default: `1`
- Enable detailed tracking and analytics for AI operations. This includes execution times, token usage, costs, and success rates. Metrics help optimize performance and monitor API usage patterns.

**enableCostAlerts** (`bool`)
- Default: `1`
- Send email notifications when AI costs approach or exceed defined thresholds. Alerts help administrators monitor spending and take action before limits are reached.

**gallerySEO** (`bool`)
- Default: `0`
- Enable AI-powered SEO field generation for image galleries.

**galleryTranslations** (`bool`)
- Default: `0`
- Enable automatic translation of image galleries to all enabled languages.

**imageAnalysis** (`bool`)
- Default: `0`
- Enable or disable the automatic image analysis feature to enhance your content's accessibility. When activated, the system will examine each images to generate relevant keywords and descriptive alt text. This functionality ensures that images are appropriately tagged, improving SEO and providing a better experience for users who rely on screen readers.

### Anthropic (1 settings)

**apiKey** (`text`)
- Default: `your-api-key-here`
- This field is used to store your Anthropic API key, which grants access to a range of AI-powered features and services provided by Anthropic. These features are designed to enhance your content management system and streamline various tasks. Some of the key functionalities include auto tagging, SEO text generation, image alt text & keyword generation.

### Blog (1 settings)

**articleDisplayMode** (`select`)
- Default: `summary`
- This setting controls if articles on the blog index show their Summary or Body text.

### Comments (2 settings)

**articlesEnabled** (`bool`)
- Default: `0`
- Turn this on to enable logged in users to comment on your articles.

**pagesEnabled** (`bool`)
- Default: `0`
- Turn this on to enable logged in users to comment on your pages.

### Editing (1 settings)

**editor** (`select`)
- Default: `trumbowyg`
- Chose your default editor for posts and pages content. Trumbowyg is good for HTML whilst Markdown-It supports Markdown.

### Email (1 settings)

**reply_email** (`text`)
- Default: `noreply@example.com`
- The "Reply Email" field allows you to specify the email address that will be used as the "Reply-To" address for outgoing emails sent from Willow CMS. When a recipient receives an email from your website and chooses to reply to it, their response will be directed to the email address specified in this field.

### Google (4 settings)

**tagManagerHead** (`textarea`)
- Default: `<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-XXXXXXXXXX');
</script>`
- The Google Tag Manager <head> tag is a JavaScript snippet placed in the <head> section that loads the GTM container and enables tag management without direct code modifications.

**translateApiKey** (`text`)
- Default: `your-api-key-here`
- This field is used to store your Google API key, which is required to access and utilize the Google Cloud Translation API. The Google Cloud Translation API allows you to integrate machine translation capabilities into your content management system, enabling automatic translation of your website content into different languages.

**youtubeApiKey** (`text`)
- Default: `your-api-key-here`
- This field is used to store your YouTube API key, which is required to access your videos to insert into post and page content.

**youtubeChannelId** (`text`)
- Default: `your-api-key-here`
- This field is used to store your YouTube Channel ID, which is required to allow you to filter videos to just your own.

### HomepageFeeds (13 settings)

**featuredArticlesEnabled** (`bool`)
- Default: `1`
- Display featured articles section on the homepage. Featured articles are highlighted at the top of the page with larger layouts.

**latestArticlesEnabled** (`bool`)
- Default: `1`
- Display latest articles section on the homepage. Shows the most recent published articles in a grid layout.

**latestProductsEnabled** (`bool`)
- Default: `1`
- Display latest products section on the homepage. Shows the most recently added products with images and descriptions.

**popularTagsEnabled** (`bool`)
- Default: `1`
- Display popular tags widget in the sidebar. Shows tag cloud with the most used tags across articles.

**socialLinksEnabled** (`bool`)
- Default: `1`
- Display social links and connect section in the sidebar with links to author pages and social profiles.

**developmentInfoEnabled** (`bool`)
- Default: `1`
- Display development stack and server information widget in the sidebar showing technology stack details.

**searchWidgetEnabled** (`bool`)
- Default: `0`
- Display search widget in the sidebar. Currently disabled as search functionality is not yet implemented.

**imageGalleriesEnabled** (`bool`)
- Default: `0`
- Display image galleries section on the homepage. Shows recent image galleries if galleries feature is enabled.

**userRegistrationWidgetEnabled** (`bool`)
- Default: `0`
- Display user registration widget in the sidebar when user registration is enabled.

**featuredArticlesLimit** (`numeric`)
- Default: `3`
- Number of featured articles to display on the homepage (1-10).

**latestArticlesLimit** (`numeric`)
- Default: `6`
- Number of latest articles to display on the homepage (1-20).

**latestProductsLimit** (`numeric`)
- Default: `4`
- Number of latest products to display on the homepage (1-12).

**popularTagsLimit** (`numeric`)
- Default: `15`
- Number of popular tags to display in the tags widget (1-50).

### ImageSizes (8 settings)

**massive** (`numeric`)
- Default: `800`
- The width for the massive image size.

**large** (`numeric`)
- Default: `400`
- The width for the large image size.

**medium** (`numeric`)
- Default: `300`
- The width for the medium image size.

**small** (`numeric`)
- Default: `200`
- The width for the small image size.

**tiny** (`numeric`)
- Default: `100`
- The width for the tiny image size.

**teeny** (`numeric`)
- Default: `50`
- The width for the teeny image size.

**micro** (`numeric`)
- Default: `10`
- The width for the micro image size.

**extraLarge** (`numeric`)
- Default: `500`
- The width for the extra-large image size.

### PagesAndArticles (1 settings)

**additionalImages** (`bool`)
- Default: `0`
- Enable additional image uploads on your Articles and Pages.

### Products (21 settings)

**enabled** (`bool`)
- Default: `1`
- Enable the products system. When disabled, products will not be accessible on the frontend.

**userSubmissions** (`bool`)
- Default: `1`
- Allow users to submit products for review. When enabled, registered users can add products that require approval.

**aiVerificationEnabled** (`bool`)
- Default: `1`
- Enable AI-powered verification of product submissions. Uses AI to validate product information and suggest improvements.

**peerVerificationEnabled** (`bool`)
- Default: `1`
- Enable peer verification where users can verify and rate product accuracy.

**minVerificationScore** (`numeric`)
- Default: `3.0`
- Minimum verification score (0-5) required for automatic approval. Products below this score require manual review.

**autoPublishThreshold** (`numeric`)
- Default: `4.0`
- Reliability score threshold for automatic publishing. Products scoring above this will be automatically published.

**maxUserSubmissionsPerDay** (`numeric`)
- Default: `5`
- Maximum number of products a user can submit per day. Set to 0 for unlimited submissions.

**duplicateDetectionEnabled** (`bool`)
- Default: `1`
- Enable duplicate detection to prevent submission of identical products based on title and manufacturer.

**productImageRequired** (`bool`)
- Default: `1`
- Require at least one product image for publication. Helps maintain visual consistency.

**technicalSpecsRequired** (`bool`)
- Default: `1`
- Require basic technical specifications (description, manufacturer, model) for product approval.

**enable_public_submissions** (`bool`)
- Default: `0`
- Allow public users to submit products via frontend forms

**require_admin_approval** (`bool`)
- Default: `1`
- Whether user-submitted products require admin approval before publication

**default_status** (`select`)
- Default: `pending`
- Default verification status for user-submitted products

**max_file_size** (`numeric`)
- Default: `5`
- Maximum file size in MB for product image uploads

**allowed_file_types** (`text`)
- Default: `jpg,jpeg,png,gif,webp`
- Comma-separated list of allowed file extensions for product images

**required_fields** (`text`)
- Default: `title,description,manufacturer`
- Comma-separated list of required form fields

**notification_email** (`text`)
- Default: `0`
- Email address to notify when new products are submitted (use 0 to disable)

**success_message** (`textarea`)
- Default: `Your product has been submitted and is awaiting review. Thank you for contributing to our adapter database!`
- Message shown to users after successful product submission

**quiz_enabled** (`bool`)
- Default: `0`
- Enable quiz-based adapter finder to help users discover suitable adapters

**quiz_config_json** (`textarea`)
- Default: `{}`
- JSON configuration for quiz questions, branching logic, and scoring algorithm

**quiz_results_page** (`select-page`)
- Default: `0`
- Page to redirect users to after quiz completion (0 = disabled)

### RateLimit (7 settings)

**numberOfSeconds** (`numeric`)
- Default: `60`
- This field complements the "Rate Limit: Number Of Requests" setting by specifying the time window in which the request limit is enforced. It determines the duration, in seconds, for which the rate limit is applied. For example, if you set the "Rate Limit: Number Of Requests" to 100 and the "Rate Limit: Number Of Seconds" to 60, it means that an IP address can make a maximum of 100 requests within a 60-second window. If an IP address exceeds this limit within the specified time frame, they will be blocked for a certain period to prevent further requests and protect your server from potential abuse or overload.

**numberOfRequests** (`numeric`)
- Default: `30`
- The maximum number of requests allowed per minute for sensitive routes such as login and registration.

**loginNumberOfRequests** (`numeric`)
- Default: `5`
- Maximum login attempts allowed within the time window.

**loginNumberOfSeconds** (`numeric`)
- Default: `60`
- Time window in seconds for login rate limiting.

**adminNumberOfSeconds** (`numeric`)
- Default: `60`
- Time window in seconds for admin area rate limiting.

**passwordResetNumberOfSeconds** (`numeric`)
- Default: `300`
- Time window in seconds for password reset rate limiting (300 = 5 minutes).

**registerNumberOfSeconds** (`numeric`)
- Default: `300`
- Time window in seconds for registration rate limiting (300 = 5 minutes).

### SEO (4 settings)

**siteMetakeywords** (`textarea`)
- Default: `Default site meta keywords`
- Metakeywords are a set of keywords or phrases that describe the content of your website. These keywords are used by search engines to index your site and improve its visibility in search results. Enter relevant and specific keywords that accurately represent the topics and themes of your site content.

**siteMetaDescription** (`textarea`)
- Default: `Default site meta description`
- The site meta description is a brief summary of your website's content and purpose. It appears in search engine results below the page title and URL, providing potential visitors with a snapshot of what your site offers. Craft a compelling and informative description to encourage clicks and improve search engine optimization (SEO).

**siteStrapline** (`textarea`)
- Default: `Welcome to Willow CMS`
- The site strapline is a brief, catchy phrase or slogan that complements your site name. It provides additional context or a memorable tagline that encapsulates the essence of your website. This strapline is often displayed alongside the site name in headers or footers.

**siteName** (`text`)
- Default: `Willow CMS`
- This field represents the official name of your website. It is typically displayed in the title bar of web browsers and is used in various places throughout the site to identify your brand or organization. Ensure that the name is concise and accurately reflects the purpose or identity of your site.

### Security (6 settings)

**trustProxy** (`bool`)
- Default: `0`
- Enable this setting if Willow CMS is deployed behind a proxy or load balancer that modifies request headers. When enabled, the application will trust the `X-Forwarded-For` and `X-Real-IP` headers to determine the original client IP address. Use this setting with caution, as it can expose Willow CMS to IP spoofing if untrusted proxies are allowed.

**trustedProxies** (`textarea`)
- Default: ``
- List of trusted proxy IP addresses (one per line). Only requests from these IPs will have their forwarded headers honored when trustProxy is enabled. Leave empty to trust all proxies (not recommended for production).

**blockOnNoIp** (`bool`)
- Default: `1`
- Block requests when the client IP address cannot be determined. Recommended for production environments to prevent IP detection bypass.

**enableRateLimiting** (`bool`)
- Default: `1`
- Enable rate limiting for IP addresses. When enabled, the system will track request frequency and temporarily block IPs that exceed the configured limits.

**suspiciousWindowHours** (`numeric`)
- Default: `24`
- Time window in hours for counting suspicious requests.

**suspiciousBlockHours** (`numeric`)
- Default: `24`
- How long to block IPs that exceed the suspicious request threshold (in hours).

### SitePages (4 settings)

**mainMenuShow** (`select`)
- Default: `root`
- Should the main menu show all root pages or only selected pages?

**footerMenuShow** (`select`)
- Default: `selected`
- Should the footer menu show all root pages or only selected pages?

**privacyPolicy** (`select-page`)
- Default: `None`
- Choose which page to show as your site Privacy Policy.

**mainTagMenuShow** (`select`)
- Default: `root`
- Should the main tag menu show all root tags or only selected tags?

### Translations (25 settings)

**bg_BG** (`bool`)
- Default: `0`
- Enable translations in Bulgarian

**cs_CZ** (`bool`)
- Default: `0`
- Enable translations in Czech

**da_DK** (`bool`)
- Default: `0`
- Enable translations in Danish

**el_GR** (`bool`)
- Default: `0`
- Enable translations in Greek

**es_ES** (`bool`)
- Default: `0`
- Enable translations in Spanish

**fi_FI** (`bool`)
- Default: `0`
- Enable translations in Finnish

**hr_HR** (`bool`)
- Default: `0`
- Enable translations in Croatian

**hu_HU** (`bool`)
- Default: `0`
- Enable translations in Hungarian

**it_IT** (`bool`)
- Default: `0`
- Enable translations in Italian

**lt_LT** (`bool`)
- Default: `0`
- Enable translations in Lithuanian

**lv_LV** (`bool`)
- Default: `0`
- Enable translations in Latvian

**no_NO** (`bool`)
- Default: `0`
- Enable translations in Norwegian

**pl_PL** (`bool`)
- Default: `0`
- Enable translations in Polish

**ru_RU** (`bool`)
- Default: `0`
- Enable translations in Russian

**sk_SK** (`bool`)
- Default: `0`
- Enable translations in Slovak

**sl_SI** (`bool`)
- Default: `0`
- Enable translations in Slovenian

**sv_SE** (`bool`)
- Default: `0`
- Enable translations in Swedish

**uk_UA** (`bool`)
- Default: `0`
- Enable translations in Ukrainian

**tr_TR** (`bool`)
- Default: `0`
- Enable translations in Turkish

**ro_RO** (`bool`)
- Default: `0`
- Enable translations in Romanian

**et_EE** (`bool`)
- Default: `0`
- Enable translations in Estonian

**de_DE** (`bool`)
- Default: `0`
- Enable translations in German

**fr_FR** (`bool`)
- Default: `0`
- Enable translations in French

**nl_NL** (`bool`)
- Default: `0`
- Enable translations in Dutch

**pt_PT** (`bool`)
- Default: `0`
- Enable translations in Portuguese

### Users (1 settings)

**registrationEnabled** (`bool`)
- Default: `0`
- Turn this on to enable users to register accounts on the site.

### i18n (2 settings)

**locale** (`select`)
- Default: `en_GB`
- This setting determines the default language for the admin area, allowing users to select languages such as French or German.

**provider** (`select`)
- Default: `google`
- This setting is used for updating the built-in translations for the Willow CMS interface. Options include Google or Anthropic, with Google generally providing better translations. For auto translation of your website content, see the Translations section to enable languages.

