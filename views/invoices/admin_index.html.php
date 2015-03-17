<?php

use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'billing_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('invoices')
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
		<?= $this->html->link($t('new invoice'), ['action' => 'add', 'library' => 'billing_core'], ['class' => 'button add']) ?>
	</div>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="is-locked" class="flag table-sort is-locked "><?= $t('locked?') ?>
					<td data-sort="date" class="date table-sort"><?= $t('Date') ?>
					<td data-sort="number" class="emphasize number table-sort"><?= $t('Number') ?>
					<td data-sort="status" class="status table-sort"><?= $t('Status') ?>
					<td data-sort="user.number" class="user table-sort"><?= $t('Recipient') ?>
					<td><?= $t('Total (net)') ?>
					<td><?= $t('Balance') ?>
					<td data-sort="modified" class="date modified table-sort desc"><?= $t('Modified') ?>
					<td class="actions">
			</thead>
			<tbody class="list">
				<?php foreach ($data as $item): ?>
					<?php $user = $item->user() ?>
				<tr data-id="<?= $item->id ?>">
					<td class="is-locked flag"><?= ($item->is_locked ? '✓' : '×') ?>
					<td class="date">
						<time datetime="<?= $this->date->format($item->date, 'w3c') ?>">
							<?= $this->date->format($item->date, 'date') ?>
						</time>
					<td class="emphasize number"><?= $item->number ?: '–' ?>
					<td class="status"><?= $statuses[$item->status] ?>
					<td class="user">
						<?php if ($user): ?>
							<?= $this->html->link($user->number, [
								'controller' => $user->isVirtual() ? 'VirtualUsers' : 'Users',
								'action' => 'edit', 'id' => $user->id,
								'library' => 'base_core'
							]) ?>
						<?php else: ?>
							-
						<?php endif ?>
					<td><?= $this->price->format($item->totals(), 'net') ?>
					<td><?= $this->money->format($item->balance()) ?>
					<td class="date modified">
						<time datetime="<?= $this->date->format($item->modified, 'w3c') ?>">
							<?= $this->date->format($item->modified, 'date') ?>
						</time>
					<td class="actions">
						<?= $this->html->link($t('PDF'), ['id' => $item->id, 'action' => 'export_pdf', 'library' => 'billing_core'], ['class' => 'button']) ?>
						<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'billing_core'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>

	<?=$this->view()->render(['element' => 'paging'], compact('paginator'), ['library' => 'base_core']) ?>

</article>