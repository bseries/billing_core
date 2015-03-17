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

use billing_core\models\TaxTypes;
use lithium\g11n\Message;

extract(Message::aliases());

// Tax that applies on all goods when business resides in Germany.
// B2B & B2C
TaxTypes::register('DE.vat.standard', [
	'name' => 'MwSt',
	'title' => $t('VAT Standard DE', ['scope' => 'billing_core']),
	'rate' => 19,
	'note' => $t('Includes 19% VAT.', ['scope' => 'billing_core'])
]);

// Tax that applies on certain goods when business resides in Germany.
TaxTypes::register('DE.vat.reduced', [
	'name' => 'red. MwSt',
	'title' => $t('VAT Reduced DE', ['scope' => 'billing_core']),
	'rate' => 7,
	'note' => $t('Includes 7% VAT.', ['scope' => 'billing_core'])
]);

// Applies under certain circumstances worldwide.
TaxTypes::register('*.vat.reverse', [
	'name' => null,
	'title' => $t('VAT Reverse Charge', ['scope' => 'billing_core']),
	'rate' => false,
	'note' => $t('Reverse Charge.', ['scope' => 'billing_core'])
]);

?>