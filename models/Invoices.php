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
use cms_core\models\VirtualUsers;
use cms_core\extensions\cms\Settings;
use cms_billing\models\Payments;
use cms_billing\models\TaxZones;
use cms_billing\models\InvoicePositions;
use DateTime;
use Exception;
use cms_billing\extensions\finance\Price;

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
		'cms_core\extensions\data\behavior\Timestamp',
		'cms_core\extensions\data\behavior\ReferenceNumber'
	];

	public static $enum = [
		'status' => [
			'created', // open
			'sent', // open
			'paid',  // paid
			'void' // storno
		]
	];

	public static function init() {
		$model = static::_object();

		static::behavior('cms_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('invoice.number')
		);
	}

	public function quantity($entity) {
		$result = preg_match('/^([0-9])\sx\s/', $entity->title, $matches);

		if (!$result) {
			return 1;
		}
		return (integer) $matches[1];
	}

	public function user($entity) {
		if ($entity->user_id) {
			return Users::findById($entity->user_id);
		}
		return VirtualUsers::findById($entity->virtual_user_id);
	}

	public function taxZone($entity) {
		return TaxZones::create([
			'rate' => $entity->tax_rate,
			'note' => $entity->tax_note
		]);
	}

	public function date($entity) {
		return DateTime::createFromFormat('Y-m-d', $entity->date);
	}

	public static function createForUser($user) {
		$item = static::create();

		if ($user->id) {
			$field = $user->isVirtual() ? 'virtual_user_id' : 'user_id';
			$item->$field = $user->id;
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
		$overdue = Settings::read('invoice.overdueAfter');

		if (!$overdue) {
			return false;
		}
		return $entity->total_gross_outstanding && $date->getTimestamp() > strtotime($overdue);
	}

	// @fixme Assume positions habe same tax zone and currency and type.
	public function totalAmount($entity) {
		$result = new Price(0, 'EUR', 'net', $entity->taxZone());

		$positions = $this->positions($entity);

		foreach ($positions as $position) {
			$result = $result->add($position->totalAmount());
		}
		return $result;
	}

	public function totalTax($entity) {
		$result = $entity->totalAmount()->getGross();
		$result = $result->subtract($entity->totalAmount()->getNet());

		return $result;
	}

	// @fixme Payments is money outstanding is price
	public function totalOutstanding($entity) {
		$sum = null;

		foreach ($entity->positions() as $position) {
			$result = $position->totalAmount();

			if ($sum) {
				$sum = $sum->add($result);
			} else {
				$sum = $result;
			}
		}
		foreach ($entity->payments() as $payment) {
			$result = $payment->totalAmount();

			if ($sum) {
				$sum = $sum->subtract($result);
			} else {
				$sum = $result;
			}
		}
		return $sum;
	}

	public function payInFull($entity, $method) {
		$payment = Payments::create([
			'billing_invoice_id' => $entity->id,
			'method' => $method,
			'date' => date('Y-m-d'),
			'amount_currency' => $entity->total_currency,
			'amount' => $entity->totalOutstanding()->getGross()->getAmount()
		]);
		return $payment->save(null, ['localize' => false]);
	}

	public function isPaidInFull($entity) {
		return $entity->totalOutstanding()->getGross()->getAmount() <= 0;
	}

	public function address($entity) {
		return Addresses::createFromPrefixed('address_', $entity->data());
	}
}

Invoices::applyFilter('save', function($self, $params, $chain) {
	$entity = $params['entity'];
	$data = $params['data'];

	if ($entity->is_locked || isset($data['is_locked'])) {
		return $chain->next($self, $params, $chain);
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

Invoices::init();

?>