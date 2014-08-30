<?php

/**
 * Application component of debug panel.
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
	 * @var array the list of IPs that are allowed to access this module.
	 * Each array element represents a single IP filter which can be either an IP address
	 * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
	 * The default value is `['127.0.0.1', '::1']`, which means the module can only be accessed
	 * by localhost.
	 */
	public $allowedIPs = array('127.0.0.1', '::1');
	/**
	 * @var null|string|callback Additional php expression for access evaluation.
	 */
	public $accessExpression;
	/**
	 * @var array|Yii2DebugPanel[] list debug panels. The array keys are the panel IDs, and values are the corresponding
	 * panel class names or configuration arrays. This will be merged with ::corePanels().
	 * You may reconfigure a core panel via this property by using the same panel ID.
	 * You may also disable a core panel by setting it to be false in this property.
	 */
	public $panels = array();
	/**
	 * @var string the directory storing the debugger data files. This can be specified using a path alias.
	 */
	public $logPath;
	/**
	 * @var integer the maximum number of debug data files to keep. If there are more files generated,
	 * the oldest ones will be removed.
	 */
	public $historySize = 50;
	/**
	 * @var bool enable/disable component in application.
	 */
	public $enabled = true;
	/**
	 * @var string module ID for viewing stored debug logs.
	 */
	public $moduleId = 'debug';
	/**
	 * @var bool use nice route rules in debug module.
	 */
	public $internalUrls = true;
		/**
	 * @var bool highlight code in debug logs.
	 */
	public $highlightCode = true;
	/**
	 * @var bool show brief application configuration.
	 */
	public $showConfig = false;
	/**
	 * @var array list of unsecure component options (like login, passwords, secret keys) that
	 * will be hidden in application configuration page.
	 */
	public $hiddenConfigOptions = array(
		'components/db/username',
		'components/db/password',
	);

	private $_tag;

	/**
	 * Panel initialization.
	 * Generate unique tag for page. Attach panels, log watcher. Register scripts for printing debug panel.
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
	 * @return string current page tag
	 */
	public function getTag()
	{
		if ($this->_tag === null) $this->_tag = uniqid();
		return $this->_tag;
	}

	/**
	 * @return array default panels
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
			'latest/<panel:\w+>' => 'default/view',
			'<action:\w+>' => 'default/<action>',
		);
	}

	/**
	 * Register debug panel scripts.
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
	 * Log processing routine.
	 */
	protected function processDebug()
	{
		$path = $this->logPath;
		if (!is_dir($path)) mkdir($path);

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
	 * Debug files rotation according to {@link ::$historySize}.
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
	 * Check access rights.
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
	 * Dump variable to debug log.
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
	 * Convert data to plain array in recursive manner.
	 * @param mixed $data
	 * @return array
	 */
	public static function prepareData($data)
	{
		static $parents = array();

		$result = array();
		if (is_array($data) || $data instanceof CMap) {
			foreach ($data as $key => $value) {
				$result[$key] = self::prepareData($value);
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
					$result[$key] = self::prepareData($value);
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