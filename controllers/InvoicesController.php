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

namespace billing_core\controllers;

use base_core\models\VirtualUsers;
use base_core\models\Users;
use billing_core\models\Invoices;
use billing_core\models\TaxTypes;
use lithium\g11n\Message;
use billing_core\models\Currencies;

class InvoicesController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\AdminDeleteTrait;

	use \base_core\controllers\AdminLockTrait;

	public function admin_export_pdf() {
		extract(Message::aliases());

		$invoice = Invoices::find('first', [
			'conditions' => [
				'id' => $this->request->id
			]
		]);
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

	protected function _selects($item = null) {
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
		$virtualUsers = [null => '-'] + VirtualUsers::find('list', ['order' => 'name']);
		$users = [null => '-'] + Users::find('list', ['order' => 'name']);

		if ($item) {
			$taxTypes = TaxTypes::find('list');
		}
		return compact('currencies', 'statuses', 'users', 'virtualUsers', 'taxTypes');
	}
}

?>