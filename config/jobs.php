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

use \DateTime;
use base_core\extensions\cms\Jobs;
use base_core\models\Users;
use billing_core\models\Invoices;

// Generates invoices from pending invoice positions
// automatically then assigns payments.
//
// Assumes that if a user is auto invoiced
// we also should auto assign the payments.
Jobs::recur('auto_invoice', function() {
	Invoices::pdo()->beginTransaction();

	// FIXME Make this work for virtual users, too?
	$users = Users::find('all', [
		'conditions' => [
			'is_auto_invoiced' => true
			// 'is_active' => true
		]
	]);

	// TODO check frequency

	/*
	$map = [
		'weekly' => '+1 week',
		'monthly' => '+1 month',
		'yearly' => '+1 year'
	];
	 */
	foreach ($users as $user) {
		// $mustInvoice = false;

		// $now = new DateTime();
		// $invoiced = DateTime::createFromFormat('Y-m-d H:i:s');

		// $invoiced will also be set when manually creating an invoice

		// if (!$mustInvoice) {
		//	continue;
		// }

		$invoice = Invoices::generateFromPending($user);

		if ($invoice === null) {
			continue; // No pending positions, no invoice to send.
		}
		if ($invoice === false) {
			Invoices::pdo()->rollback();
			return false;
		}

		$payments = Payments::find('all', [
			$user->isVirtual() ? 'virtual_user_id' : 'user_id' => $user->id,
			'billing_invoice_id' => null // Only unassigned payments.
		]);
		// Assumes the invoice we just created is not paid.

		if (!Payments::assignToInvoices($payments, [$invoice])) {
			Invoices::pdo()->rollback();
			return false;
		}
	}

	Invoices::pdo()->commit();
}, [
	'frequency' => Jobs::FREQUENCY_LOW
]);

// This will auto send any invoice that is plain created but not sent.
Jobs::recur('auto_send_invoices', function() {
	$invoices = Invoices::find('all', [
		'status' => 'created'
	]);
	foreach ($invoice as $invoice) {
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