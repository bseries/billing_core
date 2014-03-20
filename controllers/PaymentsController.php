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

use cms_billing\models\Invoices;
use cms_billing\models\Payments;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;

class PaymentsController extends \cms_core\controllers\BaseController {

	protected $_redirectUrl = [
		'action' => 'index',
		'controller' => 'Invoices'
	];

	use \cms_core\controllers\AdminAddTrait;
	use \cms_core\controllers\AdminEditTrait;
	use \cms_core\controllers\AdminDeleteTrait;

	public function admin_add() {
		extract(Message::aliases());

		$model = $this->_model;
		$redirectUrl = $this->_redirectUrl + [
			'action' => 'index', 'library' => $this->_library
		];

		if ($invoiceId = $this->request->billing_invoice_id) {
			$item = $model::create([
				'billing_invoice_id' => $invoiceId
			]);
		} else {
			$item = $model::create();
		}

		if ($this->request->data) {
			if ($item->save($this->request->data)) {
				FlashMessage::write($t('Successfully saved.'), ['level' => 'success']);

				return $this->redirect($redirectUrl);
			} else {
				FlashMessage::write($t('Failed to save.'), ['level' => 'error']);
			}
		}
		$this->_render['template'] = 'admin_form';
		return compact('item') + $this->_selects($item);
	}

	protected function _selects($item) {
		$invoices = [];

		foreach (Invoices::find('all') as $item) {
			$invoices[$item->id] = '#' . $item->number;
		}
		$currencies = [
			'EUR' => 'EUR',
			'USD' => 'USD'
		];
		return compact('currencies', 'invoices');
	}
}

?>