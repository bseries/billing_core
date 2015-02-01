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
use AD\Finance\Money as MoneyValue;
use AD\Finance\Money\Monies;
use AD\Finance\Money\MoneyIntlFormatter as MoneyFormatter;
use AD\Finance\Price;
use AD\Finance\Price\Prices;
use AD\Finance\Price\PriceIntlFormatter as PriceFormatter;

class Money extends \lithium\template\Helper {

	public function format($value, $type = null, array $options = []) {
		$options += [
			'locale' => null,
			'html' => false
		];
		$locale = $options['locale'] ?: $this->_locale();

		switch ($type) {
			case 'money':
				if ($value instanceof Monies) {
					$formatter = new MoneyFormatter($locale);
					$results = [];

					foreach ($value->sum() as $currency => $money) {
						$results[] = $formatter->format($value);
					}
					return implode(' / ', $results);
				}
				if ($value instanceof MoneyValue) {
					$formatter = new MoneyFormatter($locale);
					return $formatter->format($value);
				}
				if ($value instanceof Prices) {
					$formatter = new PriceFormatter($locale, false, false);
					$results = [];

					foreach ($value->sum() as $rate => $currencies) {
						foreach ($currencies as $currency => $price) {
							$results[] = $formatter->format($value);
						}
					}
					return implode(' / ', $results);
				}
				if ($value instanceof Price) {
					$formatter = new PriceFormatter($locale, false, false);
					return $formatter->format($value);
				}
				return;

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

	// @todo Make this work with formats without currency symbols
	// as well as ,/. separators. Use strrpos.
	/*
	protected function _applyMarkup($string) {
		$before = mb_substr($string, 0, -5);
		$comma = mb_substr($string, -5, 1);
		$places = mb_substr($string, -4, 2);
		$after = mb_substr($string, -2, 2);

		$output  =  $before;
		$output .= '<span class="comma">' . $comma . '</span>';
		$output .= '<span class="decimal-places">' . $places . '</span>';
		$output .= '<span class="currency-symbol">' . $after . '</span>';

		return $output;
	}

	protected function _superscriptPlaces($string) {
		$map = [
			'0' => '⁰',
			'1' => '¹',
			'2' => '²',
			'3' => '³',
			'4' => '⁴',
			'5' => '⁵',
			'6' => '⁶',
			'7' => '⁷',
			'8' => '⁸',
			'9' => '⁹',
		];
		$before = mb_substr($string, 0, -4);
		$places = mb_substr($string, -4, 2);
		$after = mb_substr($string, -2, 2);

		$new = '';
		for ($i = 0; $i < 2; $i++) {
			$new .= $map[mb_substr($places, $i, 1)];
		}
		return $before . $new . $after;
	}
	*/

	protected function _locale() {
		return Environment::get('locale');
	}
}

?>