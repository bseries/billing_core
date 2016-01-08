<?php
/**
 * Billing Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see http://atelierdisko.de/licenses.
 */

namespace billing_core\config;

use billing_core\models\TaxTypes;
use lithium\g11n\Message;

extract(Message::aliases());

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