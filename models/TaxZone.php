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

namespace cms_banner\models;

use lithium\g11n\Message;
use lithium\analysis\Logger;

// In the moment of generating an invoice position the price is finalized.
class TaxZone extends \cms_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	public static function create($territory, $vatRegNo, $locale) {
		extract(Message::aliases());

		// Enable if becomes required.
		// if ($vatRegNo && $this->_mustValidateVatRegNo($territory)) {
		//	if (!$this->_validateVatRegNo($vatRegNo)) {
		//		return false;
		//	}
		//}

		// National
		if ($territory == 'DE') {
			return parent::create([
				'name' => 'National, Germany',
				'rate' => $rate = 0.19,
				'note' => $t('Includes {:rate}% VAT.', compact('locale') + ['rate' => $rate * 100])
			]);
		}

		// EU
		if (static::_isEuTerritory($territory)) {
			// German VAT applies, § 3 a Abs. 1 S. 2 UStG
			if (static::_recipientType($vatRegNo) == 'C') {
				return parent::create([
					'name' => 'Inter-community, country inside EU',
					'rate' => $rate = 0.19,
					'note' => $t('Includes {:rate}% VAT.', compact('locale') + ['rate' => $rate * 100])
				]);
			}
			// Reverse charge, § 13 b UStG
			return parent::create([
				'name' => 'Inter-community, country inside EU',
				'rate' => 0,
				// For translations see:
				// @link http://www.hk24.de/recht_und_steuern/steuerrecht/umsatzsteuer_mehrwertsteuer/umsatzsteuer_mehrwertsteuer_international/644156/Uebersetzung_Steuerschuldnerschaft_des_Leistungsempfaengers.html
				'note' => $t('Reverse Charge.', compact('locale'))
			]);
		}

		// Third country
		// Reverse charge, B2B, § 13 b UStG
		// Reverse charge, B2C + Katalogleistung
		if (!static::_reverseChargeGood($territory)) {
			Logger::write('error', "Territory {$territory} not good for reverse charge.");
			return false;
		}
		return parent::create([
			'name' => 'International, third-country outside EU',
			'rate' => 0,
			'note' => $t('Reverse Charge.', compact('locale'))
		]);
	}

	// Detect if beneficiary recipient is business (B) or non-business (C).
	//
	// Type C are private persons AND receiving services for private purposes.
	// Type B are businesses OR receiving services for business purposes.
	//
	// We can take a shorter route here (see "Vertrauensschutz") and rely
	// solely on the presence of the VAT Reg. No.
	protected static function _recipientType($vatRegNo) {
		return $vatRegNo ? 'B' : 'C';
	}

	// Note: Includes Germany
	// @link http://publications.europa.eu/code/de/de-370100.htm
	protected static function _isEuTerritory($territory) {
		$territories = [
			'BE', 'BG', 'CZ', 'DE', 'DK', 'EE', 'IE', 'EL', 'ES', 'FR',
			'IT', 'CY', 'LV', 'LT', 'LU', 'HU', 'MT', 'NL', 'AT', 'PL',
			'PT', 'RO', 'SI', 'SK', 'FI', 'SE', 'GB'
		];
		return in_array($territory, $territories);
	}

	// Checks if given territory is good for reverse charge.
	//
	// @fixme Failing here shouldn't happen but when it does we need to
	// reread and apply regulations:
	// "Prüfung, ob die umsatzsteuerliche Registrierung des
	// Leistenden oder die Einsetzung eines Fiskalvertreters im
	// jeweiligen anderen EU-Mitgliedstaat notwendig ist."
	//
	// @link http://www.ihk-bonn.de/fileadmin/dokumente/Downloads/Recht_und_Steuern/Umsatzsteuer_National_Vorsteuer/BMF-Schreiben-Gegenseitigkeit10-07.pdf
	protected static function _reverseChargeGood($territory) {
		if ($territory == 'DE') {
			return false;
		}
		if (static::_isEuTerritory($territory)) {
			return true;
		}
		// "Gegenseitigkeit gegeben", § 18 Abs. 9 Satz 6 UStG
		$territories = [
			'AD', // Andorra
			'AG', // Antigua und Barbuda
			'AU', // Australien
			'BS', // Bahamas
			'BH', // Bahrain
			'BM', // Bermudas
			'BA', // Bosnien und Herzegowina
			'VG', // Britische Jungferninseln
			'BN', // Brunaei Darussalam
			'KY', // Cayman-Insel
			'TW', // China (Taiwan)
			'GI', // Gibralatar
			'GD', // Grenada
			'GL', // Grönland
			'GG', // Guernsey
			'HK', // Hongkong (VR China)
			'IQ', // Irak
			'IR', // Iran
			'IS', // Island
			'IL', // Israel
			'JM', // Jamaika
			'JP', // Japan
			'JE', // Jersey
			'CA', // Kanada
			'QA', // Katar
			'KP', // Korea, Dem. Volksrepublik
			'KR', // Korea, Republik
			'HR', // Kroatien
			'KW', // Kuwait
			'LB', // Libanon
			'LR', // Liberia
			'LY', // Lybien
			'LI', // Liechtenstein
			'MO', // Macao
			'MV', // Malediven
			'MK', // Mazedonien
			'AN', // Niederlöndische Antillen
			'NO', // Norwegen
			'OM', // Oman
			'PK', // Pakistan
			'SB', // Salomonen
			'SM', // San Marino
			'SA', // Saudi-Arabien
			'CH', // Schweiz
			'VC', // St. Vincent und die Grenadinen
			'SZ', // Swasiland
			'VA', // Vatikan
			'AE', // Vereinigte Arabische Emirate
			'US'  // USA
		];
		return in_array($territory, $territories);
	}

	// VAT reg no must be validated inside EU
	// Germany B2B                        : need to check VAT with Bundeszentralamt für Steuern
	// EU B2B                             : need to check VAT with Bundeszentralamt für Steuern
	// third country B2B                  : Do not need to check VAT reg no because reverse charge applies
	// third countr B2C + Katalogleistung : Do not need to check VAT reg no because reverse charge applies
	protected static function _mustValidateVatRegNo($territory) {
		return static::_isEuTerritory($territory); // Implictly includes DE.
	}

	// @todo implement, stub
	protected static function _validateVatRegNo($vatRegNo) {
		return true;
	}
}

?>