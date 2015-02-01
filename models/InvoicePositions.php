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

use Exception;
use Finance\Price;
use billing_core\models\Invoices;
use billing_core\models\TaxTypes;

// In the moment of generating an invoice position the price is finalized.
class InvoicePositions extends \base_core\models\Base {

	protected $_meta = [
		'source' => 'billing_invoice_positions'
	];

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\Localizable' => [
			'fields' => [
				'amount' => 'money',
				'quantity' => 'decimal'
			]
		]
	];

	public $belongsTo = [
		'Invoice' => [
			'to' => 'billing_core\models\Invoices',
			'key' => 'billing_invoice_id'
		]
	];

	public function invoice($entity) {
		return $entity->invoice ?: Invoices::find('first', [
			'conditions' => [
				'id' => $entity->billing_invoice_id
			]
		]);
	}

	public static function pending($user) {
		return static::find('all', [
			'conditions' => [
				'user_id' => $user->id,
				'billing_invoice_id' => null
			]
		]);
	}

	public function amount($entity) {
		return new Price(
			(integer) $entity->amount,
			$entity->amount_currency,
			$entity->amount_type,
			(integer) $entity->tax_rate
		);
	}

	public function total($entity) {
		return $entity->amount()->multiply($entity->quantity);
	}

	public function taxType($entity) {
		return TaxTypes::find('first', ['conditions' => ['id' => $entity->tax_type]]);
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

	/* Deprecated */

	public function totalAmount($entity) {
		trigger_error('InvoicePositions::totalAmount has been deprecated in favor of total().', E_USER_DEPRECATED);
		return $entity->total();
	}
}

?>