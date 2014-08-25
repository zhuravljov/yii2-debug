<?php

/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 *
 * @property Yii2Debug $owner
 */
class Yii2DebugModule extends CWebModule
{
	public function beforeControllerAction($controller, $action)
	{
		if (
			parent::beforeControllerAction($controller, $action) &&
			$this->owner->checkAccess()
		) {
			// Отключение дебагера на страницах просмотра ранее сохраненных логов
			Yii::app()->detachEventHandler('onEndRequest', array($this->owner, 'onEndRequest'));
			// Отключение сторонних шаблонизаторов
			Yii::app()->setComponents(array('viewRenderer' => array('enabled' => false)), false);
			// Сброс скрипта для вывода тулбара
			Yii::app()->getClientScript()->reset();
			// Clears client script map defined in app config
			Yii::app()->getClientScript()->scriptMap = array();
			return true;
		}
		else
			return false;
	}

	private $_owner;

	/**
	 * @return Yii2Debug
	 */
	public function getOwner()
	{
		return $this->_owner;
	}

	/**
	 * @param Yii2Debug $owner
	 */
	public function setOwner($owner)
	{
		$this->_owner = $owner;
	}
}
