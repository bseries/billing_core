-- Augment other tables
ALTER TABLE `users` ADD `currency` char(3) NOT NULL DEFAULT 'EUR' COMMENT 'billing' AFTER `timezone`;
ALTER TABLE `users` ADD `vat_reg_no` varchar(100) DEFAULT NULL COMMENT 'billing' AFTER `currency`;
ALTER TABLE `users` ADD `tax_no` VARCHAR(100)  NULL  DEFAULT NULL  COMMENT 'billing'  AFTER `vat_reg_no`;
ALTER TABLE `users` ADD `billing_address_id` int(11) unsigned DEFAULT NULL COMMENT 'billing' AFTER `tax_no`;
ALTER TABLE `users` ADD `tax_type` VARCHAR(20)  NULL  DEFAULT NULL  AFTER `tax_no`;

-- Augment
ALTER TABLE `users` ADD `payment_method` VARCHAR(100)  NULL  DEFAULT NULL  AFTER `tax_no`;
