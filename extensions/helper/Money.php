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

use lihtium\core\Environment;
use SebastianBergmann\Money\Money as MoneyMoney;
use SebastianBergmann\Money\IntlFormatter;

class Money extends \lithium\template\Helper {

	public function format(MoneyMoney $value, $type = null, array $options = []) {
		$options += [
			'locale' => null
		];
		$locale = $options['locale'] ?: $this->_locale();

		switch ($type) {
			case 'money':
				$formatter = new IntlFormatter($locale);
				return $formatter->format($value);
			case 'decimal':
				$formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
				return $formatter->format($value->getAmount() / 100);
		}
	}

	protected function _locale() {
		return Environment::get('locale');
	}
}

?>