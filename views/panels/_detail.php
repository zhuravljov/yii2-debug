<?php
/* @var Yii2DebugPanel $this */
/* @var string $caption */
/* @var array $values */
?>
<h3><?php echo $caption; ?></h3>
<?php if (!empty($values)): ?>
	<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
		<thead>
			<tr>
				<th style="width: 300px;">Name</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($values as $name => $value): ?>
			<tr>
				<th style="width:300px;word-break:break-all;">
					<?php echo CHtml::encode($name); ?>
				</th>
				<td>
					<div style="overflow:auto">
						<?php if (is_string($value)): ?>
							<?php echo CHtml::encode($value); ?>
						<?php else: ?>
							<div class="src <?php echo $this->highlightCode ? 'hl' : 'no-hl'; ?>"><?php
								CVarDumper::dump($value, 10, $this->highlightCode);
							?></div>
						<?php endif; ?>
					</div>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<p>Empty</p>
<?php endif; ?>