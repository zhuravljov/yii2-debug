<?php
/* @var Yii2RequestPanel $this */
/* @var int $statusCode */
/* @var string $route */
/* @var string $action */
?>
<?php if ($statusCode): ?>
	<div class="yii2-debug-toolbar-block">
		<a href="<?= $this->getUrl() ?>" title="Status code: <?= $statusCode ?>">
			Status <?= Yii2RequestPanel::getStatusCodeHtml($statusCode) ?>
		</a>
	</div>
<?php endif; ?>
<div class="yii2-debug-toolbar-block">
	<a href="<?= $this->getUrl() ?>" title="Route: <?= $route ?>">
		Action <span class="label"><?= $action ?></span>
	</a>
</div>
