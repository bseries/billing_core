ALTER TABLE `users` CHANGE `billing_currency` `currency` CHAR(3)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT 'EUR'  COMMENT 'billing_core';
ALTER TABLE `users` CHANGE `billing_vat_reg_no` `vat_reg_no` VARCHAR(100)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL  COMMENT 'billing_core';
ALTER TABLE `users` CHANGE `billing_tax_country` `tax_country` CHAR(2)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL  COMMENT 'billing_core';

ALTER TABLE `virtual_users` CHANGE `billing_currency` `currency` CHAR(3)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT 'EUR'  COMMENT 'billing_core';
ALTER TABLE `virtual_users` CHANGE `billing_vat_reg_no` `vat_reg_no` VARCHAR(100)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL  COMMENT 'billing_core';
ALTER TABLE `virtual_users` CHANGE `billing_tax_country` `tax_country` CHAR(2)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL  COMMENT 'billing_core';

