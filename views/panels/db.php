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
		<table class="table table-condensed table-bordered table-striped table-hover table-filtered" style="table-layout:fixed">
			<thead>
			<tr>
				<th style="width:100px">Time</th>
				<th style="width:80px">Duration</th>
				<th>Query</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($queries as $i => $query): ?>
				<tr>
					<td style="width:100px"><?= $query['time'] ?></td>
					<td style="width:80px"><?= $query['duration'] ?></td>
					<td>
						<?= $this->highlightCode ?
							$this->highlightSql($query['procedure']) :
							CHtml::encode($query['procedure'])
						?>
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
			<?php foreach ($resume as $i => $query): ?>
				<tr>
					<td style="width:30px;"><?= $i + 1 ?></td>
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