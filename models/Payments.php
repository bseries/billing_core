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
use billing_core\extensions\finance\Price;

class Payments extends \cms_core\models\Base {

	use \cms_core\models\UserTrait;

	protected $_meta = [
		'source' => 'billing_payments'
	];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp',
		'cms_core\extensions\data\behavior\Localizable' => [
			'fields' => [
				'amount' => 'money'
			]
		]
	];

	public function invoice($entity) {
		return Invoices::find('first', ['conditions' => ['id' => $entity->billing_invoice_id]]);
	}

	// Always gross.
	public function totalAmount($entity) {
		return new Price($entity->amount, $entity->amount_currency, 'gross', null);
	}
}

?>