<?php

/**
 * Основной компонент для подключения отладочной панели
 *
 * @property string $tag
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 */
class Yii2Debug extends CApplicationComponent
{
	/**
	 * @var array список ip и масок, которым разрешен доступ к панели
	 */
	public $allowedIPs = array('127.0.0.1', '::1');
	/**
	 * @var null|string|callback дополнительное условие доступа к панели
	 */
	public $accessExpression;
	/**
	 * @var array|Yii2DebugPanel[]
	 */
	public $panels = array();
	/**
	 * @var string путь для записи логов. По умолчанию /runtime/debug
	 */
	public $logPath;
	/**
	 * @var int максимальное кол-во логов
	 */
	public $historySize = 50;
	/**
	 * @var bool
	 */
	public $enabled = true;
	/**
	 * @var string id модуля для просмотра отладочной информации
	 */
	public $moduleId = 'debug';
	/**
	 * @var bool использование внутренних url-правил
	 */
	public $internalUrls = true;
		/**
	 * @var bool подсветка кода на страницах с отладочной информацией
	 */
	public $highlightCode = true;
	/**
	 * @var bool показывать или нет страницу с конфигурацией приложения
	 */
	public $showConfig = false;
	/**
	 * @var array список опций значения которых необходимо скрывать при выводе
	 * на страницу с конфигурацией приложения.
	 */
	public $hiddenConfigOptions = array(
		'components/db/username',
		'components/db/password',
	);

	private $_tag;

	/**
	 * Генерируется уникальная метка страницы, подключается модуль просмотра,
	 * устанавливается обработчик для сбора отладочной информации, регистрируются
	 * скрипты для вывода дебаг-панели
	 */
	public function init()
	{
		parent::init();
		if (!$this->enabled) return;

		Yii::setPathOfAlias('yii2-debug', dirname(__FILE__));
		Yii::app()->setImport(array(
			'yii2-debug.*',
			'yii2-debug.panels.*',
		));

		if ($this->logPath === null) {
			$this->logPath = Yii::app()->getRuntimePath() . '/debug';
		}

		$panels = array();
		foreach (CMap::mergeArray($this->corePanels(), $this->panels) as $id => $config) {
			if (!isset($config['highlightCode'])) $config['highlightCode'] = $this->highlightCode;
			$panels[$id] = Yii::createComponent($config, $this, $id);
		}
		$this->panels = $panels;

		Yii::app()->setModules(array(
			$this->moduleId => array(
				'class' => 'Yii2DebugModule',
				'owner' => $this,
			),
		));

		if ($this->internalUrls && (Yii::app()->getUrlManager()->urlFormat == 'path')) {
			$rules = array();
			foreach ($this->coreUrlRules() as $key => $value) {
				$rules[$this->moduleId . '/' . $key] = $this->moduleId . '/' . $value;
			}
			Yii::app()->getUrlManager()->addRules($rules, false);
		}

		Yii::app()->attachEventHandler('onEndRequest', array($this, 'onEndRequest'));
		$this->initToolbar();
	}

	/**
	 * @return string метка текущей страницы
	 */
	public function getTag()
	{
		if ($this->_tag === null) $this->_tag = uniqid();
		return $this->_tag;
	}

	/**
	 * @return array страницы по умолчанию
	 */
	protected function corePanels()
	{
		return array(
			'config' => array(
				'class' => 'Yii2ConfigPanel',
			),
			'request' => array(
				'class' => 'Yii2RequestPanel',
			),
			'log' => array(
				'class' => 'Yii2LogPanel',
			),
			'profiling' => array(
				'class' => 'Yii2ProfilingPanel',
			),
			'db' => array(
				'class' => 'Yii2DbPanel',
			),
		);
	}

	protected function coreUrlRules()
	{
		return array(
			'' => 'default/index',
			'<tag:[0-9a-f]+>/<action:toolbar|explain>' => 'default/<action>',
			'<tag:[0-9a-f]+>/<panel:\w+>' => 'default/view',
			'<tag:[0-9a-f]+>' => 'default/view',
			'<action:\w+>' => 'default/<action>',
		);
	}

	/**
	 * Регистрация скриптов для загрузки дебаг-панели
	 */
	protected function initToolbar()
	{
		if (!$this->checkAccess()) return;
		$assetsUrl = CHtml::asset(dirname(__FILE__) . '/assets');
		/* @var CClientScript $cs */
		$cs = Yii::app()->getClientScript();
		$cs->registerCoreScript('jquery');
		$url = Yii::app()->createUrl($this->moduleId . '/default/toolbar', array('tag' => $this->getTag()));
		$cs->registerScript(__CLASS__ . '#toolbar', <<<JS
(function($){
	$('<div>').appendTo('body').load('$url', function(){
		if (window.localStorage && localStorage.getItem('yii2-debug-toolbar') == 'minimized') {
			$('#yii2-debug-toolbar').hide();
			$('#yii2-debug-toolbar-min').show();
		} else {
			$('#yii2-debug-toolbar-min').hide();
			$('#yii2-debug-toolbar').show();
		}
		$('#yii2-debug-toolbar .yii2-debug-toolbar-toggler').click(function(){
			$('#yii2-debug-toolbar').hide();
			$('#yii2-debug-toolbar-min').show();
			if (window.localStorage) {
				localStorage.setItem('yii2-debug-toolbar', 'minimized');
			}
		});
		$('#yii2-debug-toolbar-min .yii2-debug-toolbar-toggler').click(function(){
			$('#yii2-debug-toolbar-min').hide();
			$('#yii2-debug-toolbar').show();
			if (window.localStorage) {
				localStorage.setItem('yii2-debug-toolbar', 'maximized');
			}
		});
	});
})(jQuery);
JS
		);
	}

	/**
	 * @param CEvent $event
	 */
	protected function onEndRequest($event)
	{
		$this->processDebug();
	}

	/**
	 * Запись отладочной информации
	 */
	protected function processDebug()
	{
		$path = $this->logPath;
		if (!is_dir($path)) mkdir($path);

		// Конвертация данных из json в serialize
		if (file_exists("$path/index.json")) {
			foreach (glob("$path/*.json") as $jsonFile) {
				$data = json_decode(file_get_contents($jsonFile), true);
				$dataFile = substr($jsonFile, -4) . 'data';
				file_put_contents($dataFile, serialize($data));
				@unlink($jsonFile);
			}
		}

		$indexFile = "$path/index.data";
		$manifest = array();
		if (is_file($indexFile)) {
			$manifest = unserialize(file_get_contents($indexFile));
		}

		$data = array();
		foreach ($this->panels as $panel) {
			$data[$panel->getId()] = $panel->save();
			if (isset($panel->filterData)) {
				$data[$panel->getId()] = $panel->evaluateExpression(
					$panel->filterData,
					array('data' => $data[$panel->getId()])
				);
			}
			$panel->load($data[$panel->getId()]);
		}

		$statusCode = null;
		if (isset($this->panels['request']) && isset($this->panels['request']->data['statusCode'])) {
			$statusCode = $this->panels['request']->data['statusCode'];
		}

		$request = Yii::app()->getRequest();
		$manifest[$this->getTag()] = $data['summary'] = array(
			'tag' => $this->getTag(),
			'url' => $request->getHostInfo() . $request->getUrl(),
			'ajax' => $request->getIsAjaxRequest(),
			'method' => $request->getRequestType(),
			'code' => $statusCode,
			'ip' => $request->getUserHostAddress(),
			'time' => time(),
		);
		$this->resizeHistory($manifest);

		file_put_contents("$path/{$this->getTag()}.data", serialize($data));
		file_put_contents($indexFile, serialize($manifest));
	}

	/**
	 * Удаление ранее сохраненных логов когда общее их кол-во больше historySize
	 * @param $manifest
	 */
	protected function resizeHistory(&$manifest)
	{
		$tags = array_keys($manifest);
		$count = 0;
		foreach ($tags as $tag) {
			if (!$this->getLock($tag)) $count++;
		}
		if ($count > $this->historySize + 10) {
			$path = $this->logPath;
			$n = $count - $this->historySize;
			foreach ($tags as $tag) {
				if (!$this->getLock($tag)) {
					@unlink("$path/$tag.data");
					unset($manifest[$tag]);
					if (--$n <= 0) break;
				}
			}
		}
	}

	/**
	 * Проверка доступа
	 * @return bool
	 */
	public function checkAccess()
	{
		if (
			$this->accessExpression !== null &&
			!$this->evaluateExpression($this->accessExpression)
		) {
			return false;
		}
		$ip = Yii::app()->getRequest()->getUserHostAddress();
		foreach ($this->allowedIPs as $filter) {
			if (
				$filter === '*' || $filter === $ip || (
					($pos = strpos($filter, '*')) !== false &&
					!strncmp($ip, $filter, $pos)
				)
			) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Дамп переменной
	 * @param mixed $data
	 */
	public static function dump($data)
	{
		Yii::log(serialize($data), CLogger::LEVEL_INFO, Yii2LogPanel::CATEGORY_DUMP);
	}

	/**
	 * @var
	 */
	private $_locks;

	/**
	 * @param string $tag
	 * @return bool
	 */
	public function getLock($tag)
	{
		if ($this->_locks === null) {
			$locksFile = $this->logPath . '/locks.data';
			if (is_file($locksFile)) {
				$this->_locks = array_flip(unserialize(file_get_contents($locksFile)));
			} else {
				$this->_locks = array();
			}
		}
		return isset($this->_locks[$tag]);
	}

	/**
	 * @param string $tag
	 * @param bool $value
	 */
	public function setLock($tag, $value)
	{
		$value = !!$value;
		if ($this->getLock($tag) !== $value) {
			if ($value) {
				$this->_locks[$tag] = true;
			} else {
				unset($this->_locks[$tag]);
			}
			$locksFile = $this->logPath . '/locks.data';
			file_put_contents($locksFile, serialize(array_keys($this->_locks)));
		}
	}

	/**
	 * Каскадное преобразование смешанных данных в массив
	 * @param mixed $data
	 * @return array
	 */
	public static function prepareData($data)
	{
		static $parents = array();

		$result = array();
		if (is_array($data) || $data instanceof CMap) {
			foreach ($data as $key => $value) {
				$result[$key] = static::prepareData($value);
			}
		} elseif (is_object($data)) {
			if (!in_array($data, $parents, true)) {
				array_push($parents, $data);
				$result['class'] = get_class($data);
				if ($data instanceof CActiveRecord) {
					foreach ($data->attributes as $field => $value) {
						$result[$field] = $value;
					}
				}
				foreach (get_object_vars($data) as $key => $value) {
					$result[$key] = static::prepareData($value);
				}
				array_pop($parents);
			} else {
				$result = get_class($data) . '()';
			}
		} else {
			$result = $data;
		}
		return $result;
	}
}