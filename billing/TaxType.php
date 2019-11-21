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

class TaxType {

	protected $_config = [];

	public function __construct(array $config) {
		return $this->_config = $config + [
			'title' => null,
			'name' => null,
			// Either percentage as integer or `false` to indicate
			// that no rate is calculated at all.
			'rate' => false,
			'note' => null
		];
	}

	public function __call($name, array $arguments) {
		if (!array_key_exists($name, $this->_config)) {
			throw new BadMethodCallException("Method or configuration `{$name}` does not exist.");
		}
		return $this->_config[$name];
	}

	public function title() {
		return is_callable($value = $this->_config[__FUNCTION__]) ? $value() : $value;
	}

	public function note() {
		return is_callable($value = $this->_config[__FUNCTION__]) ? $value() : $value;
	}
}

?>