<?php
/**
 * Billing Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see http://atelierdisko.de/licenses.
 */

namespace billing_core\config;

use base_core\security\Gate;
use li3_access\security\Access;

// Register additional roles.
Gate::registerRole('merchant');
Gate::registerRole('customer');

// Add additional entity rules.
Access::add('entity', 'user.role:merchant', function($user, $entity) {
	return $user->role == 'merchant';
});
Access::add('entity', 'user.role:customer', function($user, $entity) {
	return $user->role == 'customer';
});

?>