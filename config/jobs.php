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

use DateTime;
use base_core\extensions\cms\Jobs;
use base_core\models\Users;
use billing_core\models\Invoices;

Jobs::recur('auto_invoice', function() {
	Invoices::pdo()->beginTransaction();

	$users = Users::find('all', [
		'conditions' => [
			'is_auto_invoiced' => true
			// 'is_active' => true
		]
	]);

	// TODO check frequency

	foreach ($users as $user) {
		$mustInvoice = false;

		$map = [
			'weekly' => '+1 week',
			'monthly' => '+1 month',
			'yearly' => '+1 year'
		];
		$now = new DateTime();
		$invoiced = DateTime::createFromFormat('Y-m-d H:i:s');

		// $invoiced will also be set when manually creating an invoice

		if (!$mustInvoice) {
			continue;
		}

		$invoice = Invoices::generateFromPending($user);

		if ($invoice === null) {
			continue; // No pending positions, no invoice to send.
		}
		if ($invoice === false) {
			Invoices::pdo()->rollback();
			return false;
		}
		if (!$invoice->send()) {
			Invoices::pdo()->rollback();
			return false;
		}
	}

	Invoices::pdo()->commit();
}, [
	'frequency' => Jobs::FREQUENCY_LOW
]);

?>