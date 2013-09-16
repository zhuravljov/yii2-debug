<?php
/* @var Yii2RequestPanel $this */
?>
<?php if ($statusCode = $this->data['statusCode']): ?>
	<div class="yii2-debug-toolbar-block">
		<a href="<?php echo $this->getUrl() ?>" title="Status code: <?php echo $statusCode ?>">
			Status
			<?php if ($statusCode >= 200 && $statusCode < 300): ?>
				<span class="label label-success"><?php echo $statusCode ?></span>
			<?php elseif ($statusCode >= 100 && $statusCode < 200): ?>
				<span class="label label-info"><?php echo $statusCode ?></span>
			<?php else: ?>
				<span class="label label-important"><?php echo $statusCode ?></span>
			<?php endif; ?>
		</a>
	</div>
<?php endif; ?>
<div class="yii2-debug-toolbar-block">
	<a href="<?php echo $this->getUrl() ?>">
		Action <span class="label"><?php echo $this->data['action'] ?></span>
	</a>
</div>
