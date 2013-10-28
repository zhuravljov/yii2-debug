<?php
/* @var Yii2DbPanel $this */
/* @var int $count */
/* @var string $time */
?>
<div class="yii2-debug-toolbar-block">
	<a href="<?php echo $this->getUrl(); ?>" title="Executed <?php echo $count; ?> database queries which took <?php echo $time; ?>.">
		DB
		<span class="label"><?php echo $count; ?></span>
		<span class="label"><?php echo $time; ?></span>
	</a>
</div>
