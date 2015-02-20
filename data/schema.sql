-- Create syntax for TABLE 'billing_invoice_positions'
CREATE TABLE `billing_invoice_positions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `billing_invoice_id` int(11) unsigned DEFAULT NULL COMMENT 'NULL until assigned to invoice',
  `description` varchar(250) NOT NULL,
  `quantity` decimal(10,2) unsigned NOT NULL DEFAULT '1.00',
  `tax_type` varchar(20) NOT NULL DEFAULT '',
  `tax_rate` int(5) unsigned NOT NULL DEFAULT '0',
  `amount_currency` char(3) NOT NULL DEFAULT 'EUR',
  `amount_type` char(5) NOT NULL DEFAULT 'net',
  `amount` int(10) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `billing_invoice_id` (`billing_invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'billing_invoices'
CREATE TABLE `billing_invoices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `virtual_user_id` int(11) unsigned DEFAULT NULL,
  `number` varchar(100) NOT NULL DEFAULT '',
  `date` date NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'created',
  `total_currency` char(3) NOT NULL DEFAULT 'EUR' COMMENT 'Currency in which the invoice should be paid.',
  `user_vat_reg_no` varchar(250) DEFAULT '',
  `address_name` varchar(250) DEFAULT '',
  `address_company` varchar(250) DEFAULT NULL,
  `address_street` varchar(250) DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `address_zip` varchar(100) DEFAULT NULL,
  `address_country` char(2) DEFAULT 'DE',
  `address_phone` varchar(200) DEFAULT NULL,
  `terms` text,
  `note` text,
  `is_locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `user` (`user_id`),
  KEY `virtual_user_id` (`virtual_user_id`)
) ENGINE=InnoDB CHARSET=utf8;

-- Create syntax for TABLE 'billing_payments'
CREATE TABLE `billing_payments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `virtual_user_id` int(11) unsigned DEFAULT NULL,
  `billing_invoice_id` int(11) unsigned DEFAULT NULL COMMENT 'may be used when pymnt can be connected to inv',
  `method` varchar(100) NOT NULL DEFAULT '',
  `amount_currency` char(3) NOT NULL DEFAULT 'EUR',
  `amount` int(10) NOT NULL COMMENT 'always gross',
  `date` date NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `virtual_user_id` (`virtual_user_id`),
  KEY `billing_invoice_id` (`billing_invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Augment other tables
ALTER TABLE `users` ADD `currency` char(3) NOT NULL DEFAULT 'EUR' COMMENT 'billing_core' AFTER `timezone`;
ALTER TABLE `users` ADD `vat_reg_no` varchar(100) DEFAULT NULL COMMENT 'billing_core' AFTER `currency`;
ALTER TABLE `users` ADD `country` CHAR(2)  NULL  DEFAULT NULL  COMMENT 'billing_core' AFTER `timezone`;
ALTER TABLE `users` ADD `billing_address_id` int(11) unsigned DEFAULT NULL COMMENT 'billing_core' AFTER `vat_reg_no`;
ALTER TABLE `users` ADD `is_auto_invoiced` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  COMMENT 'billing_core' AFTER `is_notified`;
ALTER TABLE `users` ADD `auto_invoiced` DATETIME  NULL  COMMENT 'billing_core' AFTER `is_auto_invoiced`;
ALTER TABLE `users` ADD `auto_invoice_frequency` VARCHAR(20)  NOT NULL  DEFAULT 'monthly'  COMMENT 'billing_core'  AFTER `auto_invoiced`;

ALTER TABLE `virtual_users` ADD `currency` char(3) NOT NULL DEFAULT 'EUR' COMMENT 'billing_core' AFTER `timezone`;
ALTER TABLE `virtual_users` ADD `vat_reg_no` varchar(100) DEFAULT NULL COMMENT 'billing_core' AFTER `currency`;
ALTER TABLE `virtual_users` ADD `country` CHAR(2)  NULL  DEFAULT NULL  COMMENT 'billing_core' AFTER `timezone`;
ALTER TABLE `virtual_users` ADD `billing_address_id` int(11) unsigned DEFAULT NULL COMMENT 'billing_core' AFTER `vat_reg_no`;
ALTER TABLE `virtual_users` ADD `is_auto_invoiced` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  COMMENT 'billing_core' AFTER `is_notified`;
ALTER TABLE `virtual_users` ADD `auto_invoiced` DATETIME  NULL  COMMENT 'billing_core' AFTER `is_auto_invoiced`;
ALTER TABLE `virtual_users` ADD `auto_invoice_frequency` VARCHAR(20)  NOT NULL  DEFAULT 'monthly'  COMMENT 'billing_core'  AFTER `auto_invoiced`;

