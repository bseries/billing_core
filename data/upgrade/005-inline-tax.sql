ALTER TABLE `billing_invoice_positions` ADD `tax_type` VARCHAR(20)   NOT NULL  AFTER `quantity`;
ALTER TABLE `billing_invoice_positions` ADD `tax_rate` INT(5)  UNSIGNED  NOT NULL  DEFAULT '0'  AFTER `tax_type`;

-- Must migrate invoice tax_rate to position tax_rate AND tax_note.
-- ALTER TABLE `billing_invoices` DROP `tax_rate`;


