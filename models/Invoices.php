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

use DateTime;
use DateInterval;
use Exception;
use lithium\g11n\Message;
use lithium\core\Libraries;
use base_core\models\Addresses;
use base_core\extensions\cms\Settings;
use base_core\extensions\cms\Features;
use billing_core\models\Payments;
use billing_core\models\TaxZones;
use billing_core\models\InvoicePositions;
use app\extensions\pdf\InvoiceDocument;
use li3_mailer\action\Mailer;
use temporary\Manager as Temporary;
use Finance\Price;
use Finance\NullPrice;
use Finance\PriceSum;
use PHPExcel as Excel;
use PHPExcel_Writer_Excel2007 as WriterExcel2007;
use PHPExcel_IOFactory as ExcelIOFactory;

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
class Invoices extends \base_core\models\Base {

	use \base_core\models\UserTrait;

	protected $_meta = [
		'source' => 'billing_invoices'
	];

	// public $belongsTo = ['User'];

	// public $hasMany = ['InvoicePosition', 'Payments'];

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\ReferenceNumber',
		'base_core\extensions\data\behavior\StatusChange'
	];

	public static $enum = [
		'status' => [
			'created', // open
			'paid',  // paid
			'cancelled', // storno
			'awaiting-payment',
			// 'payment-accepted',
			'payment-remotely-accepted',
			'payment-error',
			'send-scheduled',
			'sent'
		],
		'frequency' => [
			'monthly',
			'yearly'
		]
	];

	public static function init() {
		$model = static::_object();

		static::behavior('base_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('invoice.number')
		);
	}

	public static function generateFromPending($user, array $data = []) {
		$positions = InvoicePositions::pending($user->id);

		if (!$positions) {
			return true;
		}
		$invoice = InvoicesModel::create($data + [
			$user->isVirtual() ? 'virtual_user_id' : 'user_id' => $user->id,
			'user_vat_reg_no' => $user->vat_reg_no,
			'date' => date('Y-m-d'),
			'status' => 'created',
			// 'note' => $t('Order No.') . ': ' . $entity->number,
			'terms' => Settings::read('billing.paymentTerms')
		]);
		$invoice = $user->address('billing')->copy($invoice, 'address_');

		if (!$invoice->save()) {
			return false;
		}

		foreach ($positions as $position) {
			$result = $position->save([
				'billing_invoice_id' => $invoice->id
			], ['whitelist' => ['billing_invoice_id']]);

			if (!$result) {
				return false;
			}
		}
		return $invoice;
	}

	public static function mustAutoInvoice($user) {
		if (!$user->auto_invoice_frequency) {
			trigger_error("User `{$user->id}` has not auto invoice frequency.", E_USER_NOTICE);
			return false;
		}
		if (!$user->auto_invoiced) {
			return true;
		}
		$last = DateTime::createFromFormat('Y-m-d', $user->auto_invoiced);
		$diff = $last->diff(new DateTime());

		switch ($user->auto_invoice_frequency) {
			case 'monthly':
				return $diff->m >= 1;
			case 'yearly':
				return $diff->y >= 1;
			default:
				throw new Exception("Unsupported frequency `$user->auto_invoice_frequency`.");
		}
		return false;
	}

	public static function nextAutoInvoiceDate($user) {
		if (!$user->auto_invoice_frequency) {
			trigger_error("User `{$user->id}` has not auto invoice frequency.", E_USER_NOTICE);
			return false;
		}
		if (!$user->auto_invoiced) {
			return false;
		}
		$date = DateTime::createFromFormat('Y-m-d', $user->auto_invoiced);

		switch ($user->auto_invoice_frequency) {
			case 'monthly':
				$interval = DateInterval::createFromDateString('1 month');
				break;
			case 'yearly':
				$interval = DateInterval::createFromDateString('1 year');
			break;
			default:
				throw new Exception("Unsupported frequency `$user->auto_invoice_frequency`.");
		}
		return $date->add($interval);
	}

	public static function autoInvoice($user) {
		$invoice = static::generateFromPending($user);

		if ($invoice === null) {
			continue; // No pending positions, no invoice to send.
		}
		if ($invoice === false) {
			return false;
		}
		$result = $user->save([
			'auto_invoiced' => date('Y-m-d H:i:s')
		], ['whitelist' => ['auto_invoiced']]);

		if (!$result) {
			return false;
		}

		$payments = Payments::find('all', [
			$user->isVirtual() ? 'virtual_user_id' : 'user_id' => $user->id,
			'billing_invoice_id' => null // Only unassigned payments.
		]);
		// Assumes the invoice we just created is not paid.

		if (!Payments::assignToInvoices($payments, [$invoice])) {
			return false;
		}
		return true;
	}

	public function send($entity) {
		$user = $entity->user();
		$contact = Settings::read('contact.billing');

		if (!$user->is_notified) {
			return;
		}

		$result = $entity->save(['status' => 'sent'], [
			'whitelist' => ['status'],
			'validate' => false,
			'lockWriteThrough' => true
		]);

		return $result && Mailer::deliver('invoice_sent', [
			'to' => $user->email,
			'bcc' => $contact['email'],
			'subject' => $t('Your invoice #{:number}.', [
				'number' => $invoice->number
			]),
			'data' => [
				'user' => $user,
				'item' => $entity
			],
			'attach' => [
				[
					'data' => $entity->exportAsPdf(),
					'filename' => 'invoice_' . $entity->number . '.pdf',
					'content-type' => 'application/pdf'
				]
			]
		]);
	}

	public function title($entity) {
		return '#' . $entity->number;
	}

	public function quantity($entity) {
		$result = preg_match('/^([0-9])\sx\s/', $entity->title, $matches);

		if (!$result) {
			return 1;
		}
		return (integer) $matches[1];
	}

	// Iterate over taxes an retrieve unique tax notes.
	public function taxNote($entity) {
		$results = [];

		foreach ($entity->positions() as $position) {
			$results[] = $position->taxType()->note;
		}
		return implode("\n", array_unique($results));
	}

	public function date($entity) {
		return DateTime::createFromFormat('Y-m-d', $entity->date);
	}

	public function positions($entity) {
		return !$entity->id ? [] : InvoicePositions::find('all', [
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

	public function totalAmount($entity) {
		$sum = new PriceSum();

		foreach ($entity->positions() as $position) {
			$sum = $sum->add($position->totalAmount());
		}
		return $sum;
	}

	public function totalTax($entity) {
		return $entity->totalAmount()->getTax();
	}

	public function totalTaxes($entity) {
		$results = [];

		foreach ($this->positions($entity) as $position) {
			$results[] = [
				'rate' => $position->tax_rate,
				'amount' => $position->totalAmount()->getTax()
			];
		}

		return $results;

	}

	// May return positive or negative values.
	public function balance($entity) {
		$sum = new PriceSum();

		foreach ($entity->positions() as $position) {
			// We need to convert to gross here as payments will be gross only.
			$sum = $sum->subtract($position->totalAmount());
		}
		foreach ($entity->payments() as $payment) {
			$sum = $sum->add($payment->totalAmount());
		}
		return $sum;
	}

	public function pay($entity, $payment) {
		$sum = $entity->balance();

		if (!$sum->greaterThan(new NullPrice())) {
			throw new Exception("Invoice is already paid in full.");
		}

		return $payment->save(['billing_invoice_id' => $entity->id], [
			'localize' => false,
			'whitelist' => ['billing_invoice_id']
		]);
	}

	/*
	public function payInFull($entity, $payment = null) {
		$sum = $entity->balance();

		if (!$sum->greaterThan(new NullPrice())) {
			throw new Exception("Invoice is already paid in full.");
		}

		$payment = Payments::create([
			'billing_invoice_id' => $entity->id,
			'method' => $method,
			'date' => date('Y-m-d'),
			'amount' => $sum->getGross()->getAmount(),
			'amount_currency' => (string) $sum->getCurrency(),
		]);
		return $payment->save(null, ['localize' => false]);
	}
	*/

	public function isPaidInFull($entity) {
		return !$entity->balance()->greaterThan(new NullPrice());
	}

	public function address($entity) {
		return Addresses::createFromPrefixed('address_', $entity->data());
	}

	public function statusChange($entity, $from, $to) {
		extract(Message::aliases());

		switch ($to) {
			// Lock invoice when its got sent.
			case 'sent':
				if ($entity->is_locked) {
					return true;
				}
				return $entity->save(['is_locked' => true], [
					'whitelist' => ['is_locked'],
					'validate' => false
				]);
			case 'paid':
				$user = $entity->user();
				$contact = Settings::read('contact.billing');

				if (!Features::enabled('invoice.sendPaidMail') || !$user->is_notified) {
					return true;
				}
				return Mailer::deliver('invoice_paid', [
					'to' => $user->email,
					'bcc' => $contact['email'],
					'subject' => $t('Invoice #{:number} paid.', [
						'number' => $entity->number
					]),
					'data' => [
						'user' => $user,
						'item' => $entity
					]
				]);
			default:
				break;
		}
		return true;
	}

	public function isCancelable($entity) {
		return in_array($entity->status, [
			'created',
			'cancelled',
			'awaiting-payment',
			'payment-error',
		]);
	}

	public function exportAsExcel($entity) {
		extract(Message::aliases());

		$excel = new Excel();

		$sheet = $excel->getActiveSheet();

		$sheet->setCellValue('A1', $t('Invoice number'));
		$sheet->setCellValue('B1', $entity->number);
		$sheet->setCellValue('A2', $t('Invoice date'));
		$sheet->setCellValue('B2', $entity->date()->format('d.m.Y'));

		$sheet->setCellValue('A4', $t('Recipient address'));
		$sheet->setCellValue('B4', $entity->address()->format('postal'));
		$sheet->setCellValue('A5', $t('Recipient VAT Reg. No.'));
		$sheet->setCellValue('B5', $entity->user_vat_reg_no);
		$sheet->setCellValue('A6', $t('Recipient number'));
		$sheet->setCellValue('B6', $entity->user()->number);

		$data = [];
		$data[] = [
			$t('Description'),
			$t('Quantity'),
			$t('Unit price (net)'),
			$t('Line total (net)')
		];
		$data[] = [];
		foreach ($entity->positions() as $position) {
			$data[] = [
				$position->description,
				$position->quantity,
				round($position->amount()->getNet()->getAmount() / 100, 4),
				round($position->totalAmount()->getNet()->getAmount() / 100, 4)
			];
		}
		$data[] = [];
		$data[] = [
			$t('Total (net)'),
			null,
			null,
			round($entity->totalAmount()->getNet()->getAmount() / 100, 4)
		];
		$data[] = [
			$t('Tax ({:rate}%)', ['rate' => $entity->tax_rate]),
			null,
			null,
			round($entity->totalAmount()->getTax()->getAmount() / 100, 4)
		];
		$data[] = [
			$t('Total (gross)'),
			null,
			null,
			round($entity->totalAmount()->getGross()->getAmount() / 100, 4)
		];
		$sheet->fromArray($data, null, 'C9');

		// Last filled cell.
		$offset = 9 + count($data) - 1;

		$sheet->setCellValue('A' . ($offset + 2), $t('Terms'));
		$sheet->setCellValue('B' . ($offset + 2), $entity->terms);
		$sheet->setCellValue('A' . ($offset + 3), $t('Note'));
		$sheet->setCellValue('B' . ($offset + 3) , $entity->note);
		$sheet->setCellValue('A' . ($offset + 4), $t('Tax note'));
		$sheet->setCellValue('B' . ($offset + 4), $entity->taxNote());

		$sheet->getStyle('A1:A45')->getFont()->setBold(true);
		$sheet->getStyle('A1:A45')->getAlignment()->setVertical('top');
		$sheet->getStyle('B1:B45')->getAlignment()->setWrapText(true);
		$sheet->getStyle('C9:F9')->getFont()->setBold(true);
		$sheet->getStyle('C' . ($offset - 2) . ':F' . $offset)->getFont()->setBold(true);

		$sheet->getColumnDimension('A')->setAutoSize(true);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getColumnDimension('F')->setAutoSize(true);

		$file = Temporary::file([
			'context' => PROJECT_NAME . '_invoices_export_excel'
		]);

		$writer = ExcelIOFactory::createWriter($excel, 'Excel2007');
		$writer->save($file);

		return fopen($file, 'r');
	}

	public function exportAsPdf($entity) {
		extract(Message::aliases());

		$stream = fopen('php://temp', 'w+');

		$user = $entity->user();
		$contact = Settings::read('contact.billing');

		$document = new InvoiceDocument();

		$document
			->invoice($entity)
			->recipient($entity->user())
			->senderContact($contact)
			->type($t('Invoice'))
			->subject($t('Invoice #{:number}', $entity->data()))
			// ->intro($t("As agreed, we're billing you for the provided services associated with your account on http://npiece.com. The costs for these services are the following."))
			->template(Libraries::get('app', 'resources') . "/pdf/empty_invoice_document.pdf")
			->paypalEmail(Settings::read('service.paypal.default.email'))
			->bankAccount(Settings::read('billing.bankAccount'))
			->paymentTerms(Settings::read('billing.paymentTerms'))
			->vatRegNo(Settings::read('billing.vatRegNo'));

		$document->compile();
		$document->render($stream);

		rewind($stream);
		return $stream;
	}

	// @deprecated
	public function totalOutstanding($entity) {
		trigger_error('totalOutstanding() is deprecated in favor of balance().', E_USER_DEPRECATED);
		return $entity->balance();
	}
}

Invoices::applyFilter('save', function($self, $params, $chain) {
	$params['options'] += [
		'lockWriteThrough' => false
	];
	$entity = $params['entity'];
	$data = $params['data'];
	$user = $entity->user();

	if ($entity->exists()) {
		$isLocked = Invoices::find('first', [
			'conditions' => ['id' => $entity->id],
			'fields' => ['is_locked']
		])->is_locked;
	} else { // We're creating a brandnew invoice.

		// Set when we last billed the user, once.
		// $user->save(['invoiced' => date('Y-m-d')], ['whitelist' => ['invoiced', 'modified']]);

		// Initial invoices are not locked.
		$isLocked = false;
	}

	if (!$params['options']['lockWriteThrough'] && $isLocked) {
		$params['options']['whitelist'] = (array) $params['options']['whitelist'] + ['status'];
	}
	if (!$result = $chain->next($self, $params, $chain)) {
		return false;
	}

	// Save nested positions.
	if (!empty($params['options']['lockWriteThrough']) || !$isLocked) {
		$new = isset($data['positions']) ? $data['positions'] : [];
		foreach ($new as $key => $data) {
			if ($key === 'new') {
				continue;
			}
			if (isset($data['id'])) {
				$item = InvoicePositions::find('first', ['conditions' => ['id' => $data['id']]]);

				if ($data['_delete']) {
					if (!$item->delete()) {
						return false;
					}
					continue;
				}
			} else {
				$item = InvoicePositions::create($data + [
					'billing_invoice_id' => $entity->id,
					$user->isVirtual() ? 'virtual_user_id' : 'user_id' => $user->id
				]);
			}
			if (!$item->save($data)) {
				return false;
			}
		}
	}

	// Save nested payments; alwas allow writing these.
	$new = isset($data['payments']) ? $data['payments'] : [];
	foreach ($new as $key => $data) {
		if ($key === 'new') {
			continue;
		}
		if (isset($data['id'])) {
			$item = Payments::find('first', ['conditions' => ['id' => $data['id']]]);

			if ($data['_delete']) {
				if (!$item->delete()) {
					return false;
				}
				continue;
			}
		} else {
			$item = Payments::create([
				'billing_invoice_id' => $entity->id,
				$user->isVirtual() ? 'virtual_user_id' : 'user_id' => $user->id
			]);
		}
		if (!$item->save($data)) {
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