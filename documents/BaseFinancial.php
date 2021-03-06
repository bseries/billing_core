<?php
/**
 * Billing Core
 *
 * Copyright (c) 2015 David Persson - All rights reserved.
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace billing_core\documents;

abstract class BaseFinancial extends \base_document\documents\Base {

	protected $_layout = 'financial';

	protected $_entity;

	protected $_type;

	protected $_subject;

	protected $_recipient;

	protected $_sender;

	public function compile() {
		parent::compile();

		// Meta Data.
		$this->metaAuthor($this->_sender->name);
		$this->metaCreator($this->_sender->name);

		if ($this->_subject) {
			$this->metaSubject($this->_subject);
		}

		/* Address field */
		$this->_compileRecipientAddressField();

		/* Numbers and type of letter right */
		if ($this->_type) {
			$this->_compileType();
		}
		$this->_compileNumbers();

		/* Date and City */
		$this->_compileDateAndCity();

		/* Subject */
		if ($this->_subject) {
			$this->_compileSubject();
		}

		/* Financial Table */
		$this->_compileTableHeader();

		foreach ($this->_entity->positions() as $position) {
			$this->_compileTablePosition($position);
		}
		$this->_compileTableFooter();
	}

	// 1.
	abstract protected function _compileRecipientAddressField();

	// 2.
	abstract protected function _compileDateAndCity();

	// 3.
	abstract protected function _compileType();

	// 4.
	abstract protected function _compileNumbers();

	// 5.
	abstract protected function _compileSubject();

	// 6.
	abstract protected function _compileTableHeader();

	// 7.
	abstract protected function _compileTablePosition($position);

	// 8.
	abstract protected function _compileTableFooter();
}

?>