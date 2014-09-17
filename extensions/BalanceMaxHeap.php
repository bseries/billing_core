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

namespace billing_core\extensions;

class BalanceMaxHeap extends \SplMaxHeap {

	protected function compare($a, $b) {
		$a = $a->balance()->getGross();
		$b = $b->balance()->getGross();

		if ($a->equals($b)) {
			return 0;
		}
		return $a->greaterThan($b) ? 1 : -1;
	}
}

?>