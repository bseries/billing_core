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

use cms_core\models\Addresses;
use cms_core\models\Users;
use cms_core\extensions\cms\Settings;
use cms_billing\models\Payments;
use cms_billing\models\TaxZones;
use cms_billing\models\InvoicePositions;
use DateTime;
use Exception;
use SebastianBergmann\Money\Money;
use SebastianBergmann\Money\Currency;

// Given our business resides in Germany DE and we're selling services
// which fall und § 3 a Abs. 4 UStG (Katalogleistung).
//
// Denormalizing in order to regenerate invoices
// even when user changes details.
//
// @link http://www.hk24.de/recht_und_steuern/steuerrecht/umsatzsteuer_mehrwertsteuer/umsatzsteuer_mehrwertsteuer_international/367156/USt_grenzueber_Dienstleistungen.html
// @link http://www.revenue.ie/en/tax/vat/leaflets/place-of-supply-of-services.html
// @link http://www.hk24.de/en/international/tax/347922/vat_goods_trading_eu.html
// @link http://www.stuttgart.ihk24.de/recht_und_steuern/steuerrecht/Umsatzsteuer_Verbrauchssteuer/Umsatzsteuer_international/971988/Steuern_und_Abgaben_grenzueberschreitend.html#121
// @link http://www.hk24.de/recht_und_steuern/steuerrecht/umsatzsteuer_mehrwertsteuer/umsatzsteuer_mehrwertsteuer_international/644156/Uebersetzung_Steuerschuldnerschaft_des_Leistungsempfaengers.html
class Invoices extends \cms_core\models\Base {

	protected $_meta = [
		'source' => 'billing_invoices'
	];

	public $belongsTo = ['User'];

	public $hasMany = ['InvoicePosition'];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp'
	];

	public static $enum = [
		'status' => [
			'created', // open
			'sent', // open
			'paid',  // paid
			'void' // storno
		]
	];

	public static function createForUser($user) {
		$item = static::create();

		if ($user->id) {
			$item->user_id = $user->id;
		}

		$item->user_vat_reg_no = $user->vat_reg_no;
		$item = $user->address('billing')->copy($item, 'address_');

		$taxZone = $user->taxZone();
		$item->tax_rate = $taxZone->rate;
		$item->tax_note = $taxZone->note;

		return $item;
	}

	public function positions($entity, array $options = []) {
		$options += ['collectPendingFor' => false];

		if (!$entity->id) {
			return [];
		}
		if ($options['collectPendingFor']) {
			return InvoicePositions::find('all', [
				'conditions' => [
					'or' => [
						'user_id' => $options['collectPendingFor'],
						'billing_invoice_id' => $entity->id
					]
				]
			]);
		}
		return InvoicePositions::find('all', [
			'conditions' => [
				'billing_invoice_id' => $entity->id
			]
		]);
	}

	public function payments($entity) {
		if (!$entity->id) {
			return [];
		}
		return Payments::find('all', [
			'conditions' => [
				'billing_invoice_id' => $entity->id
			]
		]);
	}

	public function isOverdue($entity) {
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $entity->date);
		$overdue = Settings::read('invoiceOverdueAfter');

		if (!$overdue) {
			return false;
		}
		return $entity->total_gross_outstanding & $date->getTimestamp() > strtotime($overdue);
	}

	public static function nextNumber() {
		$pattern = Settings::read('invoiceNumberPattern');

		$item = static::find('first', [
			'conditions' => [
				'number' => [
					'LIKE' => strftime($pattern['prefix']) . '%'
				]
			],
			'order' => ['number' => 'DESC'],
			'fields' => ['number']
		]);
		if ($item && ($number = $item->number)) {
			$number++;
		} else {
			$number = strftime($pattern['prefix']) . sprintf($pattern['number'], 1);
		}
		return $number;
	}

	public function totalAmount($entity, $type) {
		$result = new Money(0, new Currency($entity->currency));

		$positions = $this->positions($entity);

		foreach ($positions as $position) {
			$result = $result->add($position->totalAmount($type));
		}
		return $result;
	}

	public function totalTax($entity) {
		$result = $this->totalAmount($entity, 'gross');
		$result = $result->subtract($this->totalAmount($entity, 'net'));

		return $result;
	}

	public function totalOutstanding($entity, $type) {
		$result = new Money(0, new Currency($entity->currency));

		foreach ($entity->positions() as $position) {
			$result = $result->add($position->totalAmount($type));
		}
		foreach ($entity->payments() as $payment) {
			$result = $result->subtract($payment->totalAmount($type));
		}
		return $result;
	}

	public function paidInFull($entity, $method) {
		$payment = Payments::create([
			'billing_invoice_id' => $entity->id,
			'method' => $method,
			'currency' => $entity->currency,
			'amount' => $entity->totalOutstanding('gross')
		]);
		return $payment->save();
	}

	public function address($entity) {
		return Addresses::createFromPrefixed('address_', $entity->data());
	}
}

// @todo Extract into create method.
Invoices::applyFilter('save', function($self, $params, $chain) {
	static $useFilter = true;

	$entity = $params['entity'];
	$data = $params['data'];

	if (!$useFilter) {
		return $chain->next($self, $params, $chain);
	}
	if ($entity->is_locked || isset($data['is_locked'])) {
		return $chain->next($self, $params, $chain);
	}

	if (!$entity->exists()) {
		$entity->number = Invoices::nextNumber();
	}

	if (!$result = $chain->next($self, $params, $chain)) {
		return false;
	}

	// Save nested positions.
	$new = $entity->positions ?: [];

	foreach ($new as $key => $data) {
		if ($key === 'new') {
			continue;
		}
		if (isset($data['id'])) {
			$item = InvoicePositions::findById($data['id']);

			if ($data['_delete']) {
				if (!$item->delete()) {
					return false;
				}
				continue;
			}
		} else {
			$item = InvoicePositions::create($data + ['user_id' + $entity->user_id]);
		}
		if (!$item->save(['billing_invoice_id' => $entity->id])) {
			return false;
		}
	}

	/*
	$useFilter = false;


	$positions = InvoicePositions::find('all', [
		'conditions' => ['billing_invoice_id' => $entity->id]
	]); // Refresh.

	// 2nd step finalizing the invoice.
	// Calculate invoice totals from positions.
	$totalNet = 0;
	$totalGross = 0;

	foreach ($positions as $item) {
		$field = 'price_' . strtolower($currency);
		$price = $item->$field;
		$totalNet += $price - ($price * $entity->tax_rate);
		$totalGross += $price;
	}
	$result = $entity->save([
		'total_currency' => $currency,
		'total_net' => $totalNet,
		'total_gross' => $totalGross,
		'total_tax' => $totalGross - $totalNet,
	]);

	 */
	$useFilter = true;
	return true;
});
Invoices::applyFilter('delete', function($self, $params, $chain) {
	$entity = $params['entity'];
	$result = $chain->next($self, $params, $chain);

	if ($result) {
		$positions = InvoicePositions::find('all', [
			'conditions' => ['billing_invoice_id' => $entity->id]
		]);
		foreach ($positions as $position) {
			$position->delete();
		}
	}
	return $result;
});


?>