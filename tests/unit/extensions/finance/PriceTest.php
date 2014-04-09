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

namespace cms_billing\tests\unit\extensions\finance;

use cms_billing\extensions\finance\Price;

class PriceTest extends \PHPUnit_Framework_TestCase {

	public function testCreateLeavesAmountUntouched() {
		$subject = new Price(1, 'EUR', 'net');

		$expected = 1;
		$result = $subject->getNet()->getAmount();
		$this->assertEquals($expected, $result);
	}
}

?>