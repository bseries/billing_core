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

use lithium\net\http\Router;

$persist = ['persist' => ['admin', 'controller']];

Router::connect('/admin/billing/{:id:[0-9]+}', array(
	'controller' => 'Billing', 'library' => 'cms_billing', 'action' => 'view', 'admin' => true
), $persist);
Router::connect('/admin/billing/{:action}', array(
	'controller' => 'Billing', 'library' => 'cms_billing', 'admin' => true
), $persist);
Router::connect('/admin/billing/{:action}/{:id:[0-9]+}', array(
	'controller' => 'Billing', 'library' => 'cms_billing', 'admin' => true
), $persist);

Router::connect('/admin/billing/invoices/{:id:[0-9]+}', array(
	'controller' => 'BillingInvoices', 'library' => 'cms_billing', 'action' => 'view', 'admin' => true
), $persist);
Router::connect('/admin/billing/invoices/{:action}', array(
	'controller' => 'BillingInvoices', 'library' => 'cms_billing', 'admin' => true
), $persist);
Router::connect('/admin/billing/invoices/{:action}/{:id:[0-9]+}', array(
	'controller' => 'BillingInvoices', 'library' => 'cms_billing', 'admin' => true
), $persist);

?>