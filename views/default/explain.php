<?php
/**
 * @var DefaultController $this
 * @var array $summary
 * @var string $tag
 * @var array $manifest
 * @var Yii2DebugPanel[] $panels
 * @var Yii2DbPanel $dbPanel
 * @var CDbConnection $connection
 * @var string $procedure
 * @var array $explainRows
 */

$this->pageTitle = 'Explain Query - Yii Debugger';
?>
<div class="default-view">
	<?php $this->renderPartial('_toolbar', array('panels' => $panels)); ?>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<h1>Explain Query (<?php echo $connection->driverName; ?>)</h1>
				<div class="well">
					<?php echo $dbPanel->highlightCode ? $dbPanel->highlightSql($procedure) : CHtml::encode($procedure); ?>
				</div>
				<?php $this->renderPartial('_explain', array(
					'connection' => $connection,
					'explainRows' => $explainRows,
				)); ?>
			</div><!--/span-->
		</div>
	</div>
</div>
