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

namespace billing_core\billing;

use BadMethodCallException;
use billing_core\billing\TaxTypes;

class ClientGroup {

	protected $_config = [];

	public function __construct(array $config) {
		$this->_config = $config + [
			// The internal name of the client group.
			'name' => null,
			// The display title of the client group, can also be a callable
			// for lazy evaluation.
			'title' => null,
			// A matcher that must return `true` when a given user
			// is contained in this client group.
			'conditions' => function($user) { return false; },
			// Tax and currency preferences.
			'taxType' => null,
			'amountCurrency' => 'EUR',
			'amountType' => 'gross'
		];
	}

	public function __call($name, array $arguments) {
		if (!array_key_exists($name, $this->_config)) {
			throw new BadMethodCallException("Method or configuration `{$name}` does not exist.");
		}
		return $this->_config[$name];
	}

	public function conditions($user) {
		return $this->_config['conditions']($user);
	}

	public function title() {
		return is_callable($value = $this->_config[__FUNCTION__]) ? $value() : $value;
	}

	public function taxType() {
		return TaxTypes::registry($this->_config['taxType']);
	}
}

?>