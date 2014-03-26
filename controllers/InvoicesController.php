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

use cms_core\models\VirtualUsers;
use cms_core\models\Users;
use cms_billing\models\Invoices;
use PHPExcel as Excel;
use PHPExcel_Writer_Excel2007 as WriterExcel2007;
use PHPExcel_IOFactory as ExcelIOFactory;
// use PHPExcel_Style_NumberFormat as ExcelNumberFormat;
// use PHPExcel_Shared_Date;
// use PHPExcel_Cell;
// use PHPExcel_Cell_AdvancedValueBinder;
use lithium\g11n\Message;
use temporary\Manager as Temporary;
use cms_core\models\Currencies;

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
		// PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());

		$sheet = $excel->getActiveSheet();

		$sheet->setCellValue('A1', $t('Invoice number'));
		$sheet->setCellValue('B1', $invoice->number);
		$sheet->setCellValue('A2', $t('Invoice date'));
		$sheet->setCellValue('B2', $invoice->date()->format('d.m.Y'));
		//$sheet->setCellValue('B2', PHPExcel_Shared_Date::PHPToExcel($invoice->date()));
		/*
		$sheet->getStyle('B2')->getNumberFormat()->setFormatCode(
			ExcelNumberFormat::FORMAT_DATE_YYYYMMDDSLASH
		);
		 */

		$sheet->setCellValue('A4', $t('Recipient address'));
		$sheet->setCellValue('B4', $invoice->address()->format('postal'));
		$sheet->setCellValue('A5', $t('Recipient VAT Reg. No.'));
		$sheet->setCellValue('B5', $invoice->user_vat_reg_no);
		$sheet->setCellValue('A6', $t('Recipient number'));
		$sheet->setCellValue('B6', $invoice->user()->number);

		$data = [];
		$data[] = [
			$t('Description'),
			$t('Quantity'),
			$t('Unit price (net)'),
			$t('Line total (net)')
		];
		$data[] = [];
		foreach ($invoice->positions() as $position) {
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
			round($invoice->totalAmount()->getNet()->getAmount() / 100, 4)
		];
		$data[] = [
			$t('Tax ({:rate}%)', ['rate' => $invoice->tax_rate]),
			null,
			null,
			round($invoice->totalAmount()->getTax()->getAmount() / 100, 4)
		];
		$data[] = [
			$t('Total (gross)'),
			null,
			null,
			round($invoice->totalAmount()->getGross()->getAmount() / 100, 4)
		];
		$sheet->fromArray($data, null, 'C9');

		// Last filled cell.
		$offset = 9 + count($data) - 1;

		$sheet->setCellValue('A' . ($offset + 2), $t('Terms'));
		$sheet->setCellValue('B' . ($offset + 2), $invoice->terms);
		$sheet->setCellValue('A' . ($offset + 3), $t('Note'));
		$sheet->setCellValue('B' . ($offset + 3) , $invoice->note);
		$sheet->setCellValue('A' . ($offset + 4), $t('Tax note'));
		$sheet->setCellValue('B' . ($offset + 4), $invoice->tax_note);

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
			'cancelled' => $t('cancelled'), // storno

			'awaiting-payment' => $t('awaiting payment'),
			'payment-accepted' => $t('payment accepted'),
			'payment-remotely-accepted' => $t('payment remotely accepted'),
			'payment-error' => $t('payment error'),
		]);
		$currencies = Currencies::find('list');
		$virtualUsers = [null => '-'] + VirtualUsers::find('list');
		$users = [null => '-'] + Users::find('list');

		return compact('currencies', 'statuses', 'users', 'virtualUsers');
	}
}

?>