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

use cms_core\extensions\cms\Features;
use cms_core\extensions\cms\Settings;
use lithium\g11n\Message;

extract(Message::aliases());

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

Settings::register('cms_billing', 'billing.bankAccount', [
	'holder' => 'App',
	'bank' => 'Lorem Bank Hamburg',
	'bic' => 'ACBCDEE0123',
	'iban' => 'DE1231231123123123123123',
	'code' => '123 12 123',
	'account' => '123 1234 12'
]);

Settings::register('cms_billing', 'billing.paymentTerms', null);
Settings::register('cms_billing', 'billing.vatRegNo', 'DE1231232');
Settings::register('cms_billing', 'billing.taxNo', '12/12/12');

Features::register('cms_billing', 'invoice.sendSentMail', false);
Features::register('cms_billing', 'invoice.sendPaidMail', false);

?>