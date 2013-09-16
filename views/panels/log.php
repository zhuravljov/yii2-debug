<?php
/* @var Yii2LogPanel $this */
?>
<table class="table table-condensed table-bordered table-striped table-hover table-filtered" style="table-layout: fixed;">
	<thead>
		<tr>
			<th style="width: 100px;">Time</th>
			<th style="width: 65px;">Level</th>
			<th style="width: 250px;">Category</th>
			<th>Message</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($this->data['messages'] as $log): ?>
		<?php
		list ($message, $level, $category, $time, $traces) = $log;
		$rowOpt = '';
		switch ($level) {
			case CLogger::LEVEL_ERROR:
				$firstError = !isset($firstError);
				if ($firstError) $rowOpt = 'id="first-error"';
				break;
			case CLogger::LEVEL_WARNING:
				$firstWarning = !isset($firstWarning);
				if ($firstWarning) $rowOpt = 'id="first-warning"';
				break;
		}
		switch ($level) {
			case CLogger::LEVEL_ERROR: $class = 'error'; break;
			case CLogger::LEVEL_WARNING: $class = 'warning'; break;
			case CLogger::LEVEL_INFO: $class = 'info'; break;
			default: $class = ''; break;
		}
		?>
		<tr <?php echo $rowOpt ?> class="<?php echo $class ?>">
			<td style="width:100px"><?php echo $time ?></td>
			<td style="width:100px"><?php echo $level ?></td>
			<td style="width:250px"><?php echo $category ?></td>
			<td>
				<div style="overflow:auto">
					<?php echo nl2br(CHtml::encode($message)) ?>
					<?php if (!empty($traces)): ?>
						<ul class="trace">
						<?php foreach ($traces as $trace): ?>
							<li>
								<?php echo CHtml::encode($trace) ?>
							</li>
						<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
