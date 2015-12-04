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
use billing_core\models\TaxTypes;

class ClientGroups extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected static $_data = [];

	public static function register($id, array $data) {
		$data += [
			'id' => $id,
			'title' => null,
			'taxType' => null,
			'conditions' => function($user) { return false; },
			'amountCurrency' => 'EUR',
			'amountType' => 'gross'
		];
		static::$_data[$id] = static::create($data);
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
			if (!isset($options['conditions']['id'])) {
				throw new InvalidArgumentException('No `id` condition given.');
			}
			if (!isset(static::$_data[$key = $options['conditions']['id']])) {
				throw new OutOfBoundsException("Client group `{$key}` not registered.");
			}
			return static::$_data[$options['conditions']['id']];
		}
	}

	public function taxType($entity) {
		return TaxTypes::find('first', ['conditions' => ['name' => $entity->taxType]]);
	}
}

?>