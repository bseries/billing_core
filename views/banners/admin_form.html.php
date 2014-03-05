<?php

$title = [
	'action' => ucfirst($this->_request->action === 'add' ? $t('creating') : $t('editing')),
	'title' => $item->title ?: $t('untitled'),
	'object' => [ucfirst($t('banner')), ucfirst($t('banners'))]
];
$this->title("{$title['title']} - {$title['object'][1]}");

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha">
		<span class="action"><?= $title['action'] ?></span>
		<span class="title"><?= $title['title'] ?></span>
	</h1>

	<?=$this->form->create($item) ?>
		<?= $this->form->field('category', ['type' => 'text', 'label' => $t('Category'), 'value' => $item->category ?: 'default']) ?>
		<div class="help"><?= $t('Category can be an abstract name by which you want to group banners or indicate the location of the banner group on the site.') ?></div>

		<div class="media-attachment use-media-attachment-direct">
			<?= $this->form->label('BannerCoverMediaId', $t('Medium')) ?>
			<?= $this->form->hidden('cover_media_id') ?>
			<div class="selected"></div>
			<?= $this->html->link($t('select'), '#', ['class' => 'button select']) ?>
		</div>

		<?= $this->form->field('title', ['type' => 'text', 'label' => $t('Title')]) ?>

		<?= $this->form->field('body', [
			'type' => 'textarea',
			'label' => $t('Text'),
			'wrap' => ['class' => 'body use-editor editor-basic editor-link']
		]) ?>

		<?= $this->form->field('url', ['type' => 'url', 'label' => $t('Link')]) ?>
		<div class="help"><?= $t('Provide an URL to make the banner clickable.') ?></div>

		<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large']) ?>
	<?=$this->form->end() ?>
</article>