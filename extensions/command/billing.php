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

use temporary\Manager as Temporary;

App::import('Core', 'Controller');
App::import('Controller', 'AppController');

class BillingShell extends Shell {

	public function main() {
		$this->out('[P]osition scheduled');
		$this->out('[G]enerate Invoices');
		$this->out('[S]end Invoices');
		$this->out('[D]ump Invoices');
		$this->hr();
		$this->out('[Q]uit');

		$action = $this->in('What would you like to do?', [
			'q', 'g', 'd', 's', 'p'
		], 'q');

		switch (strtoupper($action)) {
			case 'P':
				return $this->position_scheduled();
			case 'G':
				return $this->generate_invoices();
			case 'S':
				return $this->send_invoices();
			case 'D':
				return $this->dump_invoices();
			case 'Q':
				return $this->_stop(0);
		}
	}

	public function position_scheduled() {
		$ScheduledInvoicePosition = ClassRegistry::init('BillingScheduledInvoicePosition');
		$User = ClassRegistry::init('User');

		$users = $User->find('all');

		foreach ($users as $user) {
			$positions = $ScheduledInvoicePosition->find('list', [
				'conditions' => ['user_id' => $user['User']['id']],
				'fields' => ['id', 'description']
			]);
			foreach ($positions as $position => $description) {
				// Sometimes the cron hasn't run and we must get ahead again.
				while ($ScheduledInvoicePosition->mustPosition($position)) {
					$this->out("Positioning and advancing scheduled `{$description}` for user `{$user['User']['name']}`... ", false);
					$result = $ScheduledInvoicePosition->position($position);
					$result = $result && $ScheduledInvoicePosition->advance($position);
					$this->out($result ? 'OK' : 'FAILED');
				}
			}
		}
	}

	// Generating invoices for all active users on paid plans.
	public function generate_invoices() {
		$Invoice = ClassRegistry::init('BillingInvoice');
		$User = ClassRegistry::init('User');

		$users = $User->find('all');

		foreach ($users as $user) {
			if ($Invoice->mustGenerate($user['User']['id'], $user['User']['billing_invoice_frequency'])) {
				$this->out("Generating invoice for user `{$user['User']['name']}`... ", false);

				$result = $Invoice->generate(
					$user['User']['id'],
					$user['User']['billing_currency'],
					$Invoice->taxZone(
						$user['User']['address_country'],
						$user['User']['billing_vat_reg_no'],
						$user['User']['locale']
					),
					$user['User']['billing_vat_reg_no']
				);
				$this->out($result ? 'OK' : 'FAILED');
			}
		}
	}

	 // Sending out emails for invoices with status `created`.
	public function send_invoices() {
		$Invoice = ClassRegistry::init('BillingInvoice');
		$Controller = $this->_Controller();

		$results = $Invoice->find('all', [
			'conditions' => [
				'status' => 'created'
			],
			'contain' => [
				'User'
			]
		]);
		foreach ($results as $result) {
			$this->out("Sending invoice `{$result['BillingInvoice']['number']}` to `{$result['User']['name']}`... ", false);

			$Controller->G11n->switchLocale($result['User']['locale']);

			$territories = $Controller->G11n->listTerritories();
			if (isset($territories[$result['User']['address_country']])) {
				$result['User']['address_country'] = $territories[$result['User']['address_country']];
			}
			$Controller->data = $result;
			$Controller->layout = false;

			$this->out('document... ', false);

			$file = Temporary::file(['context' => 'npiece_billing']);

			$Controller->viewPath = 'billing_invoices';
			$data = $Controller->render('pdf/document');
			file_put_contents($file, $data);
			unset($data); // Free memory.

			$Controller->Postmark->template = 'billing_invoice';
			$Controller->Postmark->from = 'NPIECE <billing@npiece.com>';
			$Controller->Postmark->to = "{$result['User']['name']} <{$result['User']['email']}>";
			// Component doesn't do string/array normalization for properties
			// except `to`.  That's why we have this as an array here.
			$Controller->Postmark->bcc = ['NPIECE <billing@npiece.com>'];
			$Controller->Postmark->subject = sprintf(
				__('New Invoice #%s'),
				$result['BillingInvoice']['number']
			);
			$Controller->Postmark->attachments = [
				"npiece_invoice_{$result['BillingInvoice']['number']}.pdf" => $file
			];

			$this->out('mail... ', false);

			if ($send = $Controller->Postmark->send()) {
				$Invoice->save(['BillingInvoice' => [
					'id' => $result['BillingInvoice']['id'],
					'status' => 'sent'
				]]);
			}
			$this->out($send ? 'OK' : 'FAILED');

			$Controller->output = ''; // Do not pile up output internally.
			$Controller->Postmark->reset();
			$Controller->G11n->switchLocale();
		}
	}

	public function dump_invoices() {
		$path = $this->in('Path to save documents to:', null, '/tmp');
		$path = rtrim($path, '/') . '/';

		$Invoice = ClassRegistry::init('BillingInvoice');
		$Controller = $this->_Controller();

		$results = $Invoice->find('all', [
			'conditions' => [
				'status' => ['created', 'paid', 'sent']
			],
			'contain' => [
				'User', 'BillingInvoicePosition'
			]
		]);
		foreach ($results as $result) {
			$this->out("Dumping invoice `{$result['BillingInvoice']['number']}` for `{$result['User']['name']}`... ", false);

			$Controller->G11n->switchLocale($result['User']['locale']);

			$territories = $Controller->G11n->listTerritories();
			if (isset($territories[$result['User']['address_country']])) {
				$result['User']['address_country'] = $territories[$result['User']['address_country']];
			}
			$Controller->data = $result;
			$Controller->layout = false;

			$file = "{$path}npiece_billing_invoice_{$result['BillingInvoice']['number']}.pdf";

			$Controller->viewPath = 'billing_invoices';
			$data = $Controller->render('pdf/document');
			file_put_contents($file, $data);
			$this->out('OK');

			$Controller->output = '';
			$Controller->G11n->switchLocale();
		}
	}

	protected function _Controller() {
		$Controller = new AppController();

		$Controller->helpers[] = 'InvoiceDocument';
		$Controller->components['Postmark'] = [
			'template' => 'default'
		];

		$Controller->constructClasses();
		$Controller->Component->initialize($Controller);
		return $Controller;
	}
}

?>