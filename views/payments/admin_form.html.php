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
		<span class="object"><?= $title['object'][0] ?></span>
		<span class="title" data-untitled="<?= $untitled ?>"><?= $title['title'] ?></span>
	</h1>

	<?=$this->form->create($item) ?>
		<?= $this->form->field('billing_invoice_id', [
			'type' => 'select',
			'label' => $t('Invoice'),
			'list' => $invoices
		]) ?>
		<div class="combined-users-fields">
			<?= $this->form->field('user_id', [
				'type' => 'select',
				'label' => $t('User'),
				'list' => $users
			]) ?>
			<div class="help">
				<?= $this->html->link($t('Create new user.'), [
					'controller' => 'Users',
					'action' => 'add',
					'library' => 'cms_core'
				]) ?>
			</div>
			<?= $this->form->field('virtual_user_id', [
				'type' => 'select',
				'label' => $t('Virtual user'),
				'list' => $virtualUsers
			]) ?>
			<div class="help">
				<?= $this->html->link($t('Create new virtual user.'), [
					'controller' => 'VirtualUsers',
					'action' => 'add',
					'library' => 'cms_core'
				]) ?>
			</div>
		</div>
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

		<?= $this->form->field('amount_currency', [
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