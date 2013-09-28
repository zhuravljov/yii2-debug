<?php
/**
 * @var DefaultController $this
 * @var Yii2DebugPanel[] $panels
 * @var string $tag
 */

$url = $panels['request']->getUrl();
?>
<div id="yii2-debug-toolbar">
	<?php foreach ($panels as $panel): ?>
		<?= $panel->getSummary() ?>
	<?php endforeach; ?>
	<span class="yii2-debug-toolbar-toggler">›</span>
</div>
<div id="yii2-debug-toolbar-min">
	<a href="<?= $url ?>" title="Open Yii Debugger" id="yii2-debug-toolbar-logo">
		<img width="29" height="30" alt="" src="<?= Yii2ConfigPanel::getYiiLogo() ?>">
	</a>
	<span class="yii2-debug-toolbar-toggler">‹</span>
</div>

<style type="text/css">
	<?= file_get_contents(dirname(__FILE__) . '/toolbar.css') ?>
</style>