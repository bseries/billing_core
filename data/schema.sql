-- Augment other tables
ALTER TABLE `users` ADD `currency` char(3) NOT NULL DEFAULT 'EUR' COMMENT 'billing' AFTER `timezone`;
ALTER TABLE `users` ADD `vat_reg_no` varchar(100) DEFAULT NULL COMMENT 'billing' AFTER `currency`;
ALTER TABLE `users` ADD `billing_address_id` int(11) unsigned DEFAULT NULL COMMENT 'billing' AFTER `vat_reg_no`;

