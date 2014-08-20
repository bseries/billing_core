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

Panes::register('billing', [
	'title' => $t('Billing'),
	'order' => 80
]);

$base = ['controller' => 'billing', 'library' => 'cms_billing', 'admin' => true];
Panes::register('billing.invoices', [
	'title' => $t('Invoices'),
	'url' => ['controller' => 'Invoices', 'action' => 'index'] + $base
]);
Panes::register('billing.payments', [
	'title' => $t('Payments'),
	'url' => ['controller' => 'Payments', 'action' => 'index'] + $base
]);

?>