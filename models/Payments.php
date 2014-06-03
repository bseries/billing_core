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
use cms_billing\extensions\finance\Price;
use cms_core\models\Users;
use cms_core\models\VirtualUsers;

class Payments extends \cms_core\models\Base {

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

	public function user($entity) {
		if ($entity->user_id) {
			return Users::find('first', ['conditions' => ['id' => $entity->user_id]]);
		}
		return VirtualUsers::find('first', ['conditions' => ['id' => $entity->virtual_user_id]]);
	}

	public function invoice($entity) {
		return Invoices::find('first', ['conditions' => ['id' => $entity->billing_invoice_id]]);
	}

	// Always gross.
	public function totalAmount($entity) {
		return new Price($entity->amount, $entity->amount_currency, 'gross', null);
	}
}

?>