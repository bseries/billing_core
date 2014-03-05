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

use lithium\net\http\Router;

$persist = ['persist' => ['admin', 'controller']];

Router::connect('/admin/banners/{:id:[0-9]+}', array(
	'controller' => 'banners', 'library' => 'cms_banner', 'action' => 'view', 'admin' => true
), $persist);
Router::connect('/admin/banners/{:action}', array(
	'controller' => 'banners', 'library' => 'cms_banner', 'admin' => true
), $persist);
Router::connect('/admin/banners/{:action}/{:id:[0-9]+}', array(
	'controller' => 'banners', 'library' => 'cms_banner', 'admin' => true
), $persist);

?>