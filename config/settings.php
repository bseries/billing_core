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

namespace billing_core\config;

use base_core\extensions\cms\Settings;
use lithium\g11n\Message;

extract(Message::aliases());

Settings::register('contact.billing', Settings::read('contact.default'));

// Number Format
Settings::register('invoice.number', [
	'sort' => '/([0-9]{4}[0-9]{4})/',
	'extract' => '/[0-9]{4}([0-9]{4})/',
	'generate' => '%Y%%04.d'
]);

// Overdue, set to false if never overdue.
// Parsed with strtotime.
Settings::register('invoice.overdueAfter', '+2 weeks');
Settings::register('invoice.sendPaidMail', false);

// Enable/disable auto mailing invoices to recipients once one becomes availablet.
Settings::register('invoice.autoSend', false);

Settings::register('service.paypal.default', [
	'email' => null
]);
Settings::register('service.bank.default', [
	'holder' => null,
	'bank' => null,
	'bic' => null,
	'iban' => null,
	'code' => null,
	'account' => null
]);

Settings::register('billing.paymentTerms', function($user) use ($t) {
	return null;
});

Settings::register('billing.vatRegNo', 'DE1231232');
Settings::register('billing.taxNo', '12/12/12');


?>