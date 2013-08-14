<?php

/**
 * @property string $tag
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 */
class Yii2Debug extends CApplicationComponent
{
	/**
	 * @var array
	 */
	public $allowedIPs = array('127.0.0.1', '::1');
	/**
	 * @var array|Yii2DebugPanel[]
	 */
	public $panels = array();
	/**
	 * @var string
	 */
	public $logPath;
	/**
	 * @var int
	 */
	public $historySize = 50;
	/**
	 * @var bool
	 */
	public $enabled = true;
	/**
	 * @var string
	 */
	public $moduleId = 'debug';
	/**
	 * @var bool
	 */
	public $highlightCode = true;

	private $_tag;

	public function init()
	{
		parent::init();
		if (!$this->enabled) return;

		if ($this->logPath === null) {
			$this->logPath = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'debug';
		}

		Yii::setPathOfAlias('debug', __DIR__);
		Yii::import('debug.Yii2DebugModule');
		Yii::import('debug.Yii2DebugPanel');
		Yii::import('debug.panels.*');

		foreach (array_merge($this->corePanels(), $this->panels) as $id => $config) {
			$config['id'] = $id;
			$config['tag'] = $this->getTag();
			$config['component'] = $this;
			if (!isset($config['highlightCode'])) $config['highlightCode'] = $this->highlightCode;
			$this->panels[$id] = Yii::createComponent($config);
		}

		Yii::app()->setModules(array_merge(Yii::app()->getModules(), array(
			$this->moduleId => array(
				'class' => 'Yii2DebugModule',
				'component' => $this,
			),
		)));

		Yii::app()->attachEventHandler('onEndRequest', array($this, 'onEndRequest'));
		$this->initToolbar();
	}

	public function getTag()
	{
		if ($this->_tag === null) $this->_tag = uniqid();
		return $this->_tag;
	}

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

	public function initToolbar()
	{
		if (!$this->checkAccess()) return;
		$assetsUrl = CHtml::asset(__DIR__ . '/assets');
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

	protected function processDebug()
	{
		$path = $this->logPath;
		if (!is_dir($path)) mkdir($path);

		$indexFile = "$path/index.json";
		$manifest = array();
		if (is_file($indexFile)) $manifest = json_decode(file_get_contents($indexFile), true);
		$request = Yii::app()->getRequest();
		$manifest[$this->getTag()] = $summary = array(
			'tag' => $this->getTag(),
			'url' => $request->getHostInfo() . $request->getUrl(),
			'ajax' => $request->isAjaxRequest,
			'method' => isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET',
			'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
			'time' => time(),
		);
		$this->resizeHistory($manifest);

		$dataFile = "$path/{$this->getTag()}.json";
		$data = array();
		foreach ($this->panels as $panel) {
			$data[$panel->id] = $panel->save();
			$panel->load($data[$panel->id]);
		}
		$data['summary'] = $summary;

		file_put_contents($dataFile, json_encode($data));
		file_put_contents($indexFile, json_encode($manifest));

		$data['panels'] = $this->panels;
		//$this->renderInternal(__DIR__ . '/views/default/_panel.php', $data);
	}

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
	 * @return bool
	 */
	public function checkAccess()
	{
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
		foreach ($this->allowedIPs as $filter) {
			if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
				return true;
			}
		}
		return false;
	}
}