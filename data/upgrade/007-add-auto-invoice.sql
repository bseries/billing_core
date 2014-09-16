ALTER TABLE `users` ADD `is_auto_invoiced` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  AFTER `is_notified`;
ALTER TABLE `virtual_users` ADD `is_auto_invoiced` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  AFTER `is_notified`;

