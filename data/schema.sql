-- Augment other tables
ALTER TABLE `users` ADD `currency` char(3) NOT NULL DEFAULT 'EUR' COMMENT 'billing_core' AFTER `timezone`;
ALTER TABLE `users` ADD `vat_reg_no` varchar(100) DEFAULT NULL COMMENT 'billing_core' AFTER `currency`;
ALTER TABLE `users` ADD `billing_address_id` int(11) unsigned DEFAULT NULL COMMENT 'billing_core' AFTER `vat_reg_no`;

ALTER TABLE `virtual_users` ADD `currency` char(3) NOT NULL DEFAULT 'EUR' COMMENT 'billing_core' AFTER `timezone`;
ALTER TABLE `virtual_users` ADD `vat_reg_no` varchar(100) DEFAULT NULL COMMENT 'billing_core' AFTER `currency`;
ALTER TABLE `virtual_users` ADD `billing_address_id` int(11) unsigned DEFAULT NULL COMMENT 'billing_core' AFTER `vat_reg_no`;

