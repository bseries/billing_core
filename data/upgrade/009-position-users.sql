ALTER TABLE `billing_invoice_positions` ADD `user_id` INT(11)  UNSIGNED  NULL  DEFAULT NULL  AFTER `billing_invoice_id`;
ALTER TABLE `billing_invoice_positions` ADD `virtual_user_id` INT(11)  UNSIGNED  NULL  DEFAULT NULL  AFTER `user_id`;

