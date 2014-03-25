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
		<span class="object"><?= $title['object'][0] ?></span>
		<span class="title" data-untitled="<?= $untitled ?>"><?= $title['title'] ?></span>
	</h1>

	<?=$this->form->create($item) ?>
		<section>
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

			<?= $this->form->field('number', [
				'type' => 'text',
				'label' => $t('Number'),
				'disabled' => true,
				'class' => 'use-for-title'
			]) ?>
			<div class="help"><?= $t('The invoice number is automatically generated.') ?></div>

			<?= $this->form->field('date', [
				'type' => 'date',
				'label' => $t('Date'),
				'value' => $item->date ?: date('Y-m-d'),
				'disabled' => $item->is_locked
			]) ?>
			<div class="help"><?= $t('Invoice date.') ?></div>

			<?= $this->form->field('address', [
				'type' => 'textarea',
				'label' => $t('Recipient Address'),
				'disabled' => true,
				'value' => $item->address()->format('postal', $locale)
			]) ?>

			<?= $this->form->field('user_vat_reg_no', [
				'type' => 'text',
				'label' => $t('Recipient VAT Reg. No.'),
				'disabled' => true
			]) ?>

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
				'label' => $t('Total (net)'),
				'value' => $this->money->format($item->totalAmount()->getNet(), 'decimal'),
				'disabled' => true
			]) ?>

			<?= $this->form->field('total_gross', [
				'type' => 'text',
				'label' => $t('Total (gross)'),
				'value' => $this->money->format($item->totalAmount()->getGross(), 'decimal'),
				'disabled' => true
			]) ?>

			<?= $this->form->field('total_gross_outstanding', [
				'type' => 'text',
				'label' => $t('Total outstanding (gross)'),
				'value' => $this->money->format($item->totalOutstanding()->getGross(), 'decimal'),
				'disabled' => true,
			]) ?>
		</section>
		<section>
			<h1 class="beta"><?= $t('Positions') ?></h1>
			<table>
				<thead>
					<tr>
						<td><?= $t('Description') ?>
						<td><?= $t('Quantity') ?>
						<td><?= $t('Currency') ?>
						<td><?= $t('Type') ?>
						<td><?= $t('Unit price') ?>
						<td><?= $t('Line total (net)') ?>
						<td>
				</thead>
				<tbody class="use-nested">
				<?php foreach ($item->positions() as $key => $child): ?>
					<?php if (!$item->is_locked): ?>
						<tr class="nested-add nested-item">
							<td>
								<?= $this->form->field('positions.new.description', [
									'type' => 'text',
									'label' => false
								]) ?>
							<td>
								<?= $this->form->field('positions.new.quantity', [
									'type' => 'text',
									'label' => false
								]) ?>
							<td>
								<?= $this->form->field("positions.new.amount_currency", [
									'type' => 'select',
									'label' => false,
									'list' => $currencies
								]) ?>
							<td>
								<?= $this->form->field("positions.new.amount_type", [
									'type' => 'select',
									'label' => false,
									'list' => ['net' => $t('net'), 'gross' => $t('gross')]
								]) ?>
							<td>
								<?= $this->form->field('positions.new.amount', [
									'type' => 'text',
									'label' => false
								]) ?>
							<td>
							<nav class="actions">
								<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
							</nav>
					<?php endif ?>

					<tr class="nested-item">
						<td>
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
								'label' => false,
								'value' => $child->description,
								'disabled' => $item->is_locked
							]) ?>
						<td>
							<?= $this->form->field("positions.{$key}.quantity", [
								'type' => 'text',
								'label' => false,
								'value' => $this->number->format($child->quantity, 'decimal'),
								'disabled' => $item->is_locked
							]) ?>
						<td>
							<?= $this->form->field("positions.{$key}.amount_currency", [
								'type' => 'select',
								'label' => false,
								'list' => $currencies,
								'value' => $child->amount_currency,
								'disabled' => $item->is_locked
							]) ?>
						<td>
							<?= $this->form->field("positions.{$key}.amount_type", [
								'type' => 'select',
								'label' => false,
								'value' => $child->amount_type,
								'list' => ['net' => $t('net'), 'gross' => $t('gross')],
								'disabled' => $item->is_locked
							]) ?>
						<td>
							<?= $this->form->field("positions.{$key}.amount", [
								'type' => 'text',
								'label' => false,
								'value' => $this->money->format($child->totalAmount()->getAmount(), 'decimal'),
								'disabled' => $item->is_locked
							]) ?>
						<td>
							<?= $this->form->field("positions.{$key}.total_net", [
								'type' => 'text',
								'label' => false,
								'value' => $this->money->format($child->totalAmount()->getNet(), 'decimal'),
								'disabled' => true
							]) ?>
						<td>
						<nav class="actions">
						<?php if (!$item->is_locked): ?>
							<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
						<?php endif ?>
						</nav>
				<?php endforeach ?>
				</tbody>
			</table>
			<?php if (!$item->is_locked): ?>
				<?= $this->form->button($t('add another position'), ['type' => 'button', 'class' => 'button add-nested']) ?>
			<?php endif ?>
		</section>

		<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large']) ?>

	<?=$this->form->end() ?>
</article>