<?php

/**
 * Yii2DebugPanel is a base class for debugger panel. It defines how data should be collected,
 * what should be dispalyed at debug toolbar and on debugger details view.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 */
class Yii2DebugPanel extends CComponent
{
	/**
	 * @var string
	 */
	public $id;
	/**
	 * @var string
	 */
	public $tag;
	/**
	 * @var Yii2Debug
	 */
	public $component;
	/**
	 * @var array
	 */
	public $data;
	/**
	 * @var bool
	 */
	public $highlightCode;

	/**
	 * @return string name of the panel
	 */
	public function getName()
	{
		return '';
	}

	/**
	 * @return string content that is displayed at debug toolbar
	 */
	public function getSummary()
	{
		return '';
	}

	/**
	 * @return string content that is displayed in debugger detail view
	 */
	public function getDetail()
	{
		return '';
	}

	/**
	 * Saves data to be later used in debugger detail view.
	 * This method is called on every page where debugger is enabled.
	 *
	 * @return mixed data to be saved
	 */
	public function save()
	{
		return null;
	}

	public function load($data)
	{
		$this->data = $data;
	}

	/**
	 * @return string URL pointing to panel detail view
	 */
	public function getUrl()
	{
		return Yii::app()->createUrl($this->component->moduleId .  '/default/view', array(
			'panel' => $this->id,
			'tag' => $this->tag,
		));
	}

	/**
	 * @param string $caption
	 * @param array $values
	 * @return string
	 */
	protected function renderDetail($caption, $values)
	{
		if (empty($values)) {
			return "<h3>$caption</h3>\n<p>Empty.</p>";
		}
		$rows = '';
		foreach ($values as $name => $value) {
			if (is_string($value)) {
				$value = CHtml::encode($value);
			} elseif ($this->highlightCode) {
				$value = $this->highlightPhp(var_export($value, true));
			} else {
				$value = CHtml::encode(var_export($value, true));
			}
			$rows .= '<tr><th style="width:300px;overflow:auto;">'
				. CHtml::encode($name)
				. '</th><td><div style="overflow:auto">'
				. $value
				. '</div></td></tr>';
		}

		return <<<HTML
<h3>$caption</h3>
<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
<thead><tr><th style="width: 300px;">Name</th><th>Value</th></tr></thead>
<tbody>
$rows
</tbody>
</table>
HTML;
	}

	/**
	 * @var CTextHighlighter
	 */
	private $_hl;

	/**
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

	/**
	 * @param array $items
	 * @return string
	 */
	protected function renderTabs($items)
	{
		static $counter = 0;
		$counter++;
		$id = "tabs$counter";

		$tabs = '';
		foreach ($items as $num => $item) {
			$tabs .= CHtml::tag('li', array(
					'class' => isset($item['active']) && $item['active'] ? 'active' : ''
				), CHtml::link($item['label'], "#$id-tab$num", array('data-toggle' => 'tab'))
			);
		}

		$details = '';
		foreach ($items as $num => $item) {
			$details .= CHtml::tag('div', array(
					'id' => "$id-tab$num",
					'class' => 'tab-pane' . (isset($item['active']) && $item['active'] ? ' active' : ''),
				), $item['content']
			);
		}

		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$id, "jQuery('$id').tab();");

		return <<<HTML
<ul id="tabs{$counter}" class="nav nav-tabs">$tabs</ul>
<div class="tab-content">$details</div>
HTML;
	}
}