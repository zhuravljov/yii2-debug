<?php

/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 *
 * @property Yii2Debug $owner
 */
class DefaultController extends CController
{
	public $layout = 'main';
	public $summary;

	/**
	 * @return Yii2Debug
	 */
	public function getOwner()
	{
		return $this->getModule()->owner;
	}

	/**
	 * @return Yii2Debug
	 * @deprecated will removed in 1.2
	 */
	public function getComponent()
	{
		return $this->getModule()->owner;
	}

	/**
	 * Общий список логов
	 */
	public function actionIndex()
	{
		$this->render('index', array(
			'manifest' => $this->getManifest(),
		));
	}

	/**
	 * Страница для просмотра отладочной информации
	 * @param null $tag сохраненного лога
	 * @param null $panel id страницы
	 */
	public function actionView($tag = null, $panel = null)
	{
		if ($tag === null) {
			$tags = array_keys($this->getManifest());
			$tag = reset($tags);
			$this->redirect(array('view', 'tag' => $tag, 'panel' => $panel));
		}
		$this->loadData($tag);
		if (isset($this->component->panels[$panel])) {
			$activePanel = $this->getOwner()->panels[$panel];
		} else {
			$activePanel = $this->getOwner()->panels['request'];
		}
		$this->render('view', array(
			'tag' => $tag,
			'summary' => $this->summary,
			'manifest' => $this->getManifest(),
			'panels' => $this->getOwner()->panels,
			'activePanel' => $activePanel,
		));
	}

	/**
	 * Блокировка/разблокировка лога от автоматического удаления
	 * @param string $tag
	 */
	public function actionLock($tag)
	{
		$lock = $this->getOwner()->getLock($tag);
		$this->getOwner()->setLock($tag, !$lock);
		echo !$lock;
	}

	/**
	 * @param string $tag
	 * @param int $num
	 * @param string $connection
	 * @throws CHttpException
	 * @throws Exception
	 */
	public function actionExplain($tag, $num, $connection)
	{
		$this->loadData($tag);

		$dbPanel = $this->getOwner()->panels['db'];
		if (!($dbPanel instanceof Yii2DbPanel)) {
			throw new Exception('Yii2DbPanel not found');
		}
		if (!$dbPanel->canExplain) {
			throw new CHttpException(403, 'Forbidden');
		}
		$message = $dbPanel->messageByNum($num);
		if ($message === null) {
			throw new Exception("Not found query by number $num");
		}
		$query = $dbPanel->formatSql($message, true);
		/* @var CDbConnection $db */
		$db = Yii::app()->getComponent($connection);

		if (!Yii::app()->request->isAjaxRequest) {
			$this->getOwner()->setLock($tag, true);
			$this->render('explain', array(
				'tag' => $tag,
				'summary' => $this->summary,
				'manifest' => $this->getManifest(),
				'panels' => $this->getOwner()->panels,
				'dbPanel' => $dbPanel,
				'connection' => $db,
				'procedure' => Yii2DbPanel::getExplainQuery($query, $db->driverName),
				'explainRows' => Yii2DbPanel::explain($query, $db),
			));
		} else {
			$this->renderPartial('_explain', array(
				'connection' => $db,
				'explainRows' => Yii2DbPanel::explain($query, $db),
			));
		}
	}

	/**
	 * Генерирует код дебаг-панели по ajax-запросу
	 * @param $tag
	 */
	public function actionToolbar($tag)
	{
		$this->loadData($tag);
		$this->renderPartial('toolbar', array(
			'tag' => $tag,
			'panels' => $this->getOwner()->panels,
		));
	}

	public function actionPhpinfo()
	{
		phpinfo();
	}

	private $_manifest;

	protected function getManifest()
	{
		if ($this->_manifest === null) {
			$path = $this->getOwner()->logPath;
			$indexFile = "$path/index.data";
			if (is_file($indexFile)) {
				$this->_manifest = array_reverse(unserialize(file_get_contents($indexFile)), true);
			} else {
				$this->_manifest = array();
			}
		}
		return $this->_manifest;
	}

	protected function loadData($tag)
	{
		$manifest = $this->getManifest();
		if (isset($manifest[$tag])) {
			$path = $this->getOwner()->logPath;
			$dataFile = "$path/$tag.data";
			$data = unserialize(file_get_contents($dataFile));
			foreach ($this->getOwner()->panels as $id => $panel) {
				if (isset($data[$id])) {
					$panel->load($data[$id], $tag);
				} else {
					// remove the panel since it has not received any data
					unset($this->getOwner()->panels[$id]);
				}
			}
			$this->summary = $data['summary'];
		} else {
			throw new CHttpException(404, "Unable to find debug data tagged with '$tag'.");
		}
	}

	public function actionConfig()
	{
		if (!$this->getOwner()->showConfig) {
			throw new CHttpException(403, 'Forbidden');
		}
		$components = array();
		foreach (Yii::app()->getComponents(false) as $id => $config) {
			try {
				$components[$id] = Yii::app()->getComponent($id);
			} catch (Exception $e) {
				assert(is_array($config));
				$components[$id] = array_merge($config, array(
					'_error_' => $e->getMessage(),
				));
			}
		}
		ksort($components);
		$modules = Yii::app()->modules;
		ksort($modules);
		$data = $this->hideConfigData(
			array(
				'app' => Yii2Debug::prepareData(Yii::app()),
				'components' => Yii2Debug::prepareData($components),
				'modules' => Yii2Debug::prepareData($modules),
				'params' => Yii2Debug::prepareData(Yii::app()->params),
			),
			$this->getOwner()->hiddenConfigOptions
		);
		$this->render('config', $data);
	}

	/**
	 * @param array $config
	 * @param array $options
	 * @return array
	 */
	private function hideConfigData($config, $options)
	{
		foreach ($options as $option) {
			$item = &$config;
			foreach (explode('/', $option) as $key) {
				if (is_array($item) && isset($item[$key])) {
					$item = &$item[$key];
				} else {
					unset($item);
					break;
				}
			}
			if (isset($item)) {
				$item = '**********';
				unset($item);
			}
		}
		return $config;
	}
}
