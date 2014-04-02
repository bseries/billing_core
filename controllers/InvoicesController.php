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
use lithium\g11n\Message;
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
		$stream = $invoice->exportAsExcel();

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

	public function admin_export_pdf() {
		extract(Message::aliases());

		$invoice = Invoices::findById($this->request->id);
		$stream = $invoice->exportAsPdf();

		$this->_renderDownload(
			$this->_downloadBasename(
				null,
				'invoice',
				$invoice->number . '.pdf'
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