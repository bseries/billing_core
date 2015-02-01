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

use lithium\g11n\Message;
use base_core\extensions\cms\Widgets;
use billing_core\models\Payments;
use billing_core\models\Invoices;
use lithium\core\Environment;
use AD\Finance\Money\MoneyIntlFormatter as MoneyFormatter;
use AD\Finance\Money\Monies;
use AD\Finance\Money\NullMoney;

extract(Message::aliases());

Widgets::register('invoices_value', function() use ($t) {
	$formatMoney = function($value) {
		$formatter = new MoneyFormatter(Environment::get('locale'));
		return $formatter->format($value);
	};

	$invoiced = new Monies();
	$paid = new Monies();


	$invoices = Invoices::find('all', [
		'conditions' => [
			'status' => [
				'!=' => 'cancelled'
			]
		]
	]);
	foreach ($invoices as $invoice) {
		foreach ($invoice->totals() as $rate => $currencies) {
			foreach ($currencies as $currency => $price) {
				$invoiced = $total->add($price->getGross());
			}
		}
	}

	$payments = Payments::find('all');
	foreach ($payments as $payment) {
		$paid = $paid->add($payment->amount());
	}

	// FIXME Auto selecting EUR for simplicity.
	if ($invoiced->isZero()) {
		$invoiced = new NullMoney();
	} else {
		$invoiced = $invoiced['EUR'];
	}
	if ($paid->isZero()) {
		$paid = new NullMoney();
	} else {
		$paid = $paid['EUR'];
	}

	return [
		'title' => $t('Cashflow'),
		'url' => [
			'controller' => 'Invoices', 'action' => 'index', 'library' => 'billing_core'
		],
		'data' => [
			$t('invoiced') => $formatter->format($invoiced),
			$t('received') => $formatter->format($paid)
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
]);

?>