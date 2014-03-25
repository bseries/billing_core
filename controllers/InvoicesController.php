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

namespace cms_billing\controllers;

use cms_core\models\Users;
use cms_billing\models\Invoices;
use PHPExcel as Excel;
use PHPExcel_Writer_Excel2007 as WriterExcel2007;
use PHPExcel_IOFactory as ExcelIOFactory;
use lithium\g11n\Message;
use temporary\Manager as Temporary;

class InvoicesController extends \cms_core\controllers\BaseController {

	use \cms_core\controllers\AdminAddTrait;
	use \cms_core\controllers\AdminEditTrait;
	use \cms_core\controllers\AdminDeleteTrait;

	use \cms_core\controllers\AdminLockTrait;

	public function admin_index() {
		$data = Invoices::find('all', [
			'order' => ['number' => 'DESC']
		]);
		return compact('data');
	}

	public function admin_export_excel() {
		extract(Message::aliases());

		$invoice = Invoices::findById($this->request->id);

		$excel = new Excel();
		$sheet = $excel->getActiveSheet();

		$sheet->setCellValue('A1', $t('Invoice number'));
		$sheet->setCellValue('B1', $invoice->number);

		$data = [];
		$data[] = [
			$t('Type'),
			$t('Description'),
			$t('Total (net)'),
			$t('Total (gross)')
		];
		foreach ($invoice->positions() as $position) {
			$data[] = [
				$t('position'),
				$position->description,
				$position->totalAmount('net')->getAmount() / 100,
				$position->totalAmount('gross')->getAmount() / 100
			];
		}
		foreach ($invoice->payments() as $payment) {
			$data[] = [
				$t('payment'),
				$payment->method,
				null,
				$payment->totalAmount()->negate()->getAmount() / 100
			];
		}
		$sheet->fromArray($data, null, 'B4');

		$file = Temporary::file([
			'context' => PROJECT_NAME . '_invoices_export_excel'
		]);

		$writer = ExcelIOFactory::createWriter($excel, 'Excel2007');
		$writer->save($file);

		$stream = fopen($file, 'r');

		$this->_renderDownload(
			$this->_downloadBasename(
				null,
				'invoice',
				$invoice->number . '.xlsx'
			),
			$stream
		);
		fclose($stream);
	}

	protected function _selects($item) {
		extract(Message::aliases());

		$statuses = Invoices::enum('status', [
			'created' => $t('created'), // open
			'sent' => $t('sent'), // open
			'paid' => $t('paid'),  // paid
			'void' => $t('void'), // storno

			'awaiting-payment' => $t('awaiting payment'),
			'payment-accepted' => $t('payment accepted'),
			'payment-remotely-accepted' => $t('payment remotely accepted'),
			'payment-error' => $t('payment error'),
		]);
		$currencies = [
			'EUR' => 'EUR',
			'USD' => 'USD'
		];
		$users = Users::find('list');

		return compact('currencies', 'statuses', 'users');
	}
}

?>