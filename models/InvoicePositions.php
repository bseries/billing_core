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

use cms_billing\models\Invoices;
use SebastianBergmann\Money\Money;
use SebastianBergmann\Money\Currency;

// In the moment of generating an invoice position the price is finalized.
class InvoicePositions extends \cms_core\models\Base {

	protected $_meta = [
		'source' => 'billing_invoice_positions'
	];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp'
	];

	// This fills out all fields open for the position making it non-pending.
	public function finalize($entity, $invoice) {
		return $entity->save(['billing_invoice_id' => $invoice]);
	}

	public static function pending($user) {
		return static::find('all', [
			'conditions' => [
				'user_id' => $user,
				'billing_invoice_id' => null
			]
		]);
	}

	public function totalAmount($entity, $type) {
		$invoice = Invoices::findById($entity->billing_invoice_id);

		$currency = $entity->currency;
		$taxZone = [
			'rate' => $invoice->tax_rate,
			'note' => $invoice->tax_note
		];

		$field = 'total_' . $type;
		return new Money((integer) $entity->$field, new Currency($currency));
	}
}

?>