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

use billing_core\models\Invoices;
use Finance\Price;

class Payments extends \base_core\models\Base {

	use \base_core\models\UserTrait;

	protected $_meta = [
		'source' => 'billing_payments'
	];

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\Localizable' => [
			'fields' => [
				'amount' => 'money'
			]
		]
	];

	public function invoice($entity) {
		return Invoices::find('first', ['conditions' => ['id' => $entity->billing_invoice_id]]);
	}

	// Always gross. Should use money object?
	public function totalAmount($entity) {
		return new Price($entity->amount, $entity->amount_currency, 'gross', null);
	}
}

?>