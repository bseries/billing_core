<?php

use SebastianBergmann\Money\IntlFormatter;

$dateFormatter = new IntlDateFormatter(
	'de_DE',
	IntlDateFormatter::SHORT,
	IntlDateFormatter::SHORT,
	$authedUser['timezone']
);

$moneyFormatter = new IntlFormatter($locale);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha"><?= $this->title($t('Invoices')) ?></h1>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td>
					<td>
					<td class="flag"><?= $t('locked?') ?>
					<td class="emphasize"><?= $t('Number') ?>
					<td class="status"><?= $t('Status') ?>
					<td><?= $t('Total (gross)') ?>
					<td><?= $t('Total (net)') ?>
					<td><?= $t('Total outstanding (gross)') ?>
					<td class="date created"><?= $t('Date') ?>
					<td class="date created"><?= $t('Created') ?>
					<td>
				<tr>
					<td>
					<td>(pos)
					<td>
					<td colspan="2" class="emphasize"><?= $t('Description') ?>
					<td><?= $t('Total (gross)') ?>
					<td><?= $t('Total (net)') ?>
					<td>
					<td>
					<td class="date created"><?= $t('Created') ?>
					<td>
				<tr>
					<td>
					<td>(pay)
					<td>
					<td colspan="2" class="emphasize"><?= $t('Method') ?>
					<td><?= $t('Total') ?>
					<td>
					<td>
					<td class="date created"><?= $t('Date') ?>
					<td class="date created"><?= $t('Created') ?>
					<td>
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
				<tr data-id="<?= $item->id ?>">
					<td>
					<td>
					<td class="flag"><?= ($item->is_locked ? '✓' : '╳') ?>
					<td class="emphasize"><?= $item->number ?: '–' ?>
					<td class="status"><?= $item->status ?>
					<td><?= ($money = $item->totalAmount('gross')) ? $moneyFormatter->format($money) : null ?>
					<td><?= ($money = $item->totalAmount('net')) ? $moneyFormatter->format($money) : null ?>
					<td><?= ($money = $item->totalOutstanding('gross')) ? $moneyFormatter->format($money) : null ?>
					<td class="date">
						<?php $date = DateTime::createFromFormat('Y-m-d', $item->date) ?>
						<time datetime="<?= $date->format(DateTime::W3C) ?>"><?= $dateFormatter->format($date) ?></time>
					<td class="date created">
						<?php $date = DateTime::createFromFormat('Y-m-d H:i:s', $item->created) ?>
						<time datetime="<?= $date->format(DateTime::W3C) ?>"><?= $dateFormatter->format($date) ?></time>
					<td>
						<nav class="actions">
							<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'cms_billing'], ['class' => 'button']) ?>
							<?= $this->html->link($item->is_locked ? $t('unlock') : $t('lock'), ['id' => $item->id, 'action' => $item->is_locked ? 'unlock': 'lock', 'library' => 'cms_billing'], ['class' => 'button']) ?>
							<?= $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'cms_billing'], ['class' => 'button']) ?>
						</nav>
					<?php foreach ($item->positions() as $sub): ?>
						<tr class="sub-item">
							<td>↳
							<td>(pos)
							<td>
							<td colspan="2" class="emphasize"><?= $sub->description ?>
							<td><?= ($money = $sub->totalAmount('gross')) ? $moneyFormatter->format($money) : null ?>
							<td><?= ($money = $sub->totalAmount('net')) ? $moneyFormatter->format($money) : null ?>
							<td>
							<td>
							<td class="date created">
								<?php $date = DateTime::createFromFormat('Y-m-d H:i:s', $sub->created) ?>
								<time datetime="<?= $date->format(DateTime::W3C) ?>"><?= $dateFormatter->format($date) ?></time>
							<td>
								<nav class="actions">
									<? // $this->html->link($t('delete'), ['id' => $sub->id, 'controller' => 'InvoicePositions', 'action' => 'delete', 'library' => 'cms_billing'], ['class' => 'button']) ?>
									<? // $this->html->link($t('edit'), ['id' => $sub->id, 'controller' => 'InvoicePositions', 'action' => 'edit', 'library' => 'cms_billing'], ['class' => 'button']) ?>
								</nav>

					<?php endforeach ?>
					<?php foreach ($item->payments() as $sub): ?>
						<tr class="sub-item">
							<td>↳
							<td>(pay)
							<td>
							<td colspan="2" class="emphasize"><?= $sub->method ?>
							<td><?= ($money = $sub->totalAmount()) ? $moneyFormatter->format($money) : null ?>
							<td>
							<td>
							<td class="date created">
								<?php $date = DateTime::createFromFormat('Y-m-d', $sub->date) ?>
								<time datetime="<?= $date->format(DateTime::W3C) ?>"><?= $dateFormatter->format($date) ?></time>
							<td class="date created">
								<?php $date = DateTime::createFromFormat('Y-m-d H:i:s', $sub->created) ?>
								<time datetime="<?= $date->format(DateTime::W3C) ?>"><?= $dateFormatter->format($date) ?></time>
							<td>
								<nav class="actions">
									<?= $this->html->link($t('delete'), ['id' => $sub->id, 'controller' => 'Payments', 'action' => 'delete', 'library' => 'cms_billing'], ['class' => 'button']) ?>
									<?= $this->html->link($t('edit'), ['id' => $sub->id, 'controller' => 'Payments', 'action' => 'edit', 'library' => 'cms_billing'], ['class' => 'button']) ?>
								</nav>

					<?php endforeach ?>

				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>