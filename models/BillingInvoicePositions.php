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

namespace cms_banner\models;

// In the moment of generating an invoice position the price is finalized.
class BillingInvoicePositions extends \cms_core\models\Base {

	/*
	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp'
	];
	*/

	public $belongsTo = [
		'User',
		'BillingInvoice'
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
}

?>