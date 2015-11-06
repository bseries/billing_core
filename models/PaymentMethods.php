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

class PaymentMethods extends \base_core\models\BaseRegister {

	protected static function _register(array $data) {
		return $data + [
			'title' => $data['name'],
			'access' => ['user.role:admin'],
			'price' => new NullPrice(),
			// Dependent on $format return either HTML or plaintext.
			'info' => null
		];
	}

	public function gateway($entity) {
		if (!$entity->gateway) {
			return falsE;
		}
		// i.e. omnipay.paypal.foo or banque.blaFasel
		if ($entity->gateway['name'] === 'omnipay') {
			$name = explode('.', $entity->gateway['name']);
			$name = implode('.', array_slice($name, 1));

			return Omnipay::create($name);
		}
	}

	public function title($entity) {
		$value = $entity->data(__FUNCTION__);
		return is_callable($value) ? $value() : $value;
	}

	public function price($entity, $user, $cart) {
		$value = $entity->data(__FUNCTION__);
		return is_callable($value) ? $value($user, $cart) : $value;
	}

	public function info($entity, $context, $format, $renderer, $order) {
		$value = $entity->data(__FUNCTION__);
		return is_callable($value) ? $value($context, $format, $renderer, $order) : $value;
	}
}

?>