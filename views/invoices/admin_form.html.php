<?php

$untitled = $t('Untitled');

$title = [
	'action' => ucfirst($this->_request->action === 'add' ? $t('creating') : $t('editing')),
	'title' => $item->number ?: $untitled,
	'object' => [ucfirst($t('invoice')), ucfirst($t('invoices'))]
];
$this->title("{$title['title']} - {$title['object'][1]}");

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> section-spacing">
	<h1 class="alpha">
		<span class="action"><?= $title['action'] ?></span>
		<span class="title" data-untitled="<?= $untitled ?>"><?= $title['title'] ?></span>
	</h1>

	<?=$this->form->create($item) ?>
		<section>
			<?= $this->form->field('number', [
				'type' => 'text',
				'label' => $t('Number'),
				'disabled' => true,
				'class' => 'use-for-title'
			]) ?>
			<div class="help"><?= $t('The invoice number is automatically generated.') ?></div>

			<?= $this->form->field('user_id', [
				'type' => 'select',
				'label' => $t('User'),
				'list' => $users
			]) ?>

			<?= $this->form->field('user_address', [
				'type' => 'textarea',
				'label' => $t('User address'),
				'disabled' => true,
				''
			]) ?>
			<div class="help"><?= $t('Address taken is the billing address selected by user.') ?></div>

			<?= $this->form->field('status', [
				'type' => 'select',
				'label' => $t('Status'),
				'list' => $statuses
			]) ?>

			<?= $this->form->field('date', [
				'type' => 'date',
				'label' => $t('Date'),
				'value' => $item->date ?: date('Y-m-d')
			]) ?>
			<div class="help"><?= $t('Invoice date.') ?></div>
		</section>
		<section>
			<?= $this->form->field('tax_rate', [
				'type' => 'text',
				'label' => $t('Tax rate'),
				'disabled' => true
			]) ?>

			<?= $this->form->field('tax_note', [
				'type' => 'text',
				'label' => $t('Tax note'),
				'disabled' => true
			]) ?>

			<?= $this->form->field('user_vat_reg_no', [
				'type' => 'text',
				'label' => $t('User VAT Reg. No.'),
				'disabled' => true
			]) ?>
			<div class="help"><?= $t('From user information.') ?></div>
		</section>

		<section>
			<?= $this->form->field('total_currency', [
				'type' => 'select',
				'label' => $t('Total currency'),
				'list' => $currencies,
				'disabled' => true
			]) ?>
			<div class="help"><?= $t('Selected from user setting.') ?></div>

			<?= $this->form->field('total_net', [
				'type' => 'text',
				'label' => $t('Total net'),
				'disabled' => true
			]) ?>
			<div class="help"><?= $t('Derived from positions.') ?></div>

			<?= $this->form->field('total_gross', [
				'type' => 'text',
				'label' => $t('Total gross'),
				'disabled' => true
			]) ?>
			<div class="help"><?= $t('Derived from positions.') ?></div>

			<?= $this->form->field('total_tax', [
				'type' => 'text',
				'label' => $t('Total tax'),
				'disabled' => true
			]) ?>
			<div class="help"><?= $t('Derived from positions and calculated automatically.') ?></div>

			<?= $this->form->field('total_gross_outstanding', [
				'type' => 'text',
				'label' => $t('Total gross outstanding')
			]) ?>
		</section>

		<section class="nested use-nested">
			<h1 class="beta"><?= $t('Positions') ?></h1>

			<article class="nested-add nested-item">
				<h1 class="gamma"><?= $t('New Position') ?></h1>

				<?= $this->form->field('positions.new.description', [
					'type' => 'text',
					'label' => $t('Description')
				]) ?>
				<?= $this->form->field('positions.new.price_eur', [
					'type' => 'text',
					'label' => $t('Price (EUR)')
				]) ?>
				<?= $this->form->field('positions.new.price_usd', [
					'type' => 'text',
					'label' => $t('Price (USD)')
				]) ?>

				<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
			</article>

			<?php foreach ($item->positions() as $key => $child): ?>
				<article class="nested-item">
					<h1 class="gamma"><?= $t('Position') ?></h1>

					<?= $this->form->field("positions.{$key}.id", [
						'type' => 'hidden',
						'value' => $child->id
					]) ?>

					<?= $this->form->field("positions.{$key}._delete", [
						'type' => 'hidden'
					]) ?>

					<?= $this->form->field("positions.{$key}.description", [
						'type' => 'text',
						'label' => $t('Description'),
						'value' => $child->description
					]) ?>
					<?= $this->form->field("positions.{$key}.price_eur", [
						'type' => 'text',
						'label' => $t('Price (EUR)'),
						'value' => $child->price_eur
					]) ?>
					<?= $this->form->field("positions.{$key}.price_usd", [
						'type' => 'text',
						'label' => $t('Price (USD)'),
						'value' => $child->price_usd
					]) ?>

					<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
				</article>
			<?php endforeach ?>
			<?= $this->form->button($t('add another position'), ['class' => 'button add-nested']) ?>
		</section>

		<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large']) ?>

	<?=$this->form->end() ?>
</article>