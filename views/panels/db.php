<?php
/* @var Yii2DbPanel $this */
/* @var array $queries */
/* @var int $queriesCount */
/* @var array $resume */
/* @var int $resumeCount */
/* @var array $connections */
/* @var int $connectionsCount */
?>
<ul class="nav nav-tabs">
	<li class="active">
		<a href="#queries" data-toggle="tab">
			Queries
			<span class="badge badge-info"><?= $queriesCount ?></span>
		</a>
	</li>
	<li>
		<a href="#resume" data-toggle="tab">
			Resume
			<?php if ($queriesCount > $resumeCount): ?>
				<span class="badge badge-warning" title="Repeated queries: <?= $queriesCount - $resumeCount ?>">
					<?= $resumeCount ?>
				</span>
			<?php else: ?>
				<span class="badge badge-info">
					<?= $resumeCount ?>
				</span>
			<?php endif; ?>
		</a>
	</li>
	<li>
		<a href="#connections" data-toggle="tab">
			Connections
			<span class="badge badge-info"><?= $connectionsCount ?></span>
		</a>
	</li>
</ul>
<div class="tab-content">
	<div id="queries" class="tab-pane active">
		<table class="table table-condensed table-bordered table-filtered" style="table-layout:fixed">
			<thead>
			<tr>
				<th style="width:100px">Time</th>
				<th style="width:80px">Duration</th>
				<th>Query</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($queries as $num => $query): ?>
				<tr>
					<td style="width:100px"><?= $query['time'] ?></td>
					<td style="width:80px"><?= $query['duration'] ?></td>
					<td>
						<?= $this->highlightCode ?
							$this->highlightSql($query['procedure']) :
							CHtml::encode($query['procedure'])
						?>
						<?php if ($this->canExplain && count($explainConnections = $this->getExplainConnections($query['procedure'])) > 0): ?>
							<div class="pull-right">
								<?php if (count($explainConnections) > 1): ?>
									<div class="btn-group">
										<button class="btn btn-link btn-small" data-toggle="dropdown">
											Explain <span class="caret"></span>
										</button>
										<ul class="dropdown-menu pull-right">
											<?php foreach ($explainConnections as $name => $info): ?>
												<li>
													<?= CHtml::link("$name - $info[driver]", array(
														'explain',
														'tag' => $this->tag,
														'num' => $num,
														'connection' => $name,
													), array('class' => 'explain')) ?>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php else: ?>
									<?php foreach ($explainConnections as $name => $info): ?>
										<?= CHtml::link('Explain', array(
											'explain',
											'tag' => $this->tag,
											'num' => $num,
											'connection' => $name,
										), array('class' => 'explain btn btn-link btn-small')) ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div><!-- queries -->
	<div id="resume" class="tab-pane">
		<table class="table table-condensed table-bordered table-striped table-hover table-filtered" style="table-layout:fixed">
			<thead>
				<tr>
					<th style="width:30px;">#</th>
					<th>Query</th>
					<th style="width:50px;">Count</th>
					<th style="width:70px;">Total</th>
					<th style="width:70px;">Avg</th>
					<th style="width:70px;">Min</th>
					<th style="width:70px;">Max</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($resume as $num => $query): ?>
				<tr>
					<td style="width:30px;"><?= $num + 1 ?></td>
					<td>
						<?= $this->highlightCode ?
							$this->highlightSql($query['procedure']) :
							CHtml::encode($query['procedure'])
						?>
					</td>
					<td style="width:50px;"><?= $query['count'] ?></td>
					<td style="width:70px;"><?= $query['total'] ?></td>
					<td style="width:70px;"><?= $query['avg'] ?></td>
					<td style="width:70px;"><?= $query['min'] ?></td>
					<td style="width:70px;"><?= $query['max'] ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div><!-- resume -->
	<div id="connections" class="tab-pane">
	<?php foreach ($connections as $caption => $info): ?>
		<?= $this->render(dirname(__FILE__) . '/_detail.php', array(
			'caption' => $caption,
			'values' => $info,
		)) ?>
	<?php endforeach; ?>
	</div><!-- connections -->
</div>
<?php
Yii::app()->getClientScript()->registerScript(__CLASS__ . '#explain', <<<JS
$('a.explain').click(function(e){
	if (e.altKey || e.ctrlKey || e.shiftKey) return;
	e.preventDefault();
	var block = $(this).data('explain-block');
	if (!block) {
		block = $('<tr>').insertAfter($(this).parents('tr').get(0));
		var div = $('<div class="explain">').appendTo($('<td colspan="3">').appendTo(block));
		div.text('Loading...');
		div.load($(this).attr('href'), {ajax: 1}, function(response, status, xhr){
			if (status == "error") {
				div.text(xhr.status + ': ' + xhr.statusText);
				block.addClass('error');
			}
		});
		$(this).data('explain-block', block);
	} else {
		block.toggle();
	}
});
JS
);
?>