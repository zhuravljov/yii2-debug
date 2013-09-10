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
	 * @var array список пользователей которым разрешен доступ к панели
	 */
	public $allowedUsers = array('*');
	/**
	 * @var array список ip и масок, которым разрешен доступ к панели
	 */
	public $allowedIPs = array('127.0.0.1', '::1');
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
	 * @var bool подсветка кода на страницах с отладочной информацией
	 */
	public $highlightCode = true;

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
			$config['id'] = $id;
			$config['tag'] = $this->getTag();
			$config['component'] = $this;
			if (!isset($config['highlightCode'])) $config['highlightCode'] = $this->highlightCode;
			$panels[$id] = Yii::createComponent($config);
		}
		$this->panels = $panels;

		Yii::app()->setModules(array_merge(Yii::app()->getModules(), array(
			$this->moduleId => array(
				'class' => 'Yii2DebugModule',
				'component' => $this,
			),
		)));

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
	public function corePanels()
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

	/**
	 * Регистрация скриптов для загрузки дебаг-панели
	 */
	public function initToolbar()
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

		$indexFile = "$path/index.json";
		$manifest = array();
		if (is_file($indexFile)) {
			$manifest = json_decode(file_get_contents($indexFile), true);
		}
		$request = Yii::app()->getRequest();
		$manifest[$this->getTag()] = $summary = array(
			'tag' => $this->getTag(),
			'url' => $request->getHostInfo() . $request->getUrl(),
			'ajax' => $request->getIsAjaxRequest(),
			'method' => $request->getRequestType(),
			'ip' => $request->getUserHostAddress(),
			'time' => time(),
		);
		$this->resizeHistory($manifest);

		$dataFile = "$path/{$this->getTag()}.json";
		$data = array();
		foreach ($this->panels as $panel) {
			$data[$panel->id] = $panel->save();
			if (isset($panel->filterData)) {
				$data[$panel->id] = $panel->evaluateExpression(
					$panel->filterData,
					array('data' => $data[$panel->id])
				);
			}
			$panel->load($data[$panel->id]);
		}
		$data['summary'] = $summary;

		file_put_contents($dataFile, json_encode($data));
		file_put_contents($indexFile, json_encode($manifest));
	}

	/**
	 * Удаление ранее сохраненных логов когда общее их кол-во больше historySize
	 * @param $manifest
	 */
	protected function resizeHistory(&$manifest)
	{
		if (count($manifest) > $this->historySize + 10) {
			$path = $this->logPath;
			$n = count($manifest) - $this->historySize;
			foreach (array_keys($manifest) as $tag) {
				@unlink("$path/$tag.json");
				unset($manifest[$tag]);
				if (--$n <= 0) break;
			}
		}
	}

	/**
	 * Проверка доступа
	 * @return bool
	 */
	public function checkAccess()
	{
		$user = Yii::app()->user->name;
		foreach ($this->allowedUsers as $filter) {
			if ($filter === '*' || $filter === $user) {
				return true;
			}
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
}
