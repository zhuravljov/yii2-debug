<?php
/* @var Yii2ViewPanel $this */
/* @var array $data */
?>
<?php foreach ($data as $item): ?>
	<?= $this->render(dirname(__FILE__) . '/_detail.php', array(
		'caption' => $item['view'],
		'values' => $item['data'],
	)) ?>
<?php endforeach; ?>