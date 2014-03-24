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

namespace cms_billing\models;

use cms_billing\extensions\finance\Price;
use cms_billing\models\Invoices;

// In the moment of generating an invoice position the price is finalized.
class InvoicePositions extends \cms_core\models\Base {

	protected $_meta = [
		'source' => 'billing_invoice_positions'
	];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp',
		'cms_core\extensions\data\behavior\Localizable' => [
			'fields' => [
				'amount' => 'money',
				'quantity' => 'decimal'
			]
		]
	];

	public function invoice($entity) {
		return Invoices::findById($entity->billing_invoice_id);
	}

	public static function pending($user) {
		return static::find('all', [
			'conditions' => [
				'user_id' => $user,
				'billing_invoice_id' => null
			]
		]);
	}

	public function amount($entity) {
		return new Price(
			$entity->amount,
			$entity->amount_currency,
			$entity->amount_type,
			$entity->invoice()->taxZone()
		);
	}

	public function totalAmount($entity) {
		return $entity->amount()->multiply($entity->quantity);
	}
}

?>