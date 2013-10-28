<?php
/* @var Yii2RequestPanel $this */
/* @var array $data */
?>
<ul class="nav nav-tabs">
	<li class="tab-pane active">
		<a href="#params" data-toggle="tab">Parameters</a>
	</li>
	<li class="tab-pane">
		<a href="#headers" data-toggle="tab">Headers</a>
	</li>
	<li class="tab-pane">
		<a href="#session" data-toggle="tab">Session</a>
	</li>
	<li class="tab-pane">
		<a href="#server" data-toggle="tab">$_SERVER</a>
	</li>
</ul>
<div class="tab-content">
	<div id="params" class="tab-pane active">
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => 'Routing',
			'values' => array(
				'Route' => $data['route'],
				'Action' => $data['action'],
				'Parameters' => $data['actionParams'],
			),
		)); ?>
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => '$_GET',
			'values' => $data['GET'],
		)); ?>
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => '$_POST',
			'values' => $data['POST'],
		)); ?>
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => '$_FILES',
			'values' => $data['FILES'],
		)); ?>
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => '$_COOKIE',
			'values' => $data['COOKIE'],
		)); ?>
	</div>
	<div id="headers" class="tab-pane">
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => 'Request Headers',
			'values' => $data['requestHeaders'],
		)); ?>
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => 'Response Headers',
			'values' => $data['responseHeaders'],
		)); ?>
	</div>
	<div id="session" class="tab-pane">
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => '$_SESSION',
			'values' => $data['SESSION'],
		)); ?>
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => 'Flashes',
			'values' => $data['flashes'],
		)); ?>
	</div>
	<div id="server" class="tab-pane">
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => '$_SERVER',
			'values' => $data['SERVER'],
		)); ?>
	</div>
</div>