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

use cms_core\models\Users;
use cms_core\extensions\cms\Settings;
use cms_billing\models\TaxZones;
use cms_billing\models\InvoicePositions;
use DateTime;

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

	// public static function init() {
	// }

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

	public function isOverdue($entity) {
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $entity->date);
		$overdue = Settings::read('invoiceOverdueAfter');

		if (!$overdue) {
			return false;
		}
		return $entity->total_gross_outstanding & $date->getTimestamp() > strtotime($overdue);
	}

	public static function totalOutstanding($user = null) {
		$results = static::find('all', [
			'conditions' => $user ? ['user_id' => $user] : []
		]);
		$outstanding = [];

		foreach ($results as $result) {
			$currency = $result->total_currency;

			if (!isset($outstanding[$currency])) {
				$outstanding[$currency]  = $result->total_gross_outstanding;
			} else {
				$outstanding[$currency] += $result->total_gross_outstanding;
			}
		}
		return $outstanding;
	}

	// If the user should get an invoice taking her invoice frequency into account.
	/*
	public function mustGenerate($user, $frequency) {
		if (!$this->InvoicePosition->pending($user)) {
			// User has no pending lines, no need to create an empty invoice.
			return false;
		}

		$invoice = $this->find('first', [
			'conditions' => ['user_id' => $user],
			'fields' => ['date'],
			'order' => 'date DESC'
		]);
		if (!$invoice) {
			return true; // No last billing available.
		}
		$last = DateTime::createFromFormat('Y-m-d', $invoice['Invoice']['date']);
		$diff = $last->diff(new DateTime());

		switch ($frequency) {
			case 'monthly':
				return $diff->m >= 1;
			case 'yearly':
				return $diff->y >= 1;
			}
		return false;
	}
	*/

	public static function nextNumber() {
		$pattern = Settings::read('invoiceNumberPattern');

		$item = static::find('first', [
			'conditions' => [
				'number' => 'LIKE ' . strftime($pattern['prefix']) . '%'
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

	public static function generate($user, $currency, array $taxZone, $vatRegNo) {
		// 1st step initalizing the invoice.
		$invoice = static::create([
			'number' => static::nextNumber(),
			'user_id' => $user,
			'date' => date('Y-m-d'),
			'user_vat_reg_no' => $vatRegNo, // fixate, may be changed by user
			'tax_rate' => $taxZone['rate'],
			'tax_note' => $taxZone['note'],
			'status' => 'created'
		]);

		if (!$invoice->save()) {
			return false;
		}

		// Finalize all pending positions.
		/*
		$positions = InvoicePositions::pending($user);
		foreach ($positions as $position) {
			$posistion->finalize($invoice->id);
		}
		 */
		$positions = InvoicePositions::find('all', [
			'conditions' => ['billing_invoice_id' => $invoice->id]
		]); // Refresh.

		// 2nd step finalizing the invoice.
		// Calculate invoice totals from positions.
		$totalNet = 0;
		$totalGross = 0;

		foreach ($positions as $item) {
			$price = $item['price_' . strtolower($currency)];
			$totalNet += $price - ($price * $invoice->tax_rate);
			$totalGross += $price;
		}
		return (boolean) $invoice->save([
			'total_currency' => $currency,
			'total_net' => $totalNet,
			'total_gross' => $totalGross,
			'total_gross_outstanding' => $totalGross,
			'total_tax' => $totalGross - $totalNet,
		]);
	}
}

Invoices::applyFilter('save', function($self, $params, $chain) {
	static $useFilter = true;

	if (!$useFilter) {
		return $chain->next($self, $params, $chain);
	}

	$entity = $params['entity'];
	$data = $params['data'];

	Invoices::pdo()->beginTransaction();

	$user = Users::findById($data['user_id']);
	$address = $user->address('billing');
	$taxZone = TaxZones::generate($address->country, $user->vat_reg_no, $user->locale);

	$entity->user_address = $address->format('postal');
	$entity->user_vat_reg_no = $user->vat_reg_no;
	$entity->tax_rate = $taxZone->rate;
	$entity->tax_note = $taxZone->note;

	$currency = $user->billing_currency;

	if (!$entity->exists()) {
		$entity->number = Invoices::nextNumber();
	}

	if (!$result = $chain->next($self, $params, $chain)) {
		Invoices::pdo()->rollback();
	}

	// Save nested positions.
	$new = $entity->positions;

	foreach ($new as $key => $data) {
		if ($key === 'new') {
			continue;
		}
		if (isset($data['id'])) {
			$item = InvoicePositions::findById($data['id']);

			if ($data['_delete']) {
				if (!$item->delete()) {
					Invoices::pdo()->rollback();
					return false;
				}
				continue;
			}
		} else {
			$item = InvoicePositions::create($data + ['user_id' + $entity->user_id]);
		}
		if (!$item->save(['billing_invoice_id' => $entity->id])) {
			Invoices::pdo()->rollback();
			return false;
		}
	}

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
		'total_gross_outstanding' => $totalGross,
		'total_tax' => $totalGross - $totalNet,
	]);

	Invoices::pdo()->commit();
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