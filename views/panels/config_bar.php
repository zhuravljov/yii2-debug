<?php
/* @var Yii2ConfigPanel $this */
/* @var string $yiiVersion */
/* @var string $phpVersion */
/* @var string $phpUrl */
?>
<div class="yii2-debug-toolbar-block">
	<a href="<?php echo $this->getUrl(); ?>">
		<img width="29" height="30" alt="" src="<?php echo $this->getYiiLogo(); ?>">
		<span><?php echo $yiiVersion; ?></span>
	</a>
</div>
<div class="yii2-debug-toolbar-block">
	<a href="<?php echo $phpUrl; ?>" title="Show phpinfo()">PHP <?php echo $phpVersion; ?></a>
</div>

