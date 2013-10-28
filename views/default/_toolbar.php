<?php
/* @var DefaultController $this */
/* @var Yii2DebugPanel[] $panels */

?>
<div class="navbar">
	<div class="navbar-inner">
		<div class="container">
			<div class="yii2-debug-toolbar-block title">
				Yii Debugger
			</div>
			<?php foreach ($panels as $panel): ?>
				<?php echo $panel->getSummary(); ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php
Yii::app()->clientScript->registerScript(__CLASS__ . '#toolbar',
	'$(".yii2-debug-toolbar-block a[title]").tooltip({placement:"bottom"});'
);
?>
