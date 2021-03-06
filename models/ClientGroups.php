<?php
/**
 * Billing Core
 *
 * Copyright (c) 2015 David Persson - All rights reserved.
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace billing_core\models;

use InvalidArgumentException;
use OutOfBoundsException;
use billing_core\billing\TaxTypes;

// @deprecated
class ClientGroups extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected static $_data = [];

	public static function register($id, array $data) {
		trigger_error('Deprecated in favor of billing\ClientGroup.', E_USER_DEPRECATED);

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
		trigger_error('Deprecated in favor of billing\ClientGroup.', E_USER_DEPRECATED);

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
		trigger_error('Deprecated in favor of billing\ClientGroup.', E_USER_DEPRECATED);

		return TaxTypes::registry($entity->taxType);
	}
}

?>