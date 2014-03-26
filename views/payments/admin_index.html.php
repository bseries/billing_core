<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> use-list">
	<h1 class="alpha"><?= $this->title($t('Payments')) ?></h1>

	<div class="help">
		<?= $t('Payments may be associated with an invoice but do not have to. This is to cater for situations where you receive a payment that you can only associate with an invoice later.') ?>
	</div>

	<?php if ($data->count()): ?>
		<table>
			<thead>
				<tr>
					<td data-sort="date" class="date list-sort desc"><?= $t('Date') ?>
					<td data-sort="method" class="method list-sort"><?= $t('Method') ?>
					<td data-sort="user" class="user list-sort"><?= $t('Payer') ?>
					<td data-sort="invoice" class="invoice list-sort"><?= $t('On Invoice') ?>
					<td><?= $t('Amount') ?>
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
								'library' => 'cms_core'
							]) ?>
						<?php else: ?>
							-
						<?php endif ?>
					<td class="invoice">
						<?php if ($invoice = $item->invoice()): ?>
							<?= $this->html->link($invoice->title(), [
								'controller' => 'Invoices',
								'action' => 'edit', 'id' => $invoice->id,
								'library' => 'cms_billing'
							]) ?>
						<?php else: ?>
							-
						<?php endif ?>
					<td><?= ($money = $item->totalAmount()) ? $this->money->format($money->getGross(), 'money') : null ?>
					<td class="actions">
						<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'cms_billing'], ['class' => 'button']) ?>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>