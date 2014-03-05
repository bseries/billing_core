<?php

// In the moment of generating an invoice position the price is finalized.
class BillingInvoicePosition extends AppModel {

	public $belongsTo = [
		'User',
		'BillingInvoice'
	];

	// This fills out all fields open for the position making it non-pending.
	public function finalize($position, $invoice) {
		return (boolean) $this->save([
			$this->alias => [
				'id' => $position,
				'billing_invoice_id' => $invoice
			]
		]);
	}

	public function pending($user) {
		return $this->find('all', [
			'conditions' => [
				'user_id' => $user,
				'billing_invoice_id' => null
			]
		]);
	}
}

?>