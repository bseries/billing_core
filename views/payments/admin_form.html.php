<?php

$untitled = $t('Untitled');

$title = [
	'action' => ucfirst($this->_request->action === 'add' ? $t('creating') : $t('editing')),
	'title' => $item->number ?: $untitled,
	'object' => [ucfirst($t('payment')), ucfirst($t('payments'))]
];
$this->title("{$title['title']} - {$title['object'][1]}");

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> section-spacing">
	<h1 class="alpha">
		<span class="action"><?= $title['action'] ?></span>
		<span class="title" data-untitled="<?= $untitled ?>"><?= $title['title'] ?></span>
	</h1>

	<?=$this->form->create($item) ?>
		<?= $this->form->field('billing_invoice_id', [
			'type' => 'select',
			'label' => $t('Invoice'),
			'disabled' => $this->_request->action == 'add' && $item->billing_invoice_id,
			'list' => $invoices
		]) ?>

		<?= $this->form->field('date', [
			'type' => 'date',
			'label' => $t('Date'),
			'value' => $item->date ?: date('Y-m-d')
		]) ?>
		<div class="help"><?= $t('Date payment was received.') ?></div>

		<?= $this->form->field('method', [
			'type' => 'text',
			'label' => $t('Method')
		]) ?>

		<?= $this->form->field('currency', [
			'type' => 'select',
			'label' => $t('Currency'),
			'list' => $currencies
		]) ?>

		<?= $this->form->field('amount', [
			'type' => 'text',
			'label' => $t('Amount'),
			'value' => ($money = $item->totalAmount()) ? $this->money->format($money, 'decimal') : null,
		]) ?>

		<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large']) ?>

	<?=$this->form->end() ?>
</article>