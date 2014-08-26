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
use cms_core\extensions\cms\Widgets;
use billing_core\models\Invoices;
use lithium\storage\Cache;
use lithium\core\Environment;
use \NumberFormatter;

extract(Message::aliases());

Widgets::register('invoices_value', function() use ($t) {
	$formatMoney = function($value) {
		if (!$value) {
			return 0;
		}
		$formatter = new NumberFormatter(Environment::get('locale'), NumberFormatter::CURRENCY);
		return $formatter->formatCurrency($value->getAmount() / 100, $value->getCurrency());
	};

	$paid = null;
	$results = Invoices::find('all', [
		'conditions' => [
			'status' => ['paid']
		]
	]);
	foreach ($results as $item) {
		if ($paid) {
			$paid = $paid->add($item->totalAmount());
		} else {
			$paid = $item->totalAmount();
		}
	}
	$open = null;
	$results = Invoices::find('all', [
		'conditions' => [
			'status' => ['!=' => ['paid', 'cancelled']]
		]
	]);
	foreach ($results as $item) {
		if ($open) {
			$open = $open->add($item->totalAmount());
		} else {
			$open = $item->totalAmount();
		}
	}


	return [
		'title' => $t('Invoices (net)'),
		'url' => [
			'controller' => 'Invoices', 'action' => 'index', 'library' => 'billing_core'
		],
		'data' => [
			$t('paid') => $formatMoney($paid->getNet()),
			$t('open') => $formatMoney($open->getNet()),
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
]);

?>