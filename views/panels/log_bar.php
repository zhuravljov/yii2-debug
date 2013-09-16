<?php
/* @var Yii2LogPanel $this */
/* @var int $count */
/* @var int $errorCount */
/* @var int $warningCount */
$title = "Logged $count messages";
if ($errorCount) $title .= " $errorCount errors";
if ($warningCount) $title .= " $warningCount warnings";
?>
<div class="yii2-debug-toolbar-block">
	<a href="<?php echo $this->getUrl() ?>" title="<?php echo $title ?>">
		Log <span class="label"><?php echo $count ?></span>
	</a>
	<?php if ($errorCount): ?>
		<a href="<?php echo $this->getUrl() ?>#first-error" title="<?php echo $title ?>">
			<span class="label label-important"><?php echo $errorCount ?></span>
		</a>
	<?php endif; ?>
	<?php if ($warningCount): ?>
		<a href="<?php echo $this->getUrl() ?>#first-warning" title="<?php echo $title ?>">
			<span class="label label-warning"><?php echo $warningCount ?></span>
		</a>
	<?php endif; ?>
</div>
