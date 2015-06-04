<?php
/**
 * Billing Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace billing_core\models;

use lithium\g11n\Catalog;

class Currencies extends \base_core\models\G11nBase {

	protected static function _available() {
		return explode(' ', PROJECT_CURRENCIES);
	}

	protected static function _data(array $options) {
		$data = [];
		$results = Catalog::read(true, 'currency', $options['translate']);

		foreach ($options['available'] as $available) {
			$data[$available] = [
				'id' => $available,
				'name' => $results[$available]
			];
		}
		return $data;
	}
}

?>