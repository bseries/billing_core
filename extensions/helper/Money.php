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

namespace cms_billing\extensions\helper;

use lithium\core\Environment;
use NumberFormatter;

class Money extends \lithium\template\Helper {

	public function format($value, $type = null, array $options = []) {
		$options += [
			'locale' => null
		];
		$locale = $options['locale'] ?: $this->_locale();

		switch ($type) {
			case 'money':
				$formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
				return $formatter->formatCurrency($value->getAmount() / 100, $value->getCurrency());
			case 'decimal':
				$formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
				$formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
				$formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

				if (is_object($value)) {
					$value = $value->getAmount();
				}
				return $formatter->format($value / 100);
		}
	}

	protected function _locale() {
		return Environment::get('locale');
	}
}

?>