ALTER TABLE `users` ADD `invoiced` DATETIME  NULL  AFTER `is_notified`;
ALTER TABLE `virtual_users` ADD `invoiced` DATETIME  NULL  AFTER `is_notified`;

