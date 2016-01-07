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
use UnexpectedValueException;

// This model is an abstract represenation of payment methods. This class extends the
// `BaseRegister` class, which also has all the information on how to add and retrieve new
// methods/types as well as how access is checked.
//
// @see base_core\models\BaseRegister
// @see billing_core\models\PaymentMethods::_register()
class PaymentMethods extends \base_core\models\BaseRegister {

	protected static function _register(array $data) {
		return $data + [
			// The (display) title of the method, can also be an anonymous function.
			// @see billing_core\models\PaymentMethods::title()
			'title' => $data['name'],

			// @see billing_core\models\PaymentMethods::gateway()
			'gateway' => null,

			// Set of conditions of which any must be fulfilled, so
			// that the method is made available to a user.
			'access' => ['user.role:admin'],

			// The fee applied when using the payment method.
			'price' => new NullPrice(),

			// Dependent on $format return either HTML or plaintext. Can be an anonymous function.
			// @see billing_core\models\PaymentMethods::info()
			'info' => null
		];
	}

	// Retrieves the Gateway object for the payment method. Each payment method
	// specifies which gateway it intends to use, using the following notation.
	//
	// Gateway provided by the B-Series : `'Banque.PayPal'`
	// --------- " ------- Omnipay      : `'Omnipay.PayPal'`
	//
	// @link http://omnipay.thephpleague.com/
	public function gateway($entity) {
		if (!$entity->gateway) {
			return false;
		}
		$name = explode('.', $entity->gateway, 2) + [null, null];

		if ($name[0] === 'Omnipay') {
			return Omnipay::create($name[1]);
		}
		throw new UnexpectedValueException("Invalid gateway name `{$entity->gateway}`.");
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