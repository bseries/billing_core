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

use cms_billing\models\InvoicePositions;

// Given our business resides in Germany DE and we're selling services
// which fall und § 3 a Abs. 4 UStG (Katalogleistung).
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

	/*
	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp'
	];
	*/

	// public $hasMany = ['InvoicePosition'];

	public static $enum = [
		'status' => ['created', 'sent', 'paid', 'void']
	];

	public function positions($entity) {
		if (!$entity->id) {
			return [];
		}
		return InovicePositions::find('all', [
			'conditions' => [
				'billing_invoice_id' => $entity->id
			]
		]);
	}

	// public $virtualFields = [
	//	'is_overdue' => "Invoice.total_gross_outstanding != 0 AND Invoice.date + INTERVAL 2 WEEK < CURDATE()"
	// ];

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

	protected static function _nextNumber() {
		$pattern = Settings::read('invoiceNumberPattern');

		$item = static::find('first', [
			'conditions' => [
				'number LIKE' => strftime($pattern['prefix']) . '%'
			],
			'order' => ['number' => 'DESC'],
			'fields' => ['number']
		]);
		if ($number = $item->number) {
			$number++;
		} else {
			$number = strftime($pattern['prefix']) . sprintf($pattern['number'], 1);
		}
		return $number;
	}

	public static function generate($user, $currency, array $taxZone, $vatRegNo) {
		// 1st step initalizing the invoice.
		$invoice = static::create([
			'number' => static::_nextNumber(),
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
		$positions = InvoicePositions::pending($user);
		foreach ($positions as $position) {
			$posistion->finalize($invoice->id);
		}
		$positions = InvoicePositios::find('all', [
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

?>