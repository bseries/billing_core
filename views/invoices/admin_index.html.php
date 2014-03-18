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
					<td><?= $t('User') ?>
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
					<td>
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
					<td>
					<td><?= $t('Total') ?>
					<td>
					<td>
					<td class="date created"><?= $t('Date') ?>
					<td class="date created"><?= $t('Created') ?>
					<td>
			</thead>
			<tbody>
				<?php foreach ($data as $item): ?>
					<?php $user = $item->user() ?>
				<tr data-id="<?= $item->id ?>">
					<td>
					<td>
					<td class="flag"><?= ($item->is_locked ? '✓' : '╳') ?>
					<td class="emphasize"><?= $item->number ?: '–' ?>
					<td class="status"><?= $item->status ?>
					<?php if ($user->isVirtual()): ?>
						<td>
							<?= $this->html->link($user->name . '/' . $user->id, [
								'controller' => 'VirtualUsers', 'action' => 'edit', 'id' => $user->id, 'library' => 'cms_core'
							]) ?>
							(<?= $this->html->link('virtual', [
								'controller' => 'VirtualUsers', 'action' => 'index', 'library' => 'cms_core'
							]) ?>)
					<?php else: ?>
						<td>
							<?= $this->html->link($user->name . '/' . $user->id, [
								'controller' => 'Users', 'action' => 'edit', 'id' => $user->id, 'library' => 'cms_core'
							]) ?>
							(<?= $this->html->link('real', [
								'controller' => 'Users', 'action' => 'index', 'library' => 'cms_core'
							]) ?>)
					<?php endif ?>
					<td><?= ($money = $item->totalAmount('gross')) ? $this->money->format($money, 'money') : null ?>
					<td><?= ($money = $item->totalAmount('net')) ? $this->money->format($money, 'money') : null ?>
					<td><?= ($money = $item->totalOutstanding('gross')) ? $this->money->format($money, 'money') : null ?>
					<td class="date">
						<time datetime="<?= $this->date->format($item->date, 'w3c') ?>">
							<?= $this->date->format($item->date, 'date') ?>
						</time>
					<td class="date created">
						<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
							<?= $this->date->format($item->created, 'date') ?>
						</time>
					<td>
						<nav class="actions">
							<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'cms_billing'], ['class' => 'button']) ?>
							<?= $this->html->link($item->is_locked ? $t('unlock') : $t('lock'), ['id' => $item->id, 'action' => $item->is_locked ? 'unlock': 'lock', 'library' => 'cms_billing'], ['class' => 'button']) ?>
							<?= $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'cms_billing'], ['class' => 'button']) ?>
							<?= $this->html->link($t('export XLS'), ['id' => $item->id, 'action' => 'export_excel', 'library' => 'cms_billing'], ['class' => 'button']) ?>
						</nav>
					<?php foreach ($item->positions() as $sub): ?>
						<tr class="sub-item">
							<td>↳
							<td>(pos)
							<td>
							<td colspan="2" class="emphasize"><?= $sub->description ?>
							<td>
							<td><?= ($money = $sub->totalAmount('gross')) ? $this->money->format($money, 'money') : null ?>
							<td><?= ($money = $sub->totalAmount('net')) ? $this->money->format($money, 'money') : null ?>
							<td>
							<td>
							<td class="date created">
								<time datetime="<?= $this->date->format($sub->created, 'w3c') ?>">
									<?= $this->date->format($sub->created, 'date') ?>
								</time>
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
							<td>
							<td><?= ($money = $sub->totalAmount()) ? $this->money->format($money, 'money') : null ?>
							<td>
							<td>
							<td class="date">
								<time datetime="<?= $this->date->format($sub->date, 'w3c') ?>">
									<?= $this->date->format($sub->date, 'date') ?>
								</time>
							<td class="date created">
								<time datetime="<?= $this->date->format($sub->created, 'w3c') ?>">
									<?= $this->date->format($sub->created, 'date') ?>
								</time>
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