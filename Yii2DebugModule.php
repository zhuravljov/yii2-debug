<?php

/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 */
class Yii2DebugModule extends CWebModule
{
	/**
	 * @var Yii2Debug
	 */
	public $component;

	public function beforeControllerAction($controller, $action)
	{
		if (
			parent::beforeControllerAction($controller, $action) &&
			$this->component->checkAccess()
		) {
			// Отключение дебагера на страницах просмотра ранее сохраненных логов
			Yii::app()->detachEventHandler('onEndRequest', array($this->component, 'onEndRequest'));
			// Отключение сторонних шаблонизаторов
			Yii::app()->setComponent('viewRenderer', null);
			// Сброс скрипта для вывода тулбара
			Yii::app()->getClientScript()->reset();
			return true;
		}
		else
			return false;
	}
}
