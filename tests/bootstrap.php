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

define('LITHIUM_APP_PATH', dirname(dirname(dirname(__DIR__))));
define('LITHIUM_LIBRARY_PATH', dirname(dirname(__DIR__)));

require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/core/Libraries.php';
require LITHIUM_LIBRARY_PATH . '/autoload.php';

\lithium\core\Libraries::add('lithium');
\lithium\core\Libraries::add(basename(dirname(__DIR__)), [
	'bootstrap' => false
]);

?>