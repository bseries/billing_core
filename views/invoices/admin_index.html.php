<?php

$dateFormatter = new IntlDateFormatter(
	'de_DE',
	IntlDateFormatter::SHORT,
	IntlDateFormatter::SHORT,
	$authedUser['timezone']
);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha"><?= $this->title($t('Invoices')) ?></h1>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td class="emphasize"><?= $t('Number') ?>
					<td class="status"><?= $t('Status') ?>
					<td><?= $t('Total currency') ?>
					<td><?= $t('Total net') ?>
					<td><?= $t('Total gross') ?>
					<td class="date created"><?= $t('Date') ?>
					<td class="date created"><?= $t('Created') ?>
					<td>
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
				<tr data-id="<?= $item->id ?>">
					<td class="emphasize"><?= $item->number ?: '–' ?>
					<td class="status"><?= $item->status ?>
					<td><?= $item->total_currency ?>
					<td><?= $item->total_net ?>
					<td><?= $item->total_gross ?>
					<td class="date">
						<?php $date = DateTime::createFromFormat('Y-m-d', $item->date) ?>
						<time datetime="<?= $date->format(DateTime::W3C) ?>"><?= $dateFormatter->format($date) ?></time>
					<td class="date created">
						<?php $date = DateTime::createFromFormat('Y-m-d H:i:s', $item->created) ?>
						<time datetime="<?= $date->format(DateTime::W3C) ?>"><?= $dateFormatter->format($date) ?></time>
					<td>
						<nav class="actions">
							<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'cms_billing'], ['class' => 'button']) ?>
							<?= $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'cms_billing'], ['class' => 'button']) ?>
						</nav>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>