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

namespace billing_core\extensions\command;

class Invoices extends \lithium\console\Command {

	// Assembles pending invoice position into a single invoice for a user
	// by given user billing frequency.
	public function generateFromPending() {
		$user = Users::find('all', [
			'conditions' => [
				// 'is_active' => true
			]
		]);
		foreach ($users as $user) {
			$positions = InvoicePositions::pending($user->id);
			if (!$positions) {
				continue;
			}
			$invoice = Invoices::createForUser($user);
			$result = $inoice->save([
				'date' => date('Y-m-d'),
				'terms' => Settings::read('billing.paymentTerms')
			]);

			foreach ($positions as $position) {
				$result = $position->save([
					'billing_invoice_id' => $invoice->id
				], ['whitelist' => ['billing_invoice_id']]);
			}
		}
	}

	// Auto-mails not already sent invoices
	// to user.
	public function sendCreated() {
		$user = Users::find('all', [
			'conditions' => [
				// FIXME check if users automatically gets invoices?
				// 'is_active' => true
			]
		]);
		foreach ($users as $user) {
			$invoices = Invoices::find('all', [
				'conditions' =>  [
					'status' => 'created'
				]
			]);
			// FIXME Bundle multiple invoices into a single mail?
			foreach ($invoices as $invoice) {
				$invoice->save(['status' => 'sent']);
			}





			$invoice = Invoices::createForUser($user);
			$result = $inoice->save([
				'date' => date('Y-m-d'),
				'terms' => Settings::read('billing.paymentTerms')
			]);

			foreach ($positions as $position) {
				$result = $position->save([
					'billing_invoice_id' => $invoice->id
				], ['whitelist' => ['billing_invoice_id']]);
			}
		}
	}
}

?>