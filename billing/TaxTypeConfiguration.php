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

namespace billing_payment\billing\payment;

class TaxTypeConfiguration extends \base_core\core\Configuration {

	public function __construct(array $config) {
		return parent::__conctruct($config + [
			'title' => $data['name'],
			// Either percentage as integer or `false` to indicate
			// that no rate is calculated at all.
			'rate' => false,
			'note' => null
		]);
	}

	public function title() {
		return is_callable($value = $this->_data[__FUNCTION__]) ? $value() : $value;
	}

	public function note() {
		return is_callable($value = $this->_data[__FUNCTION__]) ? $value() : $value;
	}
}

?>