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
use lithium\storage\Cache;
use lithium\core\Environment;
use \NumberFormatter;
use Finance\MoneySum;
use Finance\PriceSum;

extract(Message::aliases());

Widgets::register('invoices_value', function() use ($t) {
	$formatMoney = function($value) {
		$formatter = new NumberFormatter(Environment::get('locale'), NumberFormatter::CURRENCY);
		return $formatter->formatCurrency($value->getAmount() / 100, $value->getCurrency());
	};

	$total = new PriceSum();

	$sum = new MoneySum();
	$results = Invoices::find('all', [
		'conditions' => [
			'status' => [
				'!=' => 'cancelled'
			]
		]
	]);
	foreach ($results as $item) {
		$total = $total->add($item->totalAmount()->getGross());
		$sum = $sum->add($item->balance()->getMoney());
	}

	$results = Payments::find('all', [
		'conditions' => [
			'billing_invoice_id' => null
		]
	]);
	foreach ($results as $item) {
		$sum = $sum->add($item->totalAmount());
	}

	return [
		'title' => $t('Cashflow'),
		'url' => [
			'controller' => 'Invoices', 'action' => 'index', 'library' => 'billing_core'
		],
		'class' => $sum->getAmount() < 0 ? 'negative' : 'positive',
		'data' => [
			$t('balance') => !$sum->isZero() ? $formatMoney($sum->getMoney()) : 0,
			$t('total') => !$total->isZero() ? $formatMoney($total->getGross()) : 0
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
]);

?>