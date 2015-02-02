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

namespace billing_core\extensions\helper;

use lithium\core\Environment;
use NumberFormatter;
use AD\Finance\Money\Monies;
use AD\Finance\Money\MoneyIntlFormatter as MoneyFormatter;

class Money extends \lithium\template\Helper {

	public function format($value, array $options = []) {
		$options += [
			'locale' => null,
			'currency' => true
		];
		$locale = $options['locale'] ?: $this->_locale();

		if ($options['currency']) {
			$formatter = new MoneyFormatter($locale);

			if ($value instanceof Monies) {
				$results = [];

				foreach ($value->sum() as $currency => $money) {
					$results[] = $formatter->format($money);
				}
				return implode(' / ', $results);
			}
			return $formatter->format($value);
		}
		$formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
		$formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
		$formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

		if ($value instanceof Monies) {
			$results = [];

			foreach ($value->sum() as $currency => $money) {
				$results[] = $formatter->format($money->getAmount() / 100);
			}
			return implode(' / ', $results);
		}
		return $formatter->format($value->getAmount() / 100);
	}

	protected function _locale() {
		return Environment::get('locale');
	}
}

?>