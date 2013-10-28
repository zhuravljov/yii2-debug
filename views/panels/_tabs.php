<?php
/* @var Yii2DebugPanel $this */
/* @var string $id */
/* @var array $items */
?>
<ul id="<?php echo $id; ?>" class="nav nav-tabs">
<?php foreach ($items as $num => $item): ?>
	<li class="<?php echo isset($item['active']) && $item['active'] ? 'active' : ''; ?>">
		<a href="<?php echo "#$id-tab$num"; ?>" data-toggle="tab">
			<?php echo CHtml::encode($item['label']); ?>
		</a>
	</li>
<?php endforeach; ?>
</ul>
<div class="tab-content">
<?php foreach ($items as $num => $item): ?>
	<div id="<?php echo "$id-tab$num"; ?>" class="tab-pane<?php echo isset($item['active']) && $item['active'] ? ' active' : ''; ?>">
		<?php echo $item['content']; ?>
	</div>
<?php endforeach; ?>
</div>