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

define('BILLING_CORE_VERSION', '1.2.0');

require 'settings.php';
// require 'media.php';
require 'panes.php';
require 'widgets.php';

use billing_core\models\Taxes;
use lithium\g11n\Message;

extract(Message::aliases());

$vatApplicationType = function($territory, $vatRegNo) {
	if ($territory === 'DE') {
		// National
		return 'full';
	}
	if (Taxes::isEuTerritory($territory)) {
		if (Taxes::recipientType($vatRegNo) === 'C') {
			// German VAT applies, § 3 a Abs. 1 S. 2 UStG
			return 'full';
		}
		// Reverse charge, § 13 b UStG
		return 'reverse-charge';
	}
	// Reverse charge, B2B, § 13 b UStG
	// Reverse charge, B2C + Katalogleistung
	if (Taxes::reverseChargeGood($territory)) {
		return 'reverse-charge';
	}
};

// Tax that applies on all goods when business resides in Germany.
Taxes::register('DE.vat.standard', [
	'title' => $t('VAT Standard DE'),
	'rate' => function($territory, $vatRegNo) use ($vatApplicationType) {
		$type = $vatApplicationType($territory, $vatRegNo);

		if ($type === 'full') {
			return 19;
		}
	},
	'note' => $taxNote = function($territory, $vatRegNo, $rate, $locale) use ($t, $vatApplicationType) {
		$type = $vatApplicationType($territory, $vatRegNo);

		if ($type === 'full') {
			return $t('Includes {:rate}% VAT.', compact('locale'));
		}
		if ($type === 'reverse-charge') {
			return $t('Reverse Charge');
		}
	}
]);

Taxes::register('DE.vat.reduced', [
	'title' => $t('VAT Reduced DE'),
	'rate' => function($territory, $vatRegNo) use ($vatApplicationType) {
		$type = $vatApplicationType($territory, $vatRegNo);

		if ($type === 'full') {
			return 7;
		}
	},
	'note' => $taxNote
]);

?>