ALTER TABLE `billing_invoices` ADD `tax_type` VARCHAR(20)  NOT NULL  DEFAULT ''  AFTER `tax_note`;
ALTER TABLE `billing_invoice_positions` DROP `tax_type`;
ALTER TABLE `billing_invoice_positions` CHANGE `tax_rate` `amount_rate` INT(5)  UNSIGNED  NOT NULL  DEFAULT '0';
ALTER TABLE `billing_invoice_positions` MODIFY COLUMN `amount_rate` INT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `amount_type`;
ALTER TABLE `billing_invoice_positions` MODIFY COLUMN `amount` INT(10) NOT NULL AFTER `quantity`;

