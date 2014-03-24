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

			<?= $this->form->field('address', [
				'type' => 'textarea',
				'label' => $t('Address'),
				'disabled' => true,
				'value' => $item->address()->format('postal', $locale)
			]) ?>
			<div class="help"><?= $t('Address taken is the billing address selected by user.') ?></div>

			<?= $this->form->field('status', [
				'type' => 'select',
				'label' => $t('Status'),
				'list' => $statuses
			]) ?>

			<?= $this->form->field('is_locked', [
				'type' => 'checkbox',
				'label' => $t('Locked?'),
				'checked' => $item->is_locked,
				'disabled' => true
			]) ?>
			<div class="help">
				<?= $t('When the invoice is locked it cannot be changed anymore with the exception of the outstanding amount.') ?>
				<?= $t('The invoice is automatically locked once sent to the user.') ?>
			</div>

			<?= $this->form->field('date', [
				'type' => 'date',
				'label' => $t('Date'),
				'value' => $item->date ?: date('Y-m-d'),
				'disabled' => $item->is_locked
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
		</section>

		<section>
			<?= $this->form->field('total_currency', [
				'type' => 'select',
				'label' => $t('Currency'),
				'list' => $currencies,
				'disabled' => true
			]) ?>

			<?= $this->form->field('total_net', [
				'type' => 'text',
				'label' => $t('Total net'),
				'value' => $this->money->format($item->totalAmount()->getNet(), 'decimal'),
				'disabled' => true
			]) ?>
			<div class="help"><?= $t('Derived from positions.') ?></div>

			<?= $this->form->field('total_gross', [
				'type' => 'text',
				'label' => $t('Total gross'),
				'value' => $this->money->format($item->totalAmount()->getGross(), 'decimal'),
				'disabled' => true
			]) ?>
			<div class="help"><?= $t('Derived from positions.') ?></div>

			<?= $this->form->field('total_gross_outstanding', [
				'type' => 'text',
				'label' => $t('Total gross outstanding'),
				'value' => $this->money->format($item->totalOutstanding()->getGross(), 'decimal'),
				'disabled' => true,
			]) ?>
			<div class="help"><?= $t('Derived from positions and calculated automatically.') ?></div>
		</section>

		<section class="nested use-nested">
			<h1 class="beta"><?= $t('Positions') ?></h1>

			<?php if (!$item->is_locked): ?>
				<article class="nested-add nested-item">
					<h1 class="gamma"><?= $t('New Position') ?></h1>

					<?= $this->form->field('positions.new.description', [
						'type' => 'text',
						'label' => $t('Description')
					]) ?>
					<?= $this->form->field("positions.new.amount_currency", [
						'type' => 'select',
						'label' => $t('Currency'),
						'list' => $currencies
					]) ?>
					<?= $this->form->field("positions.new.amount_type", [
						'type' => 'select',
						'label' => $t('Type'),
						'list' => ['net' => $t('net'), 'gross' => $t('gross')]
					]) ?>
					<?= $this->form->field('positions.new.amount', [
						'type' => 'text',
						'label' => $t('Price')
					]) ?>

					<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
				</article>
			<?php endif ?>

			<?php foreach ($item->positions() as $key => $child): ?>
				<article class="nested-item">
					<h1 class="gamma"><?= $t('Position') ?></h1>

					<?= $this->form->field("positions.{$key}.id", [
						'type' => 'hidden',
						'value' => $child->id,
						'disabled' => $item->is_locked
					]) ?>

					<?= $this->form->field("positions.{$key}._delete", [
						'type' => 'hidden',
						'disabled' => $item->is_locked
					]) ?>

					<?= $this->form->field("positions.{$key}.description", [
						'type' => 'text',
						'label' => $t('Description'),
						'value' => $child->description,
						'disabled' => $item->is_locked
					]) ?>
					<?= $this->form->field("positions.{$key}.amount_currency", [
						'type' => 'select',
						'label' => $t('Currency'),
						'list' => $currencies,
						'value' => $child->amount_currency
					]) ?>
					<?= $this->form->field("positions.{$key}.amount_type", [
						'type' => 'select',
						'label' => $t('Type'),
						'value' => $child->amount_type,
						'list' => ['net' => $t('net'), 'gross' => $t('gross')]
					]) ?>

					<?= $this->form->field("positions.{$key}.amount", [
						'type' => 'text',
						'label' => $t('Total'),
						'value' => $this->money->format($child->totalAmount()->getAmount(), 'decimal'),
						'disabled' => $item->is_locked
					]) ?>

					<?= $this->form->field("positions.{$key}.quantity", [
						'type' => 'text',
						'label' => $t('Quantity'),
						'value' => $this->number->format($child->quantity, 'decimal'),
						'disabled' => $item->is_locked
					]) ?>
					<?= $this->form->field("positions.{$key}.total_net", [
						'type' => 'text',
						'label' => $t('Total (net)'),
						'value' => $this->money->format($child->totalAmount()->getNet(), 'decimal'),
						'disabled' => true
					]) ?>
					<?php if (!$item->is_locked): ?>
						<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
					<?php endif ?>
				</article>
			<?php endforeach ?>
			<?php if (!$item->is_locked): ?>
				<?= $this->form->button($t('add another position'), ['class' => 'button add-nested']) ?>
			<?php endif ?>
		</section>

		<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large']) ?>

	<?=$this->form->end() ?>
</article>