DROP DATABASE IF EXISTS `adaptercms`;
CREATE DATABASE `adaptercms` DEFAULT CHARACTER SET = `utf8mb4` COLLATE = `utf8mb4_unicode_ci`;

CREATE USER 'adaptercms_user'@'localhost' IDENTIFIED BY 'password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES ON `adaptercms`.* TO 'adaptercms_user'@'localhost';
FLUSH PRIVILEGES;

CREATE USER 'adaptercms_user'@'%' IDENTIFIED BY 'password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES ON `adaptercms`.* TO 'adaptercms_user'@'%';
FLUSH PRIVILEGES;

DROP DATABASE IF EXISTS `adaptercms_test`;
CREATE DATABASE `adaptercms_test` DEFAULT CHARACTER SET = `utf8mb4` COLLATE = `utf8mb4_unicode_ci`;

CREATE USER 'adaptercms_user_test'@'localhost' IDENTIFIED BY 'password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES ON `adaptercms_test`.* TO 'adaptercms_user_test'@'localhost';
FLUSH PRIVILEGES;

CREATE USER 'adaptercms_user_test'@'%' IDENTIFIED BY 'password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES ON `adaptercms_test`.* TO 'adaptercms_user_test'@'%';
FLUSH PRIVILEGES;