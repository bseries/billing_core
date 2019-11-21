<?php
/**
 * Billing Core
 *
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace billing_core\billing;

use BadMethodCallException;
use billing_core\billing\TaxTypes;
use ecommerce_core\ecommerce\aquisition\Methods as AquisitionMethods;

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
			'amountType' => 'gross',
			'method' => 'buy'
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

	public function method() {
		return AquisitionMethods::registry($this->_config['method']);
	}
}

?>