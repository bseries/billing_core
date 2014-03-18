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

use SebastianBergmann\Money\Money;
use SebastianBergmann\Money\Currency;

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

	public function totalAmount($entity) {
		if (!$entity->amount || !$entity->currency) {
			return false;
		}
		return new Money((integer) $entity->amount, new Currency($entity->currency));
	}
}

?>