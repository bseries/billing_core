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

namespace cms_banner\controllers;

use cms_banner\models\Banners;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;

class BannersController extends \cms_core\controllers\BaseController {

	use \cms_core\controllers\AdminAddTrait;
	use \cms_core\controllers\AdminEditTrait;
	use \cms_core\controllers\AdminDeleteTrait;

	use \cms_core\controllers\AdminOrderTrait;
	use \cms_core\controllers\AdminPublishTrait;

	public function admin_index() {
		$data = Banners::find('all', [
			'order' => ['order' => 'DESC']
		]);
		return compact('data');
	}
}

?>