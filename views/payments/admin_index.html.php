<?php

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('payments')
	]
]);

?>
<article
	class="use-index-table"
	data-endpoint-sort="<?= $this->url([
		'action' => 'index',
		'page' => $paginator->getPages()->current,
		'orderField' => '__ORDER_FIELD__',
		'orderDirection' => '__ORDER_DIRECTION__'
	]) ?>"
>

	<div class="top-actions">
		<?= $this->html->link($t('new payment'), ['action' => 'add', 'library' => 'billing_core'], ['class' => 'button add']) ?>
	</div>

	<div class="help">
		<?= $t('Payments may be associated with an invoice but do not have to. This is to cater for situations where you receive a payment that you can only associate with an invoice later.') ?>
	</div>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="date" class="date table-sort desc"><?= $t('Date') ?>
					<td data-sort="method" class="method table-sort"><?= $t('Method') ?>
					<td data-sort="user.number" class="user table-sort"><?= $t('Payer') ?>
					<td data-sort="invoice.number" class="invoice table-sort"><?= $t('On Invoice') ?>
					<td><?= $t('Amount') ?>
					<td data-sort="modified" class="date table-sort desc"><?= $t('Modified') ?>
					<td class="actions">
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
					<?php $user = $item->user() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="date">
						<time datetime="<?= $this->date->format($item->date, 'w3c') ?>">
							<?= $this->date->format($item->date, 'date') ?>
						</time>
					<td class="method"><?= $item->method ?: '–' ?>
					<td class="user">
						<?php if ($user): ?>
							<?= $this->html->link($user->title(), [
								'controller' => $user->isVirtual() ? 'VirtualUsers' : 'Users',
								'action' => 'edit', 'id' => $user->id,
								'library' => 'base_core'
							]) ?>
						<?php else: ?>
							-
						<?php endif ?>
					<td class="invoice">
						<?php if ($invoice = $item->invoice()): ?>
							<?= $this->html->link($invoice->title(), [
								'controller' => 'Invoices',
								'action' => 'edit', 'id' => $invoice->id,
								'library' => 'billing_core'
							]) ?>
						<?php else: ?>
							-
						<?php endif ?>
					<td><?= $this->money->format($item->amount()) ?>
					<td class="date modified">
						<time datetime="<?= $this->date->format($item->modified, 'w3c') ?>">
							<?= $this->date->format($item->modified, 'date') ?>
						</time>
					<td class="actions">
						<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'billing_core'], ['class' => 'button delete']) ?>
						<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'billing_core'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>

	<?=$this->view()->render(['element' => 'paging'], compact('paginator'), ['library' => 'base_core']) ?>

</article>