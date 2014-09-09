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

namespace billing_core\extensions\command;

class Invoices extends \lithium\console\Command {

	// Assembles pending invoice position into a single invoice for a user
	// by given user billing frequency.
	public function generateFromPending() {

	}

	// Auto-mails not already sent invoices
	// to user.
	public function sendCreated() {

	}
}

?>