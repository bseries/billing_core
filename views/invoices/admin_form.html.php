<?php

use cms_core\extensions\cms\Features;

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
		<span class="status"><?= $item->is_locked ? $t('locked') : $t('unlocked') ?></span>
		<span class="status"><?= $statuses[$item->status] ?></span>
	</h1>

	<?php if ($item->exists()): ?>
		<nav class="actions">
			<?= $this->html->link($t('PDF'), [
				'controller' => 'Invoices',
				'id' => $item->id, 'action' => 'export_pdf',
				'library' => 'cms_billing'
			], ['class' => 'button']) ?>
			<?= $this->html->link($t('XLSX'), [
				'controller' => 'Invoices',
				'id' => $item->id, 'action' => 'export_excel',
				'library' => 'cms_billing'
			], ['class' => 'button']) ?>
			<?= $this->html->link($item->is_locked ? $t('unlock') : $t('lock'), ['id' => $item->id, 'action' => $item->is_locked ? 'unlock': 'lock', 'library' => 'cms_billing'], ['class' => 'button']) ?>
		</nav>
	<?php endif ?>

	<div class="help">
		<?= $t('When the invoice is locked it cannot be changed anymore with the exception of the outstanding amount.') ?>
		<?= $t('The invoice is automatically locked once sent to the user.') ?>
	</div>


	<?=$this->form->create($item) ?>
		<section>
			<?= $this->form->field('status', [
				'type' => 'select',
				'label' => $t('Status'),
				'list' => $statuses
			]) ?>
			<div class="help">
			<?php if (Features::enabled('invoice.sendPaidMail')): ?>
				<?= $t('The user will be notified by e-mail when the status is changed to `paid`.') ?>
			<?php endif ?>
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

			<div class="combined-users-fields">
				<?= $this->form->field('user_id', [
					'type' => 'select',
					'label' => $t('Recipient user'),
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
					'label' => $t('Recipient virtual user'),
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
		</section>

		<section class="use-nested">
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
				<tbody>
				<?php foreach ($item->positions() as $key => $child): ?>
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
								'value' => 1,
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
						<td>
						<nav class="actions">
							<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
						</nav>
				<?php endif ?>
				</tbody>
				<tfoot>
					<tr>
						<td>
							<?php if (!$item->is_locked): ?>
								<?= $this->form->button($t('add position'), ['type' => 'button', 'class' => 'button add-nested']) ?>
							<?php endif ?>
					<tr>
						<td colspan="3">
						<td colspan="2"><?= $t('Total (net)') ?>
						<td><?= ($money = $item->totalAmount()) ? $this->money->format($money->getNet(), 'money') : null ?>
					<tr>
						<td colspan="3">
						<td colspan="2"><?= $t('Tax ({:rate}%)', ['rate' => $item->tax_rate]) ?>
						<td><?= ($money = $item->totalAmount()) ? $this->money->format($money->getTax(), 'money') : null ?>
					<tr>
						<td colspan="3">
						<td colspan="2"><?= $t('Total (gross)') ?>
						<td><?= ($money = $item->totalAmount()) ? $this->money->format($money->getGross(), 'money') : null ?>
					<tr>
						<td colspan="3">
						<td colspan="2"><?= $t('Balance (gross)') ?>
						<td><?= ($money = $item->totalOutstanding()) ? $this->money->format($money->getGross(), 'money') : null ?>
				</tfoot>
			</table>
		</section>

		<section>
			<?= $this->form->field('terms', [
				'type' => 'textarea',
				'label' => $t('Terms'),
				'disabled' => $item->is_locked
			]) ?>
			<div class="help"><?= $t('Visible to recipient.') ?></div>

			<?= $this->form->field('note', [
				'type' => 'textarea',
				'label' => $t('Note'),
				'disabled' => $item->is_locked
			]) ?>
			<div class="help"><?= $t('Visible to recipient.') ?></div>

			<?= $this->form->field('tax_note', [
				'type' => 'text',
				'label' => $t('Tax note'),
				'disabled' => true
			]) ?>
			<div class="help"><?= $t('Visible to recipient.') ?></div>
		</section>

		<section class="use-nested">
			<h1 class="beta"><?= $t('Payments') ?></h1>
			<table>
				<thead>
					<tr>
						<td><?= $t('Date') ?>
						<td><?= $t('Method') ?>
						<td><?= $t('Currency') ?>
						<td><?= $t('Amount') ?>
						<td>
				</thead>
				<tbody>
				<?php foreach ($item->payments() as $key => $child): ?>
					<tr class="nested-item">
						<td>
							<?= $this->form->field("payments.{$key}.id", [
								'type' => 'hidden',
								'value' => $child->id
							]) ?>
							<?= $this->form->field("payments.{$key}._delete", [
								'type' => 'hidden'
							]) ?>
							<?= $this->form->field("payments.{$key}.date", [
								'type' => 'date',
								'label' => false,
								'value' => $child->date
							]) ?>
						<td>
							<?= $this->form->field("payments.{$key}.method", [
								'type' => 'text',
								'label' => false,
								'value' => $child->method
							]) ?>
						<td>
							<?= $this->form->field("payments.{$key}.amount_currency", [
								'type' => 'select',
								'label' => false,
								'list' => $currencies,
								'value' => $child->amount_currency
							]) ?>
						<td>
							<?= $this->form->field("payments.{$key}.amount", [
								'type' => 'text',
								'label' => false,
								'value' => $this->money->format($child->totalAmount()->getAmount(), 'decimal'),
							]) ?>
						<td>
						<nav class="actions">
							<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
						</nav>
				<?php endforeach ?>
					<tr class="nested-add nested-item">
						<td>
							<?= $this->form->field("payments.new.date", [
								'type' => 'date',
								'label' => false,
								'value' => date('Y-m-d')
							]) ?>
						<td>
							<?= $this->form->field("payments.new.method", [
								'type' => 'text',
								'label' => false
							]) ?>
						<td>
							<?= $this->form->field("payments.new.amount_currency", [
								'type' => 'select',
								'label' => false,
								'list' => $currencies
							]) ?>
						<td>
							<?= $this->form->field("payments.new.amount", [
								'type' => 'text',
								'label' => false
							]) ?>
						<td>
						<nav class="actions">
							<?= $this->form->button($t('delete'), ['class' => 'button delete delete-nested']) ?>
						</nav>
				</tbody>
				<tfoot>
					<tr>
						<td>
							<?= $this->form->button($t('add payment'), ['type' => 'button', 'class' => 'button add-nested']) ?>
				</tfoot>
			</table>
		</section>

		<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large']) ?>

	<?=$this->form->end() ?>
</article>