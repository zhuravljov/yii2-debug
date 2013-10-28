<?php
/* @var Yii2RequestPanel $this */
/* @var int $statusCode */
/* @var string $route */
/* @var string $action */
?>
<?php if ($statusCode): ?>
	<div class="yii2-debug-toolbar-block">
		<a href="<?php echo $this->getUrl(); ?>" title="Status code: <?php echo $statusCode; ?>">
			Status <?php echo Yii2RequestPanel::getStatusCodeHtml($statusCode); ?>
		</a>
	</div>
<?php endif; ?>
<div class="yii2-debug-toolbar-block">
	<a href="<?php echo $this->getUrl(); ?>" title="Route: <?php echo $route; ?>">
		Action <span class="label"><?php echo $action; ?></span>
	</a>
</div>
