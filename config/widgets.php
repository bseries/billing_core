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
use billing_core\models\Invoices;
use lithium\storage\Cache;
use lithium\core\Environment;
use \NumberFormatter;
use Finance\PriceSum;

extract(Message::aliases());

Widgets::register('invoices_value', function() use ($t) {
	$formatMoney = function($value) {
		$formatter = new NumberFormatter(Environment::get('locale'), NumberFormatter::CURRENCY);
		return $formatter->formatCurrency($value->getAmount() / 100, $value->getCurrency());
	};

	$paid = new PriceSum();
	$results = Invoices::find('all', [
		'conditions' => [
			'status' => 'paid'
		]
	]);
	foreach ($results as $item) {
		$paid = $paid->add($item->totalAmount()->getGross());
	}

	$outstanding = new PriceSum();
	$results = Invoices::find('all', [
		'conditions' => [
			'status' => [
				'!=' => 'paid'
			]
		]
	]);
	foreach ($results as $item) {
		$outstanding = $outstanding->add($item->totalAmount()->getGross());
	}

	return [
		'title' => $t('Invoices'),
		'url' => [
			'controller' => 'Invoices', 'action' => 'index', 'library' => 'billing_core'
		],
		'class' => 'positive',
		'data' => [
			$t('paid') => !$paid->isZero() ? $formatMoney($paid->getGross()) : 0,
			$t('outstanding') => !$outstanding->isZero() ? $formatMoney($outstanding->getGross()) : 0
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
]);

?>