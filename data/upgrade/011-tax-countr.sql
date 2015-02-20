ALTER TABLE `users` CHANGE `tax_country` `country` CHAR(2)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL  COMMENT 'billing_core';
ALTER TABLE `users` MODIFY COLUMN `country` CHAR(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'billing_core' AFTER `timezone`;

ALTER TABLE `virtual_users` CHANGE `tax_country` `country` CHAR(2)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL  COMMENT 'billing_core';
ALTER TABLE `virtual_users` MODIFY COLUMN `country` CHAR(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'billing_core' AFTER `timezone`;

