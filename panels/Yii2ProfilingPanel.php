<?php

/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 */
class Yii2ProfilingPanel extends Yii2DebugPanel
{
	public function getName()
	{
		return 'Profiling';
	}

	public function getSummary()
	{
		$memory = sprintf('%.1f MB', $this->data['memory'] / 1048576);
		$time = number_format($this->data['time'] * 1000) . ' ms';
		$url = $this->getUrl();

		return <<<HTML
<div class="yii2-debug-toolbar-block">
	<a href="$url" title="Total request processing time was $time">Time <span class="label">$time</span></a>
</div>
<div class="yii2-debug-toolbar-block">
	<a href="$url" title="Peak memory consumption">Memory <span class="label">$memory</span></a>
</div>
HTML;
	}

	public function getDetail()
	{
		$messages = $this->data['messages'];
		$timings = array();
		$stack = array();
		foreach ($messages as $i => $log) {
			list($token, $level, $category, $timestamp) = $log;
			$log[4] = $i;
			if (strpos($token, 'begin:') === 0) {
				$log[0] = $token = substr($token, 6);
				$stack[] = $log;
			} elseif (strpos($token, 'end:') === 0) {
				$log[0] = $token = substr($token, 4);
				if (($last = array_pop($stack)) !== null && $last[0] === $token) {
					$timings[$last[4]] = array(count($stack), $token, $category, $timestamp - $last[3]);
				}
			}
		}
		$now = microtime(true);
		while (($last = array_pop($stack)) !== null) {
			$delta = $now - $last[3];
			$timings[$last[4]] = array(count($stack), $last[0], $last[2], $delta);
		}
		ksort($timings);

		$rows = array();
		foreach ($timings as $timing) {
			$time = sprintf('%.1f ms', $timing[3] * 1000);
			$procedure = str_repeat('<span class="indent">→</span>', $timing[0]) . CHtml::encode($timing[1]);
			$category = CHtml::encode($timing[2]);
			$rows[] = "<tr><td style=\"width: 80px;\">$time</td><td style=\"width: 220px;\">$category</td><td>$procedure</td>";
		}
		$rows = implode("\n", $rows);

		$memory = sprintf('%.1f MB', $this->data['memory'] / 1048576);
		$time = number_format($this->data['time'] * 1000) . ' ms';

		return <<<HTML
<p>Total processing time: <b>$time</b>; Peak memory: <b>$memory</b>.</p>

<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
<thead>
<tr>
	<th style="width: 80px;">Time</th>
	<th style="width: 220px;">Category</th>
	<th>Procedure</th>
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
		$messages = Yii::getLogger()->getLogs(CLogger::LEVEL_PROFILE);
		return array(
			'memory' => memory_get_peak_usage(),
			'time' => microtime(true) - YII_BEGIN_TIME,
			'messages' => $messages,
		);
	}
}