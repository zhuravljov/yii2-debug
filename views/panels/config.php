<?php
/* @var Yii2ConfigPanel $this */
/* @var array $data */
?>
<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
	'caption' => 'Application Configuration',
	'values' => array(
		'Yii Version' => $data['application']['yii'],
		'Application Name' => $data['application']['name'],
		'Time Zone' => isset($data['application']['timezone']) ? $data['application']['timezone'] : '',
		'Debug Mode' => $data['application']['debug'] ? 'Yes' : 'No',
	),
)); ?>
<?php if ($this->owner->showConfig): ?>
<div>
	<?php echo CHtml::link('Configuration', array('config'), array('class' => 'btn btn-info')); ?>
</div>
<?php endif; ?>
<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
	'caption' => 'PHP Configuration',
	'values' => array(
		'PHP Version' => $data['php']['version'],
		'Xdebug' => $data['php']['xdebug'] ? 'Enabled' : 'Disabled',
		'APC' => $data['php']['apc'] ? 'Enabled' : 'Disabled',
		'Memcache' => $data['php']['memcache'] ? 'Enabled' : 'Disabled',
	),
)); ?>
<div>
	<?php echo CHtml::link('phpinfo()', array('phpinfo'), array('class' => 'btn btn-info')); ?>
</div>
