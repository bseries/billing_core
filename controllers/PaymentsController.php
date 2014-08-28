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

use cms_core\models\Users;
use cms_core\models\VirtualUsers;
use billing_core\models\Invoices;
use billing_core\models\Payments;
use cms_core\models\Currencies;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;

class PaymentsController extends \cms_core\controllers\BaseController {

	use \cms_core\controllers\AdminAddTrait;
	use \cms_core\controllers\AdminEditTrait;
	use \cms_core\controllers\AdminDeleteTrait;

	public function admin_index() {
		$data = Payments::find('all', [
			'order' => ['date' => 'DESC']
		]);
		return compact('data');
	}

	protected function _selects($item = null) {
		$virtualUsers = [null => '-'] + VirtualUsers::find('list', ['order' => 'name']);
		$users = [null => '-'] + Users::find('list', ['order' => 'name']);

		$invoices = [null => '-'] + Invoices::find('list');
		$currencies = Currencies::find('list');

		return compact('currencies', 'invoices', 'users', 'virtualUsers');
	}
}

?>