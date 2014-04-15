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

use cms_core\extensions\cms\Panes;
use lithium\g11n\Message;

extract(Message::aliases());

Panes::registerGroup('cms_billing', 'billing', [
	'title' => $t('Billing'),
	'order' => 80
]);

$base = ['controller' => 'billing', 'library' => 'cms_billing', 'admin' => true];
Panes::registerActions('cms_billing', 'billing', [
	$t('List invoices') => ['controller' => 'Invoices', 'action' => 'index'] + $base,
	$t('New invoice') => ['controller' => 'Invoices', 'action' => 'add'] + $base,
	$t('List payments') => ['controller' => 'Payments', 'action' => 'index'] + $base,
	$t('New payment') => ['controller' => 'Payments', 'action' => 'add'] + $base,
]);

?>