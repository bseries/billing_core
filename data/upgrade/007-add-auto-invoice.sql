ALTER TABLE `users` ADD `is_auto_invoiced` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0' COMMENT 'billing_core' AFTER `is_notified`;
ALTER TABLE `users` ADD `auto_invoiced` DATETIME  NULL  DEFAULT NULL  COMMENT 'billing_core' AFTER `is_auto_invoiced`;
ALTER TABLE `users` ADD `auto_invoice_frequency` VARCHAR(20)  NOT NULL  DEFAULT 'monthly'  COMMENT 'billing_core'  AFTER `auto_invoiced`;

ALTER TABLE `virtual_users` ADD `is_auto_invoiced` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0' COMMENT 'billing_core' AFTER `is_notified`;
ALTER TABLE `virtual_users` ADD `auto_invoiced` DATETIME  NULL  DEFAULT NULL  COMMENT 'billing_core' AFTER `is_auto_invoiced`;
ALTER TABLE `virtual_users` ADD `auto_invoice_frequency` VARCHAR(20)  NOT NULL  DEFAULT 'monthly'  COMMENT 'billing_core'  AFTER `auto_invoiced`;

