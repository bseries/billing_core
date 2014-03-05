<?php

use \DateTime;
use \DateInterval;

class BillingScheduledInvoicePosition extends AppModel {

	public $belongsTo = [
		'User',

		// Polymorphic Associations
		// 'BillingPlan' => array('conditions' => array('BillingScheduledInvoicePosition.model' => 'BillingPlan'), 'foreignKey' => 'foreign_key'),
		// 'BillingPlanExtra' => array('conditions' => array('BillingScheduledInvoicePosition.model' => 'BillingPlanExtra'), 'foreignKey' => 'foreign_key'),
	];

	public $enum = [
		'period' => ['month', 'year']
	];

	public function mustPosition($id) {
		$now = new DateTime();

		$stop = $this->field('stop', compact('id'));
		$stop = DateTime::createFromFormat('Y-m-d', $stop);

		// Exclusive stop; stop is not just the last day of execution.
		// Always let the last day of billing period pass first fully.
		return $now > $stop;
	}

	// This may lead to a partial or full billing.
	// 1 day as the smallest unit.
	public function position($scheduled) {
		$InvoicePosition = ClassRegistry::init('BillingInvoicePosition');

		$scheduled = $this->find('first', [
			'conditions' => ['id' => $scheduled]
		]);
		$locale = $this->User->field('locale', [
			['id' => $scheduled[$this->alias]['user_id']]
		]);

		$mapTranslations = [
			'1 month' => __l('1 month', $locale),
			'1 year' => __l('1 year', $locale),
			'%d%% of a month' => __l('%d%% of a month', $locale),
			'%d%% of a year' => __l('%d%% of a year', $locale)
		];

		$period = $scheduled[$this->alias]['period'];

		extract($this->_currentPeriodDates($scheduled, $locale));

		$fakeStop = $this->_advanceDateByPeriod($period, $start);
		$totalUnits = $fakeStop->diff($start)->days;
		$usedUnits = $stop->diff($start)->days;

		// Adding one day because of overlapping.
		if ($totalUnits == ($usedUnits + 1)) {
			$modify = 1;
			$amount = __m('1 ' . $period, $mapTranslations);
		} else {
			$modify = ($usedUnits + 1) / $totalUnits;
			$amount = sprintf(__m('%d%% of a ' . $period, $mapTranslations), $modify * 100);
		}

		// Create invoice position, then update positioned date on scheduled.
		$data = [
			$InvoicePosition->alias => [
				'user_id' => $scheduled[$this->alias]['user_id'],
				'description' => sprintf(
					'%s; %s–%s, %s',
					$scheduled[$this->alias]['description'],
					$startLocalized,
					$stopLocalized,
					$amount
				),
				'price_eur' => round($scheduled[$this->alias]['price_eur'] * $modify, 2),
				'price_usd' => round($scheduled[$this->alias]['price_usd'] * $modify, 2),
			]
		];
		$InvoicePosition->create();
		return (boolean) $InvoicePosition->save($data);
	}

	// Ensure we have enough execution room.
	public function advance($id) {
		$item = $this->find('first', [
			'conditions' => ['id' => $id],
			'fields' => ['id', 'start', 'stop', 'period']
		]);

		$start = $this->_advanceDateByPeriod(
			$item[$this->alias]['period'],
			DateTime::createFromFormat('Y-m-d', $item[$this->alias]['start'])
		);
		$stop = $this->_advanceDateByPeriod(
			$item[$this->alias]['period'],
			DateTime::createFromFormat('Y-m-d', $item[$this->alias]['stop'])
		);

		return (boolean) $this->save([
			$this->alias => [
				'id' => $item[$this->alias]['id'],
				'start' => $start->format('Y-m-d'),
				'stop' => $stop->format('Y-m-d')
			]
		]);
	}

	public function createFromPlan($related, $plan, $locale, $now = null) {
		$now = $now ?: new DateTime();

		$start = clone $now;

		$stop = clone $start;
		$stop = $this->_advanceDateByPeriod('month', $stop);
		$stop->sub(new DateInterval('P1D')); // Prevent overlapping.

		$data = [
			$this->alias => $related + [
				'description' => sprintf(
					__l('the %s plan', $locale),
					$plan['BillingPlan']['name']
				),
				'price_eur' => $plan['BillingPlan']['price_eur'],
				'price_usd' => $plan['BillingPlan']['price_usd'],
				'period' => 'month',
				'start' => $start->format('Y-m-d'),
				'stop' => $stop->format('Y-m-d')
			]
		];
		$this->create();
		return (boolean) $this->save($data);
	}

	public function createFromExtra($related, $extra, $locale, $now = null) {
		$now = $now ?: new DateTime();

		$start = clone $now;

		$stop = clone $start;
		$stop = $this->_advanceDateByPeriod('month', $stop);
		$stop->sub(new DateInterval('P1D')); // Prevent overlapping.

		$data = [
			$this->alias => $related + [
				'description' => sprintf(
					__l('Extra option domain %s.', $locale),
					$extra['BillingExtra']['description']
				),
				'price_eur' => $extra['BillingExtra']['price_eur'],
				'price_usd' => $extra['BillingExtra']['price_usd'],
				'period' => 'month',
				'start' => $start->format('Y-m-d'),
				'stop' => $stop->format('Y-m-d')
			]
		];
		$this->create();
		return (boolean) $this->save($data);
	}

	protected function _advanceDateByPeriod($period, $date) {
		$date = clone $date;

		if ($period == 'month') {
			$date = $this->_sameDayNextMonth($date);
		} else {
			$PeriodInterval = new DateInterval('P1' . strtoupper($period[0]));
			$date->add($PeriodInterval);
		}
		return $date;
	}

	protected function _sameDayNextMonth($date) {
		$date = clone $date;

		if ($date->format('t') == $date->format('j')) {
			$date->modify('last day of next month');
		} else {
			$date->add(new DateInterval('P1M'));
		}
		return $date;
	}

	// @fixme This is ugly get rid of it sooner or later.
	protected function _currentPeriodDates($scheduled, $locale) {
		$backup = Configure::read('G11n.locale');
		Configure::write('G11n.locale', $locale);
		$this->Behaviors->attach('G11n.Localizable', [
			'fields' => [
				'start' => ['format' => 'date'],
				'stop' => ['format' => 'date']
			]
		]);

		$start = DateTime::createFromFormat('Y-m-d', $scheduled[$this->alias]['start']);
		$stop = DateTime::createFromFormat('Y-m-d', $scheduled[$this->alias]['stop']);
		$startLocalized = $this->localizeField('start', $scheduled[$this->alias]['start']);
		$stopLocalized = $this->localizeField('stop', $scheduled[$this->alias]['stop']);

		// Do not normalize not-localized fields on save later.
		$this->Behaviors->detach('Localizable');
		Configure::write('G11n.locale', $backup);

		return compact('start', 'startLocalized', 'stop', 'stopLocalized');
	}
}

?>