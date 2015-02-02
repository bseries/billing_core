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

use Exception;
use lithium\core\Environment;
use NumberFormatter;
use AD\Finance\Money\NullMoney;
use AD\Finance\Money\Monies;
use AD\Finance\Money\MoneyIntlFormatter as MoneyFormatter;
use AD\Finance\Money\MoniesFormatter as MoniesFormatter;

class Money extends \lithium\template\Helper {

	public function format($value, array $options = []) {
		$options += [
			'locale' => null,
			'currency' => true
		];
		$locale = $options['locale'] ?: $this->_locale();

		if ($options['currency']) {
			if (!is_object($value)) {
				throw new Exception('Cannot format money with currency, not given a Money object.');
			}
			if ($value instanceof Monies) {
				$formatter = new MoniesFormatter($locale);
			} else {
				$formatter = new MoneyFormatter($locale);
			}
			return $formatter->format($value);
		}
		$formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
		$formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
		$formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

		if (is_object($value)) {
			if ($value instanceof Monies) {
				$results = [];

				foreach ($value->sum() as $currency => $money) {
					if ($money instanceof NullMoney) {
						continue;
					}
					$results[] = $formatter->format($money->getAmount() / 100);
				}
				if (!$results) {
					return 0;
				}
				return implode(' / ', $results);
			}
			return $formatter->format($value->getAmount() / 100);
		}
		return $formatter->format($value / 100);
	}

	protected function _locale() {
		return Environment::get('locale');
	}
}

?>