<?php
/* @var Yii2ConfigPanel $this */
/* @var array $data */
?>
<?= $this->render(dirname(__FILE__) . '/_detail.php', array(
	'caption' => 'Application Configuration',
	'values' => array(
		'Yii Version' => $data['application']['yii'],
		'Application Name' => $data['application']['name'],
		'Debug Mode' => $data['application']['debug'] ? 'Yes' : 'No',
	),
)) ?>
<?php if ($this->owner->showConfig): ?>
<div>
	<?= CHtml::link('Configuration', array('config'), array('class' => 'btn btn-info')) ?>
</div>
<?php endif; ?>
<?= $this->render(dirname(__FILE__) . '/_detail.php', array(
	'caption' => 'PHP Configuration',
	'values' => array(
		'PHP Version' => $data['php']['version'],
		'Xdebug' => $data['php']['xdebug'] ? 'Enabled' : 'Disabled',
		'APC' => $data['php']['apc'] ? 'Enabled' : 'Disabled',
		'Memcache' => $data['php']['memcache'] ? 'Enabled' : 'Disabled',
	),
)) ?>
<div>
	<?= CHtml::link('phpinfo()', array('phpinfo'), array('class' => 'btn btn-info')) ?>
</div>
