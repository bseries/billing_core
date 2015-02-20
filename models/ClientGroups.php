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

namespace billing_core\models;

use billing_core\models\TaxTypes;

class ClientGroups extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected static $_data = [];

	public static function register($name, array $data) {
		$data += [
			'id' => $name,
			'title' => null,
			'taxType' => null,
			'conditions' => function($user) { return false; },
			'amountCurrency' => 'EUR',
			'amountType' => 'gross'
		];
		static::$_data[$name] = static::create($data);
	}

	public static function find($type, array $options = []) {
		if ($type == 'all') {
			return static::$_data;
		} elseif ($type == 'first') {
			if (isset($options['conditions']['user'])) {
				foreach (static::$_data as $name => $item) {
					$conditions = $item->conditions;
					if ($conditions($options['conditions']['user'])) {
						return $item;
					}
				}
				return false;
			}
			return static::$_data[$options['conditions']['id']];
		}
	}

	public function taxType($entity) {
		return TaxTypes::find('first', ['conditions' => ['id' => $entity->taxType]]);
	}
}

?>