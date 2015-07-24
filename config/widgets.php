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

namespace billing_core\config;

use base_core\extensions\cms\Widgets;
use lithium\g11n\Message;

extract(Message::aliases());

Widgets::register('cashflow', function() use ($t) {
	return [
		'title' => $t('Cashflow', ['scope' => 'billing_core']),
		'data' => []
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD
]);

?>