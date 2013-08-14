<?php
/**
 * @var CController $this
 * @var string $content
 */

$assetUrl = CHtml::asset(Yii::getPathOfAlias('debug.assets'));
/* @var CClientScript $cs */
$cs = Yii::app()->getClientScript();
$cs->registerCoreScript('jquery');
$cs->registerScriptFile($assetUrl . '/js/bootstrap.js');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="<?php echo Yii::app()->language; ?>" />
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $assetUrl; ?>/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $assetUrl; ?>/css/main.css" />
</head>
<body>
	<?php echo $content; ?>
</body>
</html>
