<?php

/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 */
class Yii2ViewPanel extends Yii2DebugPanel
{
	public function getName()
	{
		return 'Views';
	}

	public function __construct($owner, $id)
	{
		parent::__construct($owner, $id);
		$renderer = Yii::app()->getComponent('viewRenderer');
		Yii::app()->setComponent('viewRenderer', null);
		Yii::app()->setComponents(array(
			'viewRenderer' => array(
				'class' => 'Yii2DebugViewRenderer',
				'instance' => $renderer,
			),
		), false);
	}

	public function getSummary()
	{
		if ($count = count($this->data)) {
			return $this->render(dirname(__FILE__) . '/../views/panels/view_bar.php', array(
				'count' => $count,
			));
		}
		return '';
	}

	public function getDetail()
	{
		$data = $this->getData();
		$base = dirname(Yii::app()->getBasePath()) . DIRECTORY_SEPARATOR;
		foreach ($data as &$item) {
			$item['view'] = str_replace($base, '', $item['view']);
			$item['view'] = str_replace('\\', '/', $item['view']);
		}
		unset($item);
		return $this->render(dirname(__FILE__) . '/../views/panels/view.php', array(
			'data' => $data,
		));
	}


	public function save()
	{
		$renderer = Yii::app()->getComponent('viewRenderer');
		if ($renderer instanceof Yii2DebugViewRenderer) {
			return Yii2Debug::prepareData($renderer->getStack());
		}
		return null;
	}

	protected function prepareData($data)
	{
		$result = array();
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$result[$key] = $this->prepareData($value);
			}
		} elseif (is_object($data)) {
			$result['class'] = get_class($data);
			if ($data instanceof CActiveRecord) {
				foreach ($data->attributes as $field => $value) {
					$result[$field] = $value;
				}
			}
			foreach (get_object_vars($data) as $key => $value) {
				$result[$key] = $this->prepareData($value);
			}
		} else {
			$result = $data;
		}
		return $result;
	}

}