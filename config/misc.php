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

use base_core\security\Gate;
use billing_core\models\TaxTypes;
use li3_access\security\Access;
use lithium\g11n\Message;

extract(Message::aliases());

// Register additional roles.
Gate::registerRole('merchant');
Gate::registerRole('customer');

// Add additional entity rules.
Access::add('entity', 'user.role:merchant', function($user, $entity) {
	return $user->role == 'merchant';
});
Access::add('entity', 'user.role:customer', function($user, $entity) {
	return $user->role == 'customer';
});

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