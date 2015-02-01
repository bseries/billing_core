<?php

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('invoices')
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> use-list">

	<div class="top-actions">
		<?= $this->html->link($t('new invoice'), ['action' => 'add', 'library' => 'billing_core'], ['class' => 'button add']) ?>
	</div>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="is-locked" class="flag list-sort is-locked "><?= $t('locked?') ?>
					<td data-sort="date" class="date list-sort"><?= $t('Date') ?>
					<td data-sort="number" class="emphasize number list-sort desc"><?= $t('Number') ?>
					<td data-sort="status" class="status list-sort"><?= $t('Status') ?>
					<td data-sort="user" class="user list-sort"><?= $t('Recipient') ?>
					<td><?= $t('Total (net)') ?>
					<td><?= $t('Balance') ?>
					<td data-sort="created" class="date created list-sort"><?= $t('Created') ?>
					<td class="actions">
						<?= $this->form->field('search', [
							'type' => 'search',
							'label' => false,
							'placeholder' => $t('Filter'),
							'class' => 'list-search'
						]) ?>
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
					<td><?= $this->money->format($item->totals(), 'money') ?>
					<td><?= $this->money->format($item->balance(), 'money') ?>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
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
</article>