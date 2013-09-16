<?php
/* @var Yii2RequestPanel $this */
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
				'Route' => $this->data['route'],
				'Action' => $this->data['action'],
				'Parameters' => $this->data['actionParams'],
			),
		)) ?>
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => '$_GET',
			'values' => $this->data['GET'],
		)) ?>
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => '$_POST',
			'values' => $this->data['POST'],
		)) ?>
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => '$_FILES',
			'values' => $this->data['FILES'],
		)) ?>
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => '$_COOKIE',
			'values' => $this->data['COOKIE'],
		)) ?>
	</div>
	<div id="headers" class="tab-pane">
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => 'Request Headers',
			'values' => $this->data['requestHeaders'],
		)) ?>
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => 'Response Headers',
			'values' => $this->data['responseHeaders'],
		)) ?>
	</div>
	<div id="session" class="tab-pane">
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => '$_SESSION',
			'values' => $this->data['SESSION'],
		)) ?>
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => 'Flashes',
			'values' => $this->data['flashes'],
		)) ?>
	</div>
	<div id="server" class="tab-pane">
		<?php echo $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => '$_SERVER',
			'values' => $this->data['SERVER'],
		)) ?>
	</div>
</div>