<?php
/* @var Yii2ConfigPanel $this */
?>
<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
	'caption' => 'Application Configuration',
	'values' => array(
		'Yii Version' => $this->data['application']['yii'],
		'Application Name' => $this->data['application']['name'],
		'Debug Mode' => $this->data['application']['debug'] ? 'Yes' : 'No',
	),
)) ?>
<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
	'caption' => 'PHP Configuration',
	'values' => array(
		'PHP Version' => $this->data['php']['version'],
		'Xdebug' => $this->data['php']['xdebug'] ? 'Enabled' : 'Disabled',
		'APC' => $this->data['php']['apc'] ? 'Enabled' : 'Disabled',
		'Memcache' => $this->data['php']['memcache'] ? 'Enabled' : 'Disabled',
	),
)) ?>
<div>
	<?php echo CHtml::link('phpinfo()', array('phpinfo'), array('class' => 'btn btn-info')) ?>
</div>
