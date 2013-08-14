<?php

/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 */
class Yii2LogPanel extends Yii2DebugPanel
{
	public function getName()
	{
		return 'Logs';
	}

	public function getSummary()
	{
		$count = count($this->data['messages']);
		$errorCount = 0;
		$warningCount = 0;
		foreach ($this->data['messages'] as $log) {
			$level = $log[1];
			if ($level == CLogger::LEVEL_ERROR) $errorCount++;
			elseif ($level == CLogger::LEVEL_WARNING) $warningCount++;
		}

		$output = array('<span class="label">' . count($this->data['messages']) . '</span>');
		$title = 'Logged ' . count($this->data['messages']) . ' messages';
		if ($errorCount) {
			$output[] = '<span class="label label-important">' . $errorCount . '</span>';
			$title .= ", $errorCount errors";
		}
		if ($warningCount) {
			$output[] = '<span class="label label-warning">' . $warningCount . '</span>';
			$title .= ", $warningCount warnings";
		}
		$html = implode('&nbsp;', $output);
		$url = $this->getUrl();
		return <<<HTML
<div class="yii2-debug-toolbar-block">
	<a href="$url" title="$title">Log $html</a>
</div>
HTML;
	}

	public function getDetail()
	{
		$rows = array();
		foreach ($this->data['messages'] as $log) {
			list ($message, $level, $category, $time) = $log;
			$time = date('H:i:s.', $time) . sprintf('%03d', (int)(($time - (int)$time) * 1000));
			$message = nl2br(CHtml::encode($message));
			/*if (!empty($traces)) {
				$message .= Html::ul($traces, array(
					'class' => 'trace',
					'item' => function ($trace) {
						return "<li>{$trace['file']}({$trace['line']})</li>";
					},
				));
			}*/
			if ($level == CLogger::LEVEL_ERROR) {
				$class = ' class="error"';
			} elseif ($level == CLogger::LEVEL_WARNING) {
				$class = ' class="warning"';
			} elseif ($level == CLogger::LEVEL_INFO) {
				$class = ' class="info"';
			} else {
				$class = '';
			}
			$rows[] = "<tr$class><td style=\"width: 100px;\">$time</td><td style=\"width: 100px;\">$level</td><td style=\"width: 250px;\">$category</td><td><div style=\"overflow:auto\">$message</div></td></tr>";
		}
		$rows = implode("\n", $rows);
		return <<<HTML
<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
<thead>
<tr>
	<th style="width: 100px;">Time</th>
	<th style="width: 65px;">Level</th>
	<th style="width: 250px;">Category</th>
	<th>Message</th>
</tr>
</thead>
<tbody>
$rows
</tbody>
</table>
HTML;
	}

	public function save()
	{
		$messages = Yii::getLogger()->getLogs(implode(',', array(
			CLogger::LEVEL_ERROR,
			CLogger::LEVEL_INFO,
			CLogger::LEVEL_WARNING,
			CLogger::LEVEL_TRACE,
		)));
		return array(
			'messages' => $messages,
		);
	}
}