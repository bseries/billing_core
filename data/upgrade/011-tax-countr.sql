ALTER TABLE `users` CHANGE `tax_country` `country` CHAR(2)  NULL  DEFAULT NULL  COMMENT 'billing_core';
ALTER TABLE `users` MODIFY COLUMN `country` CHAR(2) DEFAULT NULL COMMENT 'billing_core' AFTER `timezone`;

ALTER TABLE `virtual_users` CHANGE `tax_country` `country` CHAR(2)  NULL  DEFAULT NULL  COMMENT 'billing_core';
ALTER TABLE `virtual_users` MODIFY COLUMN `country` CHAR(2)  DEFAULT NULL COMMENT 'billing_core' AFTER `timezone`;

