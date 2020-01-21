<?php
/**
 * Billing Core
 *
 * Copyright (c) 2015 David Persson - All rights reserved.
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace billing_core\extensions\helper;

use lithium\core\Environment;
use NumberFormatter;
use AD\Finance\Price\NullPrice;
use AD\Finance\Price\Prices;
use AD\Finance\Money\MoneyIntlFormatter as MoneyFormatter;

class Price extends \lithium\template\Helper {

	public function format($value, $type = 'net', array $options = []) {
		$options += [
			'locale' => null,
			'currency' => true
		];
		$locale = $options['locale'] ?: $this->_locale();
		$byMethod = 'get' . ucfirst($type);

		if ($options['currency']) {
			$formatter = new MoneyFormatter($locale);

			if ($value instanceof Prices) {
				$results = [];

				foreach ($value->sum() as $rate => $currencies) {
					foreach ($currencies as $currency => $price) {
						$results[] = $formatter->format($price->{$byMethod}());
					}
				}
				return implode(' / ', $results);
			}
			return $formatter->format($value->{$byMethod}());
		}
		$formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
		$formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
		$formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

		if ($value instanceof Prices) {
			$results = [];

			foreach ($value->sum() as $rate => $currencies) {
				foreach ($currencies as $currency => $price) {
					if ($price instanceof NullPrice) {
						continue;
					}
					$results[] = $formatter->format($price->{$byMethod}()->getAmount() / 100);
				}
			}
			return implode(' / ', $results);
		}
		return $formatter->format($value->{$byMethod}()->getAmount() / 100);
	}

	protected function _locale() {
		return Environment::get('locale');
	}
}

?>