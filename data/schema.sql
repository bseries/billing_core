# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: localhost (MySQL 10.0.10-MariaDB-log)
# Datenbank: rainmap
# Erstellungsdauer: 2014-06-03 14:11:54 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Export von Tabelle billing_invoice_positions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `billing_invoice_positions`;

CREATE TABLE `billing_invoice_positions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `billing_invoice_id` int(11) unsigned DEFAULT NULL COMMENT 'NULL until assigned to invoice',
  `description` varchar(250) NOT NULL,
  `quantity` decimal(10,2) unsigned NOT NULL DEFAULT '1.00',
  `amount_currency` char(3) NOT NULL DEFAULT 'EUR',
  `amount_type` char(5) NOT NULL DEFAULT 'net',
  `amount` int(10) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `billing_invoice_id` (`billing_invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Export von Tabelle billing_invoices
# ------------------------------------------------------------

DROP TABLE IF EXISTS `billing_invoices`;

CREATE TABLE `billing_invoices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `virtual_user_id` int(11) unsigned DEFAULT NULL,
  `number` varchar(100) NOT NULL DEFAULT '',
  `date` date NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'created',
  `total_currency` char(3) NOT NULL DEFAULT 'EUR' COMMENT 'Currency in which the invoice should be paid.',
  `tax_rate` int(4) unsigned NOT NULL,
  `tax_note` varchar(250) NOT NULL DEFAULT '',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Export von Tabelle billing_payments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `billing_payments`;

CREATE TABLE `billing_payments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `virtual_user_id` int(11) unsigned DEFAULT NULL,
  `billing_invoice_id` int(11) unsigned DEFAULT NULL,
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




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

ALTER TABLE `users` ADD `billing_currency` char(3) NOT NULL DEFAULT 'EUR' AFTER `timezone`;
ALTER TABLE `users` ADD `billing_vat_reg_no` varchar(100) DEFAULT NULL AFTER `billing_currency`;
ALTER TABLE `users` ADD `billing_address_id` int(11) unsigned DEFAULT NULL AFTER `billing_vat_reg_no`;
ALTER TABLE `users` ADD `invoiced` DATETIME  NULL  AFTER `is_notified`;
ALTER TABLE `users` ADD `billing_tax_country` CHAR(2)  NULL  DEFAULT NULL  AFTER `billing_vat_reg_no`;

ALTER TABLE `virtual_users` ADD `billing_currency` char(3) NOT NULL DEFAULT 'EUR' AFTER `timezone`;
ALTER TABLE `virtual_users` ADD `billing_vat_reg_no` varchar(100) DEFAULT NULL AFTER `billing_currency`;
ALTER TABLE `virtual_users` ADD `billing_address_id` int(11) unsigned DEFAULT NULL AFTER `billing_vat_reg_no`;
ALTER TABLE `virtual_users` ADD `invoiced` DATETIME  NULL  AFTER `is_notified`;
ALTER TABLE `virtual_users` ADD `billing_tax_country` CHAR(2)  NULL  DEFAULT NULL  AFTER `billing_vat_reg_no`;

