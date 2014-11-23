<?php
/**
 * Billing Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

use lithium\net\http\Router;

$persist = ['persist' => ['admin', 'controller']];

Router::connect('/admin/billing/invoices/{:id:[0-9]+}', [
	'controller' => 'Invoices', 'library' => 'billing_core', 'action' => 'view', 'admin' => true
], $persist);
Router::connect('/admin/billing/invoices/{:action}', [
	'controller' => 'Invoices', 'library' => 'billing_core', 'admin' => true
], $persist);
Router::connect('/admin/billing/invoices/{:action}/{:id:[0-9]+}', [
	'controller' => 'Invoices', 'library' => 'billing_core', 'admin' => true
], $persist);

Router::connect('/admin/billing/payments/{:id:[0-9]+}', [
	'controller' => 'payments', 'library' => 'billing_core', 'action' => 'view', 'admin' => true
], $persist);
Router::connect('/admin/billing/payments/add/{:billing_invoice_id:[0-9]+}', [
	'controller' => 'payments', 'action' => 'add', 'library' => 'billing_core', 'admin' => true
], $persist);
Router::connect('/admin/billing/payments/{:action}', [
	'controller' => 'payments', 'library' => 'billing_core', 'admin' => true
], $persist);
Router::connect('/admin/billing/payments/{:action}/{:id:[0-9]+}', [
	'controller' => 'payments', 'library' => 'billing_core', 'admin' => true
], $persist);

?>