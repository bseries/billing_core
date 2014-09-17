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

namespace billing_core\models;

use billing_core\models\Invoices;
use SebastianBergmann\Money\Money;
use billing_core\extensions\BalanceMaxHeap;
use billing_core\extensions\TotalAmountMaxHeap;

class Payments extends \base_core\models\Base {

	use \base_core\models\UserTrait;

	protected $_meta = [
		'source' => 'billing_payments'
	];

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\Localizable' => [
			'fields' => [
				'amount' => 'money'
			]
		]
	];

	public function invoice($entity) {
		return Invoices::find('first', ['conditions' => ['id' => $entity->billing_invoice_id]]);
	}

	public function totalAmount($entity) {
		return new Money($entity->amount, $entity->amount_currency);
	}

	// Assigns payments to invoices to make them fully paid.
	//
	// The more payments and invoices are passed the better
	// can the algorithm optimize the assignments.
	//
	// FIXME This algorithm should be optimized to search
	// for the closest matching payment instead of using the largest
	// one available. To reduce amount of split payments.
	public static function assignToInvoices($payments, $invoices) {
		// First sort by amounts, highest first.
		// This must be done here as we cannot do this realiably in the DB.

		$stack = new BalanceMayHeap();
		foreach ($invoices as $invoice) {
			$stack->insert($invoice);
		}
		$invoices = $stack;

		$stack = new PaymentTotalHeap();
		foreach ($payments as $payment) {
			$stack->insert($payment);
		}
		$payments = $stack;

		// Add that many payments to invoice until it's fully paid
		// or there are no payments left.
		while (!$invoices->isEmpty() && !$payments->isEmpty()) {
			$invoice = $invoices->extract();

			while (!$invoice->isPaidInFull() && !$payments->isEmpty()) {
				// Need to refresh balance each time a payment was made.
				$balance = $invoice->balance()->getGross()->getMoney();
				$payment = $payments->extract();

				if ($payment->totalAmount()->greaterThan($balance)) {
					// We need to split the payment and end the loop.
					// FIXME optimize to search for next smallest payment that fits.

					$result = $payment->split([
						$balance->getAmount(),
						$payment->totalAmount()->subtract($balance)->getAmount()
					]);
					if (!$result) {
						return false;
					}
					// The result will always contain two entries.
					// As the payment inside this if-block will always
					// be greater than the balance.

					if (!$invoice->pay($result[0])) {
						return false;
					}

					// Take care to insert newly split left over payment.
					if ($result[1]) {
						$payments->insert($result[1]);
					}
					// We don't explictly need to break the loop here, th Next iteration
					// will catch the isPaid-Case for us.
				} else {
					// Add payments to invoice until it's paid.
					if (!$invoice->pay($payment)) {
						return false;
					}
				}
			}
		}
		return true;
	}

	// Splits the payment into given amounts.
	// Amounts must be cents. This must be wrapped in a transaction.
	//
	// Returns
	public function split($entity, array $amounts) {
		if (array_sum($amounts) > $entity->totalAmount()->getAmount()) {
			return false;
		}
		$results = array_fill_keys(array_keys($amounts), null);

		foreach ($amounts as $key => $amount) {
			$payment = static::create(compact('amount') + $entity->data());

			if (!$payment->save()) {
				return false;
			}
			$results[$key] = $payment;
		}
		if (!$entity->delete()) {
			return false;
		}
		return $results;
	}
}

?>