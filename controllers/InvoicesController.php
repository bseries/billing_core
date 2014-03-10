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

class InvoicesController extends \cms_core\controllers\BaseController {

	use \cms_core\controllers\AdminAddTrait;
	use \cms_core\controllers\AdminEditTrait;
	use \cms_core\controllers\AdminDeleteTrait;

	public function admin_index() {
		$data = Invoices::find('all', [
			'order' => ['number' => 'DESC']
		]);
		return compact('data');
	}

	protected function _selects() {
		$parent = parent::_selects();
		$statuses = Invoices::enum('status');
		$currencies = [
			'EUR' => 'EUR',
			'USD' => 'USD'
		];
		$users = Users::find('list');

		return compact('currencies', 'statuses', 'users') + $parent;
	}
}

?>