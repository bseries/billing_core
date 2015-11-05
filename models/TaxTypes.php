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

namespace billing_core\models;

class TaxTypes extends \base_core\models\BaseRegister {

	public static function register($name, array $data = []) {
		$data += [
			'name' => $name,
			'title' => $name,
			// Either percentage as integer or `false` to indicate
			// that no rate is calculated at all.
			'rate' => false,
			'note' => null
		];
		static::$_data[$name] = static::create($data);
	}

	// Detect if beneficiary recipient is business (B) or non-business (C).
	//
	// Type C are private persons AND receiving services for private purposes.
	// Type B are businesses OR receiving services for business purposes.
	//
	// We can take a shorter route here (see "Vertrauensschutz") and rely
	// solely on the presence of the VAT Reg. No.
	public static function recipientType($vatRegNo) {
		return $vatRegNo ? 'B' : 'C';
	}

	// Note: Includes Germany
	// @link http://publications.europa.eu/code/de/de-370100.htm
	public static function isEuTerritory($territory) {
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
	public static function reverseChargeGood($territory) {
		if ($territory == 'DE') {
			return false; // TODO only valid if inside DE
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

	public function title($entity) {
		$value = $entity->data(__FUNCTION__);
		return is_callable($value) ? $value() : $value;
	}

	public function note($entity) {
		$value = $entity->data(__FUNCTION__);
		return is_callable($value) ? $value() : $value;
	}

	/* Deprecated / BC */

	public function name($entity) {
		trigger_error('TaxTypes::name() is deprecated in favor of title().', E_USER_DEPRECATED);
		return $entity->title();
	}
}

?>