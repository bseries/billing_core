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
			'locale' => null,
			'html' => false
		];
		$locale = $options['locale'] ?: $this->_locale();

		switch ($type) {
			case 'money':
				$formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
				$result = $formatter->formatCurrency($value->getAmount() / 100, $value->getCurrency());

				if ($options['html']) {
					return $this->_applyMarkup($result);
				}
				return $result;
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
	protected function _applyMarkup($string) {
		$before = mb_substr($string, 0, -4);
		$places = mb_substr($string, -4, 2);
		$after = mb_substr($string, -2, 2);

		return $before . '<span class="decimal-places">' . $places . '</span>' . '<span class="currency-symbol">' . $after . '</span>';
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

	protected function _locale() {
		return Environment::get('locale');
	}
}

?>