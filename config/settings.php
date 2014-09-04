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

use base_core\extensions\cms\Features;
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

Settings::register('tax.vat.title', $t('VAT'));
Settings::register('tax.vat.rate', 19);

Settings::register('tax.reducedVat.title', $t('red. VAT'));
Settings::register('tax.reducedVat.rate', 7);

Settings::register('billing.bankAccount', [
	'holder' => 'App',
	'bank' => 'Lorem Bank Hamburg',
	'bic' => 'ACBCDEE0123',
	'iban' => 'DE1231231123123123123123',
	'code' => '123 12 123',
	'account' => '123 1234 12'
]);

Settings::register('billing.paymentTerms', null);
Settings::register('billing.vatRegNo', 'DE1231232');
Settings::register('billing.taxNo', '12/12/12');

Features::register('invoice.sendSentMail', false);
Features::register('invoice.sendPaidMail', false);

?>