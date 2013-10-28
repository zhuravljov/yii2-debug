<?php
/* @var Yii2ProfilingPanel $this */
/* @var string $time */
/* @var string $memory */
?>
<div class="yii2-debug-toolbar-block">
	<a href="<?php echo $this->getUrl(); ?>" title="Total request processing time was <?php echo $time; ?>">
		Time <span class="label"><?php echo $time; ?></span>
	</a>
</div>
<div class="yii2-debug-toolbar-block">
	<a href="<?php echo $this->getUrl(); ?>" title="Peak memory consumption was <?php echo $memory; ?>">
		Memory <span class="label"><?php echo $memory; ?></span>
	</a>
</div>
