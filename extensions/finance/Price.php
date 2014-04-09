<?php
/**
 * Bureau Billing
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace cms_billing\extensions\finance;

use SebastianBergmann\Money\Money;
use SebastianBergmann\Money\Currency;
use Exception;

/**
 * A class which is loosely based upon the Money class. In contrast to money
 * Price can handle net/gross conversions and internally works with floating
 * point numbers in order to minimize error when adding/subtracting prices.
 *
 * Price can optionally work without a given TaxZone but will throw an exception
 * if a conversion from the internal type is attempted.
 */
class Price {

	protected $_amount;

	protected $_currency;

	protected $_type;

	protected $_taxZone;

	public function __construct($value, $currency, $type, $taxZone = null) {
		$this->_amount = $value;
		$this->_currency = $currency;
		$this->_type = $type;
		$this->_taxZone = $taxZone;
	}

	public function getMoney() {
		return new Money((integer) $this->_amount, new Currency($this->_currency));
	}

	public function getAmount($type = null) {
		if ($type === 'net') {
			return $this->getNet()->getAmount();
		} elseif ($type === 'gross') {
			return $this->getGross()->getAmount();
		}
		return $this->_amount;
	}

	public function getCurrency() {
		return $this->_currency;
	}

	public function getType() {
		return $this->_type;
	}

	public function getTaxZone() {
		return $this->_taxZone;
	}

	public function getNet() {
		if ($this->_type === 'net') {
			return $this;
		}
		if (!$this->_taxZone) {
			throw new Exception('Cannot calculate net price without tax zone.');
		}
		return new Price(
			($this->_amount / (100 + $this->_taxZone->rate) * 100),
			$this->_currency,
			'net',
			$this->_taxZone
		);
	}

	public function getGross() {
		if ($this->_type === 'gross') {
			return $this;
		}
		if (!$this->_taxZone) {
			throw new Exception('Cannot calculate gross price without tax zone.');
		}
		return new Price(
			($this->_amount / 100 * (100 + $this->_taxZone->rate)),
			$this->_currency,
			'gross',
			$this->_taxZone
		);
	}

	public function getTax() {
		$result = $this->getGross()->getMoney();
		$result = $result->subtract($this->getNet()->getMoney());

		return $result;
	}

	public function multiply($factor) {
		return new Price(
			$this->_amount * $factor,
			$this->_currency,
			$this->_type,
			$this->_taxZone
		);
	}

	public function add(Price $value) {
		if ($value->getCurrency() !== $this->getCurrency()) {
			throw new Exception('Cannot add prices with different currencies.');
		}
		return new Price(
			$this->getAmount() + $value->getAmount($this->getType()),
			$this->getCurrency(),
			$this->getType(),
			$this->getTaxZone()
		);
	}

	public function subtract(Price $value) {
		if ($value->getCurrency() !== $this->getCurrency()) {
			throw new Exception('Cannot subtract prices with different currencies.');
		}
		return new Price(
			$this->getAmount() - $value->getAmount($this->getType()),
			$this->getCurrency(),
			$this->getType(),
			$this->getTaxZone()
		);
	}

	public function greaterThan(Price $value) {
		return $this->getAmount($value->getType()) > $value->getAmount();
	}
}

?>