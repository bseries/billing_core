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

use InvalidArgumentException;
use OutOfBoundsException;
use lithium\core\Environment;
use lithium\util\Collection;

class TaxTypes extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected static $_data = [];

	public static function register($id, array $data) {
		$data += [
			'id' => $id,
			'name' => function($locale) {
				return null;
			},
			'title' => function($locale) {
				return null;
			},
			// Either percentage as integer or `false` to indicate
			// that no rate is calculated at all.
			'rate' => false,
			'note' => null
		];
		static::$_data[$id] = static::create($data);
	}

	public static function find($type, array $options = []) {
		if ($type == 'all') {
			return new Collection(['data' => static::$_data]);
		} elseif ($type == 'first') {
			if (!isset($options['conditions']['id'])) {
				throw new InvalidArgumentException('No `id` condition given.');
			}
			if (!isset(static::$_data[$key = $options['conditions']['id']])) {
				throw new OutOfBoundsException("Tax type `{$key}` not registered.");
			}
			return static::$_data[$key];
		} elseif ($type == 'list') {
			$results = [];

			foreach (static::$_data as $item) {
				$results[$item->id] = $item->title();
			}
			return $results;
		}
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

	public function name($entity) {
		$value = $entity->data(__FUNCTION__);
		return is_callable($value) ? $value() : $value;
	}

	public function title($entity) {
		$value = $entity->data(__FUNCTION__);
		return is_callable($value) ? $value() : $value;
	}

	public function note($entity) {
		$value = $entity->data(__FUNCTION__);
		return is_callable($value) ? $value() : $value;
	}
}

?>