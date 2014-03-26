<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> use-list">
	<h1 class="alpha"><?= $this->title($t('Invoices')) ?></h1>

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
					<td><?= $t('Total (gross)') ?>
					<td><?= $t('Balance (gross)') ?>
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
					<td class="is-locked flag"><?= ($item->is_locked ? '✓' : '╳') ?>
					<td class="date">
						<time datetime="<?= $this->date->format($item->date, 'w3c') ?>">
							<?= $this->date->format($item->date, 'date') ?>
						</time>
					<td class="emphasize number"><?= $item->number ?: '–' ?>
					<td class="status"><?= $item->status ?>
					<td class="user">
						<?php if ($user): ?>
							<?= $this->html->link($user->title(), [
								'controller' => $user->isVirtual() ? 'VirtualUsers' : 'Users',
								'action' => 'edit', 'id' => $user->id,
								'library' => 'cms_core'
							]) ?>
						<?php else: ?>
							-
						<?php endif ?>
					<td><?= ($money = $item->totalAmount()) ? $this->money->format($money->getNet(), 'money') : null ?>
					<td><?= ($money = $item->totalAmount()) ? $this->money->format($money->getGross(), 'money') : null ?>
					<td><?= ($money = $item->totalOutstanding()) ? $this->money->format($money->getGross(), 'money') : null ?>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td class="actions">
						<?= $this->html->link($item->is_locked ? $t('unlock') : $t('lock'), ['id' => $item->id, 'action' => $item->is_locked ? 'unlock': 'lock', 'library' => 'cms_billing'], ['class' => 'button']) ?>
						<?= $this->html->link($t('XLSX'), ['id' => $item->id, 'action' => 'export_excel', 'library' => 'cms_billing'], ['class' => 'button']) ?>
						<?= $this->html->link($t('PDF'), ['id' => $item->id, 'action' => 'export_pdf', 'library' => 'cms_billing'], ['class' => 'button']) ?>
						<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'cms_billing'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>