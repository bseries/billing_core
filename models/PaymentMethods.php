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

use AD\Finance\Price\NullPrice;
use lithium\util\Collection;

class PaymentMethods extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected $_actsAs = [
		'base_core\extensions\data\behavior\Access'
	];

	protected static $_data = [];

	public static function register($name, array $data) {
		$data += [
			'id' => $name,
			'name' => $name,
			'title' => function() {
				return null;
			},
			'access' => ['user.role:admin'],
			'price' => function($user, $cart) {
				return new NullPrice();
			},
			'info' => function($context, $format, $renderer, $order) {
				// Dependent on $format return either HTML or plaintext.
				return null;
			}
		];
		$data['access'] = (array) $data['access'];
		static::$_data[$name] = static::create($data);
	}

	public static function find($type, array $options = []) {
		if ($type == 'all') {
			return new Collection(['data' => static::$_data]);
		} elseif ($type == 'first') {
			return static::$_data[$options['conditions']['id']];
		} elseif ($type == 'list') {
			$results = [];

			foreach (static::$_data as $item) {
				$results[$item->id] = $item->title();
			}
			return $results;
		}
	}

	public function title($entity) {
		$value = $entity->data('title');
		return is_callable($value) ? $value() : $value;
	}

	public function price($entity, $user, $cart) {
		$value = $entity->data('price');
		return is_callable($value) ? $value($user, $cart) : $value;
	}

	public function info($entity, $context, $format, $renderer, $order) {
		$value = $entity->data('info');
		return is_callable($value) ? $value($context, $format, $renderer, $order) : $value;
	}
}

?>