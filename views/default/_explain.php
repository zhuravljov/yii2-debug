<?php
/**
 * @var DefaultController $this
 * @var CDbConnection $connection
 * @var array $explainRows
 */
?>
<?php if (($first = reset($explainRows)) !== false): ?>
	<table class="table table-condensed table-bordered">
		<thead>
		<tr>
			<?php foreach (array_keys($first) as $key): ?>
				<th><?= CHtml::encode($key) ?></th>
			<?php endforeach; ?>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($explainRows as $row): ?>
			<tr>
				<?php foreach ($row as $value): ?>
					<td><?= CHtml::encode($value) ?></td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<p>Empty</p>
<?php endif; ?>
