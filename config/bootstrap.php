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
		$t('List Invoices') => ['controller' => 'Invoices', 'action' => 'index'] + $base,
		$t('New Invoice') => ['controller' => 'Invoices', 'action' => 'add'] + $base,
	]
]);

// Number Format
//
// Parsed with sprintf.
// Parsed with strftime.
Settings::register('cms_billing', 'invoiceNumberPattern.number', '%04.d');
Settings::register('cms_billing', 'invoiceNumberPattern.prefix', '%Y-');

// Overdue, set to false if never overdue.
// Parsed with strtotime.
Settings::register('cms_billing', 'invoiceOverdueAfter', '+2 weeks');

?>