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

use billing_core\models\TaxTypes;
use lithium\g11n\Message;

extract(Message::aliases());

// Tax that applies on all goods when business resides in Germany.
// B2B & B2C
TaxTypes::register('DE.vat.standard', [
	'name' => function($locale) use ($t) {
		return $t('VAT', ['scope' => 'billing_core', 'locale' => $locale]);
	},
	'title' => function($locale) use ($t) {
		return $t('VAT Standard DE', ['scope' => 'billing_core', 'locale' => $locale]);
	},
	'rate' => 19,
	'note' => $t('Includes 19% VAT.', ['scope' => 'billing_core'])
]);

// Tax that applies on certain goods when business resides in Germany.
TaxTypes::register('DE.vat.reduced', [
	'name' => function($locale) use ($t) {
		return $t('red. VAT', ['scope' => 'billing_core', 'locale' => $locale]);
	},
	'title' => function($locale) use ($t) {
		return $t('VAT Reduced DE', ['scope' => 'billing_core', 'locale' => $locale]);
	},
	'rate' => 7,
	'note' => $t('Includes 7% VAT.', ['scope' => 'billing_core'])
]);

// Applies under certain circumstances worldwide.
TaxTypes::register('*.vat.reverse', [
	'name' => null,
	'title' => function($locale) use ($t) {
		return $t('VAT Reverse Charge', ['scope' => 'billing_core', 'locale' => $locale]);
	},
	'rate' => false,
	'note' => $t('Reverse Charge.', ['scope' => 'billing_core'])
]);

?>