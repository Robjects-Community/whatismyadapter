-- MySQL initialization script for WillowCMS
-- This script creates test databases and grants permissions
-- Main database 'cms' and user are created by MySQL's entrypoint using environment variables

-- Test Database
CREATE DATABASE IF NOT EXISTS `cms_test` DEFAULT CHARACTER SET = `utf8mb4` COLLATE = `utf8mb4_unicode_ci`;
GRANT ALL PRIVILEGES ON `cms_test`.* TO 'cms_user'@'%';

-- Foreign Keys Test Database
CREATE DATABASE IF NOT EXISTS `cms_test_foreign_keys` DEFAULT CHARACTER SET = `utf8mb4` COLLATE = `utf8mb4_unicode_ci`;
GRANT ALL PRIVILEGES ON `cms_test_foreign_keys`.* TO 'cms_user'@'%';

FLUSH PRIVILEGES;
