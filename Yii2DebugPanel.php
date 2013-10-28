<?php

/**
 * Yii2DebugPanel - базовый класс для страниц с отладочной информацией.
 * Он определяет как информация будет сохраняться и выводиться на просмотр.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 *
 * @property Yii2Debug $owner
 * @property string $id страницы
 * @property string $tag метка для просмотра информации
 * @property string $url
 */
class Yii2DebugPanel extends CComponent
{
	/**
	 * @var bool|null подсветка кода. По умолчанию Yii2Debug::$highlightCode
	 */
	public $highlightCode;
	/**
	 * @var callback функция для обработки данных панели перед сохранением
	 */
	public $filterData;
	/**
	 * @var Yii2Debug
	 */
	private $_owner;
	/**
	 * @var string id страницы
	 */
	private $_id;
	/**
	 * @var string tag метка для просмотра информации
	 */
	private $_tag;
	/**
	 * @var array массив отладочных данных
	 */
	private $_data;

	/**
	 * @return string название панели для вывода в меню
	 */
	public function getName()
	{
		return '';
	}

	/**
	 * @return string html-контент для вывода в дебаг-панель
	 */
	public function getSummary()
	{
		return '';
	}

	/**
	 * @return string html-контент для вывода на страницу
	 */
	public function getDetail()
	{
		return '';
	}

	/**
	 * Базовый метод для сбора отладочной информации
	 * @return mixed
	 */
	public function save()
	{
	}

	/**
	 * @param Yii2Debug $owner
	 * @param string $id
	 */
	public function __construct($owner, $id)
	{
		$this->_owner = $owner;
		$this->_id = $id;
		$this->_tag = $owner->getTag();
	}

	/**
	 * @return Yii2Debug
	 */
	public function getOwner()
	{
		return $this->_owner;
	}

	/**
	 * @return Yii2Debug
	 * @deprecated will removed in v1.2
	 */
	public function getComponent()
	{
		return $this->_owner;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @return string
	 */
	public function getTag()
	{
		return $this->_tag;
	}

	/**
	 * @return array
	 */
	protected function getData()
	{
		return $this->_data;
	}

	/**
	 * @return string URL страницы
	 */
	public function getUrl()
	{
		return Yii::app()->createUrl($this->getOwner()->moduleId .  '/default/view', array(
			'tag' => $this->getTag(),
			'panel' => $this->getId(),
		));
	}

	/**
	 * @param array $data
	 * @param null|string $tag
	 */
	public function load($data, $tag = null)
	{
		if ($tag) $this->_tag = $tag;
		$this->_data = $data;
	}

	/**
	 * Renders a view file
	 * @param string $_viewFile_ view file
	 * @param array $_data_ data to be extracted and made available to the view file
	 * @return string the rendering result
	 */
	public function render($_viewFile_, $_data_ = null)
	{
		if (is_array($_data_)) {
			extract($_data_);
		} else {
			$data = $_data_;
		}
		ob_start();
		ob_implicit_flush(false);
		require($_viewFile_);
		return ob_get_clean();
	}

	/**
	 * Рендер блока с массивом key-value
	 * @param string $caption
	 * @param array $values
	 * @return string
	 * @deprecated
	 */
	public function renderDetail($caption, $values)
	{
		return $this->render(dirname(__FILE__) . '/views/panels/_detail.php', array(
			'caption' => $caption,
			'values' => $values,
		));
	}

	/**
	 * Рендер панели с закладками
	 * @param array $items
	 * @return string
	 * @deprecated
	 */
	public function renderTabs($items)
	{
		static $counter = 0;
		return $this->render(dirname(__FILE__) . '/views/panels/_tabs.php', array(
			'id' => 'tabs' . ($counter++),
			'items' => $items,
		));
	}

	/**
	 * @var CTextHighlighter
	 */
	private $_hl;

	/**
	 * Подсветка php-кода
	 * @param string $code
	 * @return string
	 */
	protected function highlightPhp($code)
	{
		if ($this->_hl === null) {
			$this->_hl = Yii::createComponent(array(
				'class' => 'CTextHighlighter',
				'language' => 'php',
				'showLineNumbers' => false,
			));
		}
		$html = $this->_hl->highlight($code);
		return strip_tags($html, '<div>,<span>');
	}
}