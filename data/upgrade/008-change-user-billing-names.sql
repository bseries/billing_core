ALTER TABLE `users` CHANGE `billing_currency` `currency` CHAR(3)  NOT NULL  DEFAULT 'EUR'  COMMENT 'billing_core';
ALTER TABLE `users` CHANGE `billing_vat_reg_no` `vat_reg_no` VARCHAR(100)  NULL  DEFAULT NULL  COMMENT 'billing_core';
ALTER TABLE `users` CHANGE `billing_tax_country` `tax_country` CHAR(2)  NULL  DEFAULT NULL  COMMENT 'billing_core';

ALTER TABLE `virtual_users` CHANGE `billing_currency` `currency` CHAR(3)  NOT NULL  DEFAULT 'EUR'  COMMENT 'billing_core';
ALTER TABLE `virtual_users` CHANGE `billing_vat_reg_no` `vat_reg_no` VARCHAR(100)  NULL  DEFAULT NULL  COMMENT 'billing_core';
ALTER TABLE `virtual_users` ADD `tax_country` CHAR(2)  NULL  DEFAULT NULL  COMMENT 'billing_core' AFTER `vat_reg_no`;

UPDATE `users` SET `tax_country` = 'DE' WHERE 1 = 1;
UPDATE `virtual_users` SET `tax_country` = 'DE' WHERE 1 = 1;

