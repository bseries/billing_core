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

namespace cms_banner\models;

class Banners extends \cms_core\models\Base {

	public $belongsTo = [
		'CoverMedia' => [
			'to' => 'cms_media\models\Media',
			'key' => 'cover_media_id'
		]
	];

	protected static $_actsAs = [
		'cms_media\extensions\data\behavior\Coupler' => [
			'bindings' => [
				'cover' => [
					'type' => 'direct',
					'to' => 'cover_media_id'
				]
			]
		],
		'cms_core\extensions\data\behavior\Timestamp',
		'cms_core\extensions\data\behavior\Sortable' => [
			'field' => 'order',
			'cluster' => []
		]
	];

	public static function init() {
		$model = static::_object();

		$model->validates['cover_media_id'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'message' => 'Ein Medium muss ausgewählt sein.'
			]
		];
		$model->validates['category'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'message' => 'Der Banner muss einer Kategorie zugewiesen sein.'
			]
		];
	}
}

Banners::init();

?>