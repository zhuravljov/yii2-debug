<?php
/**
 * @var DefaultController $this
 * @var array $manifest
 */

$this->pageTitle = 'Available Debug Data - Yii Debugger';
?>
<div class="default-index">
	<div class="navbar">
		<div class="navbar-inner">
			<div class="container">
				<div class="yii2-debug-toolbar-block title">
					Yii Debugger
				</div>
			</div>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<h1>Available Debug Data</h1>
			<table class="table table-condensed table-bordered table-striped table-hover table-filtered" style="table-layout: fixed;">
				<thead>
				<tr>
					<th style="width:20px;text-align:center;"><i class="icon-star"></i></th>
					<th style="width: 160px;">Time</th>
					<th style="width: 120px;">IP</th>
					<th style="width: 60px;">Method</th>
					<th style="width: 40px;">Code</th>
					<th>URL</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($manifest as $tag => $data): ?>
					<tr>
						<td style="text-align:center;">
							<?= CHtml::link(
								'<i class="icon-star' . (!$this->getComponent()->getLock($tag) ? '-empty' : '') . '"></i>',
								array('lock', 'tag' => $tag),
								array('class' => 'lock' . ($this->getComponent()->getLock($tag) ? ' active' : ''))
							) ?>
						</td>
						<td><?= CHtml::link(date('Y-m-d h:i:s', $data['time']), array('view', 'tag' => $tag)) ?></td>
						<td><?= $data['ip'] ?></td>
						<td><?= $data['method'] ?></td>
						<td><?= isset($data['code']) ? Yii2RequestPanel::getStatusCodeHtml($data['code']) : '' ?></td>
						<td style="word-break:break-all;"><?= $data['url'] ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php
Yii::app()->clientScript->registerScript(__CLASS__ . '#view', <<<JS
	$('a.lock').click(function(e){
		e.preventDefault();
		var el = $(this);
		$.get(el.attr('href'), function(data){
			if (data) {
				$(el).addClass('active').children('i').addClass('icon-star').removeClass('icon-star-empty');
			} else {
				$(el).removeClass('active').children('i').addClass('icon-star-empty').removeClass('icon-star');
			}
		});
	});
JS
);
Yii::app()->clientScript->registerCss(__CLASS__ . '#view', <<<CSS
	a.lock {opacity: 0.5;}
	a.lock.active, a.lock:hover {opacity: 1;}
CSS
);
?>