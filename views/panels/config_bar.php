<?php
/* @var Yii2ConfigPanel $this */
/* @var string $phpUrl */
?>
<div class="yii2-debug-toolbar-block">
	<a href="<?php echo $this->getUrl() ?>">
		<img width="29" height="30" alt="" src="<?php echo $this->getYiiLogo() ?>">
		<span><?php echo $this->data['application']['yii'] ?></span>
	</a>
</div>
<div class="yii2-debug-toolbar-block">
	<a href="<?php echo $phpUrl ?>" title="Show phpinfo()">PHP <?php echo $this->data['php']['version'] ?></a>
</div>

