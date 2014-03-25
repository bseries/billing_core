<?php
/**
 * Bureau Billing
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

use cms_core\extensions\cms\Settings;
use cms_core\extensions\cms\Panes;
use lithium\g11n\Message;

require_once dirname(__DIR__) . '/libraries/autoload.php';

extract(Message::aliases());

Panes::register('cms_billing', 'billing', [
	'title' => $t('Billing'),
	'group' => Panes::GROUP_AUTHORING,
	'url' => $base = ['controller' => 'billing', 'library' => 'cms_billing', 'admin' => true],
	'actions' => [
		$t('List invoices') => ['controller' => 'Invoices', 'action' => 'index'] + $base,
		$t('New invoice') => ['controller' => 'Invoices', 'action' => 'add'] + $base,
		$t('New payment') => ['controller' => 'payments', 'action' => 'add'] + $base,
	]
]);

// Number Format
Settings::register('cms_billing', 'invoice.number', [
	'sort' => '/([0-9]{4}[0-9]{4})/',
	'extract' => '/[0-9]{4}([0-9]{4})/',
	'generate' => '%Y%%04.d'
]);

// Overdue, set to false if never overdue.
// Parsed with strtotime.
Settings::register('cms_billing', 'invoice.overdueAfter', '+2 weeks');

Settings::register('cms_billing', 'tax.vat.title', $t('VAT'));
Settings::register('cms_billing', 'tax.vat.rate', 19);

Settings::register('cms_billing', 'tax.reducedVat.title', $t('red. VAT'));
Settings::register('cms_billing', 'tax.reducedVat.rate', 7);

?>