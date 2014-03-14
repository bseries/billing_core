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

class PaymentsController extends \cms_core\controllers\BaseController {

	protected $_redirectUrl = [
		'action' => 'index',
		'controller' => 'Invoices'
	];

	use \cms_core\controllers\AdminAddTrait;
	use \cms_core\controllers\AdminEditTrait;
	use \cms_core\controllers\AdminDeleteTrait;

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