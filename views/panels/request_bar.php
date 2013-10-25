<?php
/* @var Yii2RequestPanel $this */
?>
<?php if ($statusCode = $this->data['statusCode']): ?>
	<div class="yii2-debug-toolbar-block">
		<a href="<?= $this->getUrl() ?>" title="Status code: <?= $statusCode ?>">
			Status <?= Yii2RequestPanel::getStatusCodeHtml($statusCode) ?>
		</a>
	</div>
<?php endif; ?>
<div class="yii2-debug-toolbar-block">
	<a href="<?= $this->getUrl() ?>" title="Route: <?= $this->data['route'] ?>">
		Action <span class="label"><?= $this->data['action'] ?></span>
	</a>
</div>
