ALTER TABLE `billing_invoices` CHANGE `address_name` `address_recipient` VARCHAR(250)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT '';
ALTER TABLE `billing_invoices` CHANGE `address_company` `address_organization` VARCHAR(250)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;
ALTER TABLE `billing_invoices` CHANGE `address_street` `address_address_line_1` VARCHAR(250)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;
ALTER TABLE `billing_invoices` CHANGE `address_city` `address_locality` VARCHAR(100)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;
ALTER TABLE `billing_invoices` CHANGE `address_zip` `address_postal_code` VARCHAR(100)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;
ALTER TABLE `billing_invoices` ADD `address_address_line_2` VARCHAR(250)  NULL  DEFAULT NULL  AFTER `address_address_line_1`;
ALTER TABLE `billing_invoices` ADD `address_dependent_locality` VARCHAR(100)  NULL  DEFAULT NULL  AFTER `address_locality`;
ALTER TABLE `billing_invoices` ADD `address_sorting_code` VARCHAR(100)  NULL  DEFAULT NULL  AFTER `address_postal_code`;
ALTER TABLE `billing_invoices` ADD `address_administrative_area` VARCHAR(200)  NULL  DEFAULT NULL  AFTER `address_country`;


