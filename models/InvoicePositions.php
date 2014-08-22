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

use billing_core\extensions\finance\Price;
use billing_core\models\Invoices;
use Exception;

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
		return Invoices::find('first', [
			'conditions' => [
				'id' => $entity->billing_invoice_id
			]
		]);
	}

	public static function pending($user) {
		return static::find('all', [
			'conditions' => [
				'user_id' => $user,
				'billing_invoice_id' => null
			]
		]);
	}

	public function amount($entity, $taxZone = null) {
		return new Price(
			$entity->amount,
			$entity->amount_currency,
			$entity->amount_type,
			$taxZone ?: $entity->invoice()->taxZone()
		);
	}

	public function totalAmount($entity, $taxZone = null) {
		return $entity->amount($taxZone)->multiply($entity->quantity);
	}

	// Assumes format "Foobar (#12345)".
	public function itemNumber($entity) {
		if (!preg_match('/\(#(.*)\)/', $entity->description, $matches)) {
			throw new Exception('Failed to extract item number from description.');
		}
		return $matches[1];
	}

	// Assumes format "Foobar (#12345)".
	public function itemTitle($entity) {
		if (!preg_match('/^(.*)\(/', $entity->description, $matches)) {
			throw new Exception('Failed to extract item title from description.');
		}
		return $matches[1];
	}
}

?>