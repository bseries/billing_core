<?php

$this->set([
	'page' => [
		'type' => 'single',
		'title' => false,
		'empty' => false,
		'object' => $t('payment')
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> section-spacing">
	<?=$this->form->create($item) ?>
		<?= $this->form->field('id', ['type' => 'hidden']) ?>

		<div class="grid-row grid-row-last">
			<div class="grid-column-left">
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
			</div>
			<div class="grid-column-right">
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
			</div>
		</div>
		<div class="bottom-actions">
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'save large']) ?>
		</div>
	<?=$this->form->end() ?>
</article>