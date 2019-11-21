<?php
/**
 * Billing Core
 *
 * Copyright (c) 2013 David Persson - All rights reserved.
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace billing_core\models;

use lithium\g11n\Catalog;

class Currencies extends \base_core\models\BaseG11n {

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