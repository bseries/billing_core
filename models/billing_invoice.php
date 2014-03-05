<?php

// Given our business resides in Germany DE and we're selling services
// which fall und § 3 a Abs. 4 UStG (Katalogleistung).
//
// @link http://www.hk24.de/recht_und_steuern/steuerrecht/umsatzsteuer_mehrwertsteuer/umsatzsteuer_mehrwertsteuer_international/367156/USt_grenzueber_Dienstleistungen.html
// @link http://www.revenue.ie/en/tax/vat/leaflets/place-of-supply-of-services.html
// @link http://www.hk24.de/en/international/tax/347922/vat_goods_trading_eu.html
// @link http://www.stuttgart.ihk24.de/recht_und_steuern/steuerrecht/Umsatzsteuer_Verbrauchssteuer/Umsatzsteuer_international/971988/Steuern_und_Abgaben_grenzueberschreitend.html#121
// @link http://www.hk24.de/recht_und_steuern/steuerrecht/umsatzsteuer_mehrwertsteuer/umsatzsteuer_mehrwertsteuer_international/644156/Uebersetzung_Steuerschuldnerschaft_des_Leistungsempfaengers.html
class BillingInvoice extends AppModel {

	public $belongsTo = ['User'];

	public $hasMany = ['BillingInvoicePosition'];

	public $enum = [
		'status' => ['created', 'sent', 'paid', 'void']
	];

	public $virtualFields = [
		'is_overdue' => "BillingInvoice.total_gross_outstanding != 0 AND BillingInvoice.date + INTERVAL 2 WEEK < CURDATE()"
	];

	public function totalOutstanding($user = null) {
		$results = $this->find('all', [
			'conditions' => $user ? ['user_id' => $user] : []
		]);
		$outstanding = [];

		foreach ($results as $result) {
			$currency = $result['BillingInvoice']['total_currency'];

			if (!isset($outstanding[$currency])) {
				$outstanding[$currency]  = $result['BillingInvoice']['total_gross_outstanding'];
			} else {
				$outstanding[$currency] += $result['BillingInvoice']['total_gross_outstanding'];
			}
		}
		return $outstanding;
	}

	// If the user should get an invoice taking her invoice frequency into account.
	public function mustGenerate($user, $frequency) {
		if (!$this->BillingInvoicePosition->pending($user)) {
			// User has no pending lines, no need to create an empty invoice.
			return false;
		}

		$invoice = $this->find('first', [
			'conditions' => ['user_id' => $user],
			'fields' => ['date'],
			'order' => 'date DESC'
		]);
		if (!$invoice) {
			return true; // No last billing available.
		}
		$last = DateTime::createFromFormat('Y-m-d', $invoice['BillingInvoice']['date']);
		$diff = $last->diff(new DateTime());

		switch ($frequency) {
			case 'monthly':
				return $diff->m >= 1;
			case 'yearly':
				return $diff->y >= 1;
			}
		return false;
	}

	protected function _nextNumber() {
		$number = $this->field('number', ['number LIKE' => date('y') . '%'], 'number DESC');
		$number ? $number++ : $number = date('y') . '00001'; // five digit

		return $number;
	}

	public function generate($user, $currency, array $taxZone, $vatRegNo) {
		// 1st step initalizing the invoice.
		$invoice = [
			$this->alias => [
				'number' => $this->_nextNumber(),
				'user_id' => $user,
				'date' => date('Y-m-d'),
				'user_vat_reg_no' => $vatRegNo, // fixate, may be changed by user
				'tax_rate' => $taxZone['rate'],
				'tax_note' => $taxZone['note'],
				'status' => 'created'
			]
		];
		$this->create();
		if (!$this->save($invoice)) {
			return false;
		}
		$invoiceId = $this->getLastInsertID();

		// Finalize all pending positions.
		$positions = $this->BillingInvoicePosition->pending($user);
		foreach ($positions as $position) {
			$this->BillingInvoicePosition->finalize(
				$position['BillingInvoicePosition']['id'],
				$invoiceId
			);
		}
		$positions = $this->BillingInvoicePosition->find('all', [
			'conditions' => ['billing_invoice_id' => $invoiceId]
		]); // Refresh.

		// 2nd step finalizing the invoice.
		// Calculate invoice totals from positions.
		$totalNet = 0;
		$totalGross = 0;

		foreach ($positions as $item) {
			$price = $item['BillingInvoicePosition']['price_' . strtolower($currency)];
			$totalNet += $price - ($price * $invoice[$this->alias]['tax_rate']);
			$totalGross += $price;
		}
		return (boolean) $this->save([
			$this->alias => [
				'id' => $invoiceId,
				'total_currency' => $currency,
				'total_net' => $totalNet,
				'total_gross' => $totalGross,
				'total_gross_outstanding' => $totalGross,
				'total_tax' => $totalGross - $totalNet,
			]
		]);
	}

	public function taxZone($territory, $vatRegNo, $locale) {
		// Enable if becomes required.
		// if ($vatRegNo && $this->_mustValidateVatRegNo($territory)) {
		//	if (!$this->_validateVatRegNo($vatRegNo)) {
		//		return false;
		//	}
		//}

		// National
		if ($territory == 'DE') {
			return [
				'name' => 'National, Germany',
				'rate' => $rate = 0.19,
				'note' => sprintf(__l('Includes %d%% VAT.', $locale), $rate * 100)
			];
		}

		// EU
		if ($this->_isEuTerritory($territory)) {
			// German VAT applies, § 3 a Abs. 1 S. 2 UStG
			if ($this->_recipientType($vatRegNo) == 'C') {
				return [
					'name' => 'Inter-community, country inside EU',
					'rate' => $rate = 0.19,
					'note' => sprintf(__l('Includes %d%% VAT.', $locale), $rate * 100)
				];
			}
			// Reverse charge, § 13 b UStG
			return [
				'name' => 'Inter-community, country inside EU',
				'rate' => 0,
				// For translations see:
				// @link http://www.hk24.de/recht_und_steuern/steuerrecht/umsatzsteuer_mehrwertsteuer/umsatzsteuer_mehrwertsteuer_international/644156/Uebersetzung_Steuerschuldnerschaft_des_Leistungsempfaengers.html
				'note' => __l('Reverse Charge.', $locale)
			];
		}

		// Third country
		// Reverse charge, B2B, § 13 b UStG
		// Reverse charge, B2C + Katalogleistung
		if (!$this->_reverseChargeGood($territory)) {
			CakeLog::write('error', "Territory {$territory} not good for reverse charge.");
			return false;
		}
		return [
			'name' => 'International, third-country outside EU',
			'rate' => 0,
			'note' => __l('Reverse Charge.', $locale)
		];
	}

	// Detect if beneficiary recipient is business (B) or non-business (C).
	//
	// Type C are private persons AND receiving services for private purposes.
	// Type B are businesses OR receiving services for business purposes.
	//
	// We can take a shorter route here (see "Vertrauensschutz") and rely
	// solely on the presence of the VAT Reg. No.
	protected function _recipientType($vatRegNo) {
		return $vatRegNo ? 'B' : 'C';
	}

	// VAT reg no must be validated inside EU
	// Germany B2B                        : need to check VAT with Bundeszentralamt für Steuern
	// EU B2B                             : need to check VAT with Bundeszentralamt für Steuern
	// third country B2B                  : Do not need to check VAT reg no because reverse charge applies
	// third countr B2C + Katalogleistung : Do not need to check VAT reg no because reverse charge applies
	protected function _mustValidateVatRegNo($territory) {
		return $this->_isEuTerritory($territory); // Implictly includes DE.
	}

	// @todo implement, stub
	protected function _validateVatRegNo($vatRegNo) {
		return true;
	}

	// Note: Includes Germany
	// @link http://publications.europa.eu/code/de/de-370100.htm
	protected function _isEuTerritory($territory) {
		$territories = [
			'BE', 'BG', 'CZ', 'DE', 'DK', 'EE', 'IE', 'EL', 'ES', 'FR',
			'IT', 'CY', 'LV', 'LT', 'LU', 'HU', 'MT', 'NL', 'AT', 'PL',
			'PT', 'RO', 'SI', 'SK', 'FI', 'SE', 'GB'
		];
		return in_array($territory, $territories);
	}

	// Checks if given territory is good for reverse charge.
	//
	// @fixme Failing here shouldn't happen but when it does we need to
	// reread and apply regulations:
	// "Prüfung, ob die umsatzsteuerliche Registrierung des
	// Leistenden oder die Einsetzung eines Fiskalvertreters im
	// jeweiligen anderen EU-Mitgliedstaat notwendig ist."
	//
	// @link http://www.ihk-bonn.de/fileadmin/dokumente/Downloads/Recht_und_Steuern/Umsatzsteuer_National_Vorsteuer/BMF-Schreiben-Gegenseitigkeit10-07.pdf
	protected function _reverseChargeGood($territory) {
		if ($territory == 'DE') {
			return false;
		}
		if ($this->_isEuTerritory($territory)) {
			return true;
		}
		// "Gegenseitigkeit gegeben", § 18 Abs. 9 Satz 6 UStG
		$territories = [
			'AD', // Andorra
			'AG', // Antigua und Barbuda
			'AU', // Australien
			'BS', // Bahamas
			'BH', // Bahrain
			'BM', // Bermudas
			'BA', // Bosnien und Herzegowina
			'VG', // Britische Jungferninseln
			'BN', // Brunaei Darussalam
			'KY', // Cayman-Insel
			'TW', // China (Taiwan)
			'GI', // Gibralatar
			'GD', // Grenada
			'GL', // Grönland
			'GG', // Guernsey
			'HK', // Hongkong (VR China)
			'IQ', // Irak
			'IR', // Iran
			'IS', // Island
			'IL', // Israel
			'JM', // Jamaika
			'JP', // Japan
			'JE', // Jersey
			'CA', // Kanada
			'QA', // Katar
			'KP', // Korea, Dem. Volksrepublik
			'KR', // Korea, Republik
			'HR', // Kroatien
			'KW', // Kuwait
			'LB', // Libanon
			'LR', // Liberia
			'LY', // Lybien
			'LI', // Liechtenstein
			'MO', // Macao
			'MV', // Malediven
			'MK', // Mazedonien
			'AN', // Niederlöndische Antillen
			'NO', // Norwegen
			'OM', // Oman
			'PK', // Pakistan
			'SB', // Salomonen
			'SM', // San Marino
			'SA', // Saudi-Arabien
			'CH', // Schweiz
			'VC', // St. Vincent und die Grenadinen
			'SZ', // Swasiland
			'VA', // Vatikan
			'AE', // Vereinigte Arabische Emirate
			'US'  // USA
		];
		return in_array($territory, $territories);
	}
}

?>