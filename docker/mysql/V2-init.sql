-- =========================================================================
-- WILLOW CMS PRODUCT SYSTEM EXTENSION
-- Compatible with Willow CMS v1.4.0
-- Implements cord adapter/electronic accessory product management
-- Following existing CMS patterns for articles system
-- =========================================================================

-- Add new AI prompts for product system
INSERT INTO `aiprompts` (`id`, `task_type`, `system_prompt`, `model`, `max_tokens`, `temperature`, `created`, `modified`) VALUES
('prod-001-spec-analysis', 'product_specification_analysis', 'You are a technical product analyzer for electronic accessories and cord adapters. Analyze product descriptions and specifications to extract structured technical data including connector types, power ratings, data transfer speeds, compatibility information, and key features. Respond with JSON containing: {"connector_types": [], "power_rating": "", "data_transfer_speed": "", "compatibility": [], "key_features": [], "technical_specifications": {}}', 'claude-3-5-sonnet-20241022', 4000, 0, NOW(), NOW()),

('prod-002-verification', 'product_verification', 'You are a product verification assistant for an electronics accessory database. Evaluate product submissions for accuracy, completeness, and quality. Check for: technical specification accuracy, proper categorization, complete product information, potential duplicate entries, and overall reliability. Respond with JSON: {"verification_score": 0.0-5.0, "issues_found": [], "suggestions": [], "is_approved": boolean, "verification_notes": ""}', 'claude-3-5-sonnet-20241022', 3000, 0, NOW(), NOW()),

('prod-003-seo-generation', 'product_seo_generation', 'Generate SEO metadata for electronic product listings. Create compelling titles, descriptions, and keywords optimized for search engines and social media platforms. Focus on technical specifications, compatibility, and user benefits. Return JSON with meta_title, meta_description, meta_keywords, facebook_description, linkedin_description, twitter_description, instagram_description fields.', 'claude-3-5-sonnet-20241022', 4000, 0, NOW(), NOW()),

('prod-004-content-generation', 'product_content_generation', 'Generate comprehensive product content including descriptions, summaries, and technical details for electronic accessories and cord adapters. Create user-friendly content that explains technical specifications in accessible language while maintaining technical accuracy. Include usage scenarios and compatibility information.', 'claude-3-5-sonnet-20241022', 6000, 0, NOW(), NOW());

-- Add product-related settings
INSERT INTO `settings` (`id`, `ordering`, `category`, `key_name`, `value`, `value_type`, `value_obscure`, `description`, `data`, `column_width`, `created`, `modified`) VALUES
('prod-set-001', 1, 'Products', 'enabled', '1', 'bool', 0, 'Enable the product management system for cord adapters and electronic accessories.', NULL, 2, NOW(), NOW()),
('prod-set-002', 2, 'Products', 'userSubmissionsEnabled', '1', 'bool', 0, 'Allow registered users to submit new product suggestions for review.', NULL, 2, NOW(), NOW()),
('prod-set-003', 3, 'Products', 'aiVerificationEnabled', '1', 'bool', 0, 'Enable AI-powered verification of product submissions and technical specifications.', NULL, 2, NOW(), NOW()),
('prod-set-004', 4, 'Products', 'peerVerificationEnabled', '1', 'bool', 0, 'Enable peer verification system for community-driven quality control.', NULL, 2, NOW(), NOW()),
('prod-set-005', 5, 'Products', 'minVerificationScore', '3.0', 'numeric', 0, 'Minimum verification score required for products to be publicly visible (0.0-5.0).', NULL, 2, NOW(), NOW()),
('prod-set-006', 6, 'Products', 'autoPublishThreshold', '4.0', 'numeric', 0, 'Verification score threshold for automatic publication without manual review.', NULL, 2, NOW(), NOW()),
('prod-set-007', 7, 'Products', 'affiliateLinkTracking', '1', 'bool', 0, 'Enable affiliate link tracking and analytics for product monetization.', NULL, 2, NOW(), NOW());

-- =========================================================================
-- CORE PRODUCT TABLES
-- =========================================================================

-- Main products table (mirrors articles structure)
CREATE TABLE `products` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kind` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'adapter',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lede` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `markdown` text COLLATE utf8mb4_unicode_ci,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` int DEFAULT NULL,
  `mime` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `published` datetime DEFAULT NULL,
  `meta_title` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `facebook_description` text COLLATE utf8mb4_unicode_ci,
  `linkedin_description` text COLLATE utf8mb4_unicode_ci,
  `instagram_description` text COLLATE utf8mb4_unicode_ci,
  `twitter_description` text COLLATE utf8mb4_unicode_ci,
  `word_count` int DEFAULT NULL,
  `parent_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lft` int NOT NULL DEFAULT 1,
  `rght` int NOT NULL DEFAULT 2,
  `main_menu` tinyint(1) NOT NULL DEFAULT '0',
  `view_count` int NOT NULL DEFAULT '0' COMMENT 'Number of views for the product',
  
  -- Product-specific fields
  `product_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manufacturer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `availability_status` enum('in_stock','out_of_stock','discontinued','pre_order') COLLATE utf8mb4_unicode_ci DEFAULT 'in_stock',
  `stock_quantity` int DEFAULT NULL,
  
  -- Verification and quality control fields
  `reliability_score` decimal(3,2) DEFAULT 0.00 COMMENT 'Reliability score from 0.00 to 5.00',
  `entry_input_type` enum('developer','user_submission','ai_generated','peer_reviewed') COLLATE utf8mb4_unicode_ci DEFAULT 'developer',
  `customer_peer_verification_count` int NOT NULL DEFAULT 0,
  `verification_status` enum('pending','under_review','approved','rejected','needs_revision') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `ai_analysis_score` decimal(3,2) DEFAULT NULL,
  `verification_notes` text COLLATE utf8mb4_unicode_ci,
  `verified_at` datetime DEFAULT NULL,
  `verified_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  
  -- Technical specifications (JSON for flexibility)
  `technical_specs` json DEFAULT NULL,
  `connector_info` json DEFAULT NULL,
  `compatibility_info` json DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  KEY `idx_product_slug` (`slug`),
  KEY `idx_product_published` (`is_published`),
  KEY `idx_product_featured` (`featured`),
  KEY `idx_product_kind` (`kind`),
  KEY `idx_product_verification` (`verification_status`),
  KEY `idx_product_reliability` (`reliability_score`),
  KEY `idx_product_manufacturer` (`manufacturer`),
  KEY `idx_product_availability` (`availability_status`),
  KEY `idx_product_user` (`user_id`),
  KEY `idx_product_code` (`product_code`),
  KEY `idx_product_parent` (`parent_id`),
  KEY `idx_product_tree` (`lft`, `rght`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products translations table
CREATE TABLE `products_translations` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lede` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `meta_title` text COLLATE utf8mb4_unicode_ci,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `facebook_description` text COLLATE utf8mb4_unicode_ci,
  `linkedin_description` text COLLATE utf8mb4_unicode_ci,
  `instagram_description` text COLLATE utf8mb4_unicode_ci,
  `twitter_description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`,`locale`),
  KEY `idx_product_translation_locale` (`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products to tags relationship
CREATE TABLE `products_tags` (
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`product_id`,`tag_id`),
  KEY `idx_products_tags_product` (`product_id`),
  KEY `idx_products_tags_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- PRODUCT-SPECIFIC SUPPORTING TABLES
-- =========================================================================

-- Product categories for better organization
CREATE TABLE `product_categories` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parent_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lft` int NOT NULL DEFAULT 1,
  `rght` int NOT NULL DEFAULT 2,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int NOT NULL DEFAULT 0,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_category_slug` (`slug`),
  KEY `idx_category_parent` (`parent_id`),
  KEY `idx_category_tree` (`lft`, `rght`),
  KEY `idx_category_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product to categories relationship
CREATE TABLE `products_categories` (
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `idx_products_categories_product` (`product_id`),
  KEY `idx_products_categories_category` (`category_id`),
  KEY `idx_products_categories_primary` (`is_primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Connector types master table
CREATE TABLE `connector_types` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `connector_family` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_input` tinyint(1) DEFAULT 1,
  `is_output` tinyint(1) DEFAULT 1,
  `power_capability` tinyint(1) DEFAULT 0,
  `data_capability` tinyint(1) DEFAULT 0,
  `video_capability` tinyint(1) DEFAULT 0,
  `audio_capability` tinyint(1) DEFAULT 0,
  `max_power_watts` decimal(6,2) DEFAULT NULL,
  `max_data_speed_gbps` decimal(8,3) DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_connector_slug` (`slug`),
  KEY `idx_connector_family` (`connector_family`),
  KEY `idx_connector_capabilities` (`power_capability`, `data_capability`, `video_capability`, `audio_capability`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product connectors relationship
CREATE TABLE `product_connectors` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connector_type_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connector_role` enum('input','output','bidirectional') COLLATE utf8mb4_unicode_ci NOT NULL,
  `connector_position` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT 1,
  `power_rating_watts` decimal(6,2) DEFAULT NULL,
  `data_speed_gbps` decimal(8,3) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_product_connectors_product` (`product_id`),
  KEY `idx_product_connectors_type` (`connector_type_id`),
  KEY `idx_product_connectors_role` (`connector_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Affiliate links for monetization
CREATE TABLE `product_affiliate_links` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `affiliate_network` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `merchant_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `affiliate_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `commission_rate` decimal(5,2) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `click_count` int NOT NULL DEFAULT 0,
  `conversion_count` int NOT NULL DEFAULT 0,
  `last_checked` datetime DEFAULT NULL,
  `availability_status` enum('available','unavailable','unknown') COLLATE utf8mb4_unicode_ci DEFAULT 'unknown',
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_affiliate_product` (`product_id`),
  KEY `idx_affiliate_network` (`affiliate_network`),
  KEY `idx_affiliate_active` (`is_active`),
  KEY `idx_affiliate_primary` (`is_primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product reviews and ratings
CREATE TABLE `product_reviews` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint(1) NOT NULL COMMENT 'Rating from 1 to 5',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `review_text` text COLLATE utf8mb4_unicode_ci,
  `verified_purchase` tinyint(1) NOT NULL DEFAULT 0,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `helpful_votes` int NOT NULL DEFAULT 0,
  `unhelpful_votes` int NOT NULL DEFAULT 0,
  `moderation_notes` text COLLATE utf8mb4_unicode_ci,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_review_product` (`product_id`),
  KEY `idx_review_user` (`user_id`),
  KEY `idx_review_approved` (`is_approved`),
  KEY `idx_review_rating` (`rating`),
  KEY `idx_review_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product specifications (flexible key-value store)
CREATE TABLE `product_specifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `spec_category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `spec_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `spec_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `spec_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spec_type` enum('text','numeric','boolean','json') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `display_order` int NOT NULL DEFAULT 0,
  `is_filterable` tinyint(1) NOT NULL DEFAULT 0,
  `is_searchable` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_spec_product` (`product_id`),
  KEY `idx_spec_category` (`spec_category`),
  KEY `idx_spec_name` (`spec_name`),
  KEY `idx_spec_filterable` (`is_filterable`),
  KEY `idx_spec_searchable` (`is_searchable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product verification workflow
CREATE TABLE `product_verifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verifier_user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_type` enum('ai_analysis','peer_review','expert_review','automated_check') COLLATE utf8mb4_unicode_ci NOT NULL,
  `verification_score` decimal(3,2) NOT NULL,
  `verification_details` json DEFAULT NULL,
  `issues_found` json DEFAULT NULL,
  `suggestions` json DEFAULT NULL,
  `verification_notes` text COLLATE utf8mb4_unicode_ci,
  `is_approved` tinyint(1) DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_verification_product` (`product_id`),
  KEY `idx_verification_user` (`verifier_user_id`),
  KEY `idx_verification_type` (`verification_type`),
  KEY `idx_verification_score` (`verification_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product page views (analytics)
CREATE TABLE `product_views` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `referer` text COLLATE utf8mb4_unicode_ci,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_views_product` (`product_id`),
  KEY `idx_product_views_date` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- SAMPLE DATA INSERTION
-- =========================================================================

-- Insert default connector types focusing on Type-C
INSERT INTO `connector_types` (`id`, `name`, `slug`, `display_name`, `description`, `connector_family`, `power_capability`, `data_capability`, `video_capability`, `audio_capability`, `max_power_watts`, `max_data_speed_gbps`) VALUES
(UUID(), 'usb-c', 'usb-c', 'USB-C', 'Universal Serial Bus Type-C connector supporting power, data, video, and audio', 'USB', 1, 1, 1, 1, 100.00, 40.000),
(UUID(), 'usb-a', 'usb-a', 'USB-A', 'Universal Serial Bus Type-A connector', 'USB', 1, 1, 0, 0, 15.00, 10.000),
(UUID(), 'lightning', 'lightning', 'Lightning', 'Apple proprietary connector for iOS devices', 'Apple', 1, 1, 0, 1, 12.00, 0.480),
(UUID(), 'micro-usb', 'micro-usb', 'Micro USB', 'Micro USB connector commonly used in older Android devices', 'USB', 1, 1, 0, 0, 10.00, 0.480),
(UUID(), 'hdmi', 'hdmi', 'HDMI', 'High-Definition Multimedia Interface', 'Video', 0, 0, 1, 1, 0.00, 18.000),
(UUID(), '3.5mm-audio', '3-5mm-audio', '3.5mm Audio Jack', 'Standard 3.5mm audio connector', 'Audio', 0, 0, 0, 1, 0.00, 0.000),
(UUID(), 'displayport', 'displayport', 'DisplayPort', 'DisplayPort video connector', 'Video', 0, 0, 1, 1, 0.00, 32.400),
(UUID(), 'thunderbolt-3', 'thunderbolt-3', 'Thunderbolt 3', 'Thunderbolt 3 using USB-C connector', 'Thunderbolt', 1, 1, 1, 1, 100.00, 40.000);

-- Insert default product categories
INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`) VALUES
(UUID(), 'Charging Cables', 'charging-cables', 'Cables primarily designed for device charging'),
(UUID(), 'Data Cables', 'data-cables', 'Cables for data transfer between devices'),
(UUID(), 'Video Adapters', 'video-adapters', 'Adapters for video signal conversion'),
(UUID(), 'Audio Adapters', 'audio-adapters', 'Adapters for audio signal conversion'),
(UUID(), 'Multi-function Hubs', 'multi-function-hubs', 'Hubs providing multiple connectivity options'),
(UUID(), 'Power Adapters', 'power-adapters', 'Adapters for power delivery and charging');

-- =========================================================================
-- EXTEND EXISTING SLUG SYSTEM FOR PRODUCTS
-- =========================================================================

-- Add trigger to automatically create slugs for products (similar to articles)
DELIMITER $$
CREATE TRIGGER `tr_products_slug_insert` AFTER INSERT ON `products`
FOR EACH ROW BEGIN
    INSERT INTO `slugs` (`id`, `model`, `foreign_key`, `slug`, `created`)
    VALUES (UUID(), 'Products', NEW.id, NEW.slug, NOW());
END$$

CREATE TRIGGER `tr_products_slug_update` AFTER UPDATE ON `products`
FOR EACH ROW BEGIN
    IF OLD.slug != NEW.slug THEN
        INSERT INTO `slugs` (`id`, `model`, `foreign_key`, `slug`, `created`)
        VALUES (UUID(), 'Products', NEW.id, NEW.slug, NOW());
    END IF;
END$$
DELIMITER ;

-- =========================================================================
-- INDEXES AND CONSTRAINTS
-- =========================================================================

-- Add indexes for performance optimization
ALTER TABLE `products` 
ADD INDEX `idx_product_verification_status` (`verification_status`, `is_published`),
ADD INDEX `idx_product_reliability_published` (`reliability_score`, `is_published`),
ADD INDEX `idx_product_created_published` (`created`, `is_published`),
ADD INDEX `idx_product_category_lookup` (`kind`, `is_published`, `reliability_score`);

-- Add foreign key constraints where appropriate
-- Note: In production, you may want to add these constraints after data migration
-- ALTER TABLE `products` ADD CONSTRAINT `fk_products_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`);
-- ALTER TABLE `products` ADD CONSTRAINT `fk_products_parent` FOREIGN KEY (`parent_id`) REFERENCES `products`(`id`);

-- =========================================================================
-- ADDITIONAL SETTINGS FOR PRODUCT MANAGEMENT
-- =========================================================================

INSERT INTO `settings` (`id`, `ordering`, `category`, `key_name`, `value`, `value_type`, `value_obscure`, `description`, `data`, `column_width`, `created`, `modified`) VALUES
('prod-set-008', 8, 'Products', 'requireUserVerification', '1', 'bool', 0, 'Require user verification before allowing product submissions.', NULL, 2, NOW(), NOW()),
('prod-set-009', 9, 'Products', 'maxUserSubmissionsPerDay', '5', 'numeric', 0, 'Maximum number of product submissions allowed per user per day.', NULL, 2, NOW(), NOW()),
('prod-set-010', 10, 'Products', 'duplicateDetectionEnabled', '1', 'bool', 0, 'Enable automatic duplicate product detection using AI analysis.', NULL, 2, NOW(), NOW()),
('prod-set-011', 11, 'Products', 'productImageRequired', '1', 'bool', 0, 'Require at least one product image for all product submissions.', NULL, 2, NOW(), NOW()),
('prod-set-012', 12, 'Products', 'technicalSpecsRequired', '1', 'bool', 0, 'Require technical specifications for all product submissions.', NULL, 2, NOW(), NOW());

COMMIT;
