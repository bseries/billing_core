<?php
/**
 * Billing Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see http://atelierdisko.de/licenses.
 */

namespace billing_core\config;

use base_core\extensions\cms\Settings;

Settings::register('contact.billing',
	Settings::read('contact.default') + [
		'vat_reg_no' => 'DE123123123',
		'tax_no' => '12/12/12'
	]
);

Settings::register('billing.paymentTerms', function($user) {
	return null;
});

Settings::register('billing.paymentTerms', function($user) {
	return null;
});


?>