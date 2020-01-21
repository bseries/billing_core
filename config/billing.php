<?php
/**
 * Billing Core
 *
 * Copyright (c) 2014 David Person - All rights reserved.
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace billing_core\config;

use billing_core\billing\TaxTypes;
use lithium\g11n\Message;
use base_core\extensions\cms\Settings;

extract(Message::aliases());

//
// Contacts
//
if (!$primaryContact = Settings::read('contact.primary')) {
	trigger_error('No primary contact found, using deprecated default.', E_USER_DEPRECATED);
	$primaryContact = Settings::read('contact.default');
}
Settings::register('contact.billing', $primaryContact + [
	'vat_reg_no' => null, // i.e. 'DE123123123'
	'tax_no' => null, // i.e. '12/12/12'
]);

//
// Tax Types
//
// Tax that applies on all goods when business resides in Germany.
// B2B & B2C
TaxTypes::register('DE.vat.standard', [
	'rate' => 19,
	'title' => function() use ($t) {
		return $t('VAT', ['scope' => 'billing_core']);
	},
	'note' => function() use ($t) {
		return $t('Includes 19% VAT.', ['scope' => 'billing_core']);
	}
]);

// Tax that applies on certain goods when business resides in Germany.
TaxTypes::register('DE.vat.reduced', [
	'rate' => 7,
	'title' => function() use ($t) {
		return $t('red. VAT', ['scope' => 'billing_core']);
	},
	'note' => function() use ($t) {
		return $t('Includes 7% VAT.', ['scope' => 'billing_core']);
	}
]);

// Applies under certain circumstances worldwide.
TaxTypes::register('*.vat.reverse', [
	'rate' => false,
	'title' => function() use ($t) {
		return $t('VAT Reverse Charge', ['scope' => 'billing_core']);
	},
	'note' => function() use ($t) {
		return $t('Reverse Charge.', ['scope' => 'billing_core']);
	}
]);

?>