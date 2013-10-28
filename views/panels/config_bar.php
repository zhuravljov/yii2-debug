<?php
/* @var Yii2ConfigPanel $this */
/* @var string $yiiVersion */
/* @var string $phpVersion */
/* @var string $phpUrl */
?>
<div class="yii2-debug-toolbar-block">
	<a href="<?= $this->getUrl() ?>">
		<img width="29" height="30" alt="" src="<?= $this->getYiiLogo() ?>">
		<span><?= $yiiVersion ?></span>
	</a>
</div>
<div class="yii2-debug-toolbar-block">
	<a href="<?= $phpUrl ?>" title="Show phpinfo()">PHP <?= $phpVersion ?></a>
</div>

