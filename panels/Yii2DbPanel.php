<?php

/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 */
class Yii2DbPanel extends Yii2DebugPanel
{
	public function getName()
	{
		return 'Database';
	}

	public function getSummary()
	{
		$timings = $this->calculateTimings();
		$queryCount = count($timings);
		$queryTime = 0;
		foreach ($timings as $timing) {
			$queryTime += $timing[3];
		}
		$queryTime = number_format($queryTime * 1000) . ' ms';
		$url = $this->getUrl();
		$output = <<<HTML
<div class="yii2-debug-toolbar-block">
	<a href="$url" title="Executed $queryCount database queries which took $queryTime.">
		DB <span class="label">$queryCount</span> <span class="label">$queryTime</span>
	</a>
</div>
HTML;
		return $queryCount > 0 ? $output : '';
	}

	public function getDetail()
	{
		$queriesCount = count($this->calculateTimings());
		$connectionsCount = count($this->data['connections']);
		return $this->renderTabs(array(
			array(
				'label' => "Queries ($queriesCount)",
				'content' => $this->getQueriesDetail(),
				'active' => true,
			),
			array(
				'label' => "Connections ($connectionsCount)",
				'content' => $this->getConnectionsDetail(),
			)
		));
	}

	/**
	 * @return string html-контент закладки со списком sql-запросов
	 */
	protected function getQueriesDetail()
	{
		$timings = $this->calculateTimings();
		$rows = array();
		$num = 0;
		foreach ($timings as $timing) {
			$duration = sprintf('%.1f ms', $timing[3] * 1000);
			$procedure = $this->formatSql($timing[1]);
			if ($this->highlightCode) {
				$procedure = $this->highlightSql($procedure);
			} else {
				$procedure = CHtml::encode($procedure);
			}
			$rows[] = "<tr><td style=\"width: 80px;\">$duration</td><td>$procedure</td>";
		}
		$rows = implode("\n", $rows);
		return <<<HTML
<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
<thead>
<tr>
	<th style="width: 80px;">Time</th>
	<th>Query</th>
</tr>
</thead>
<tbody>
$rows
</tbody>
</table>
HTML;
	}

	/**
	 * @return string html-контент закладки с детальной информацией активных
	 * подключений к базам данных
	 */
	protected function getConnectionsDetail()
	{
		$content = '';
		foreach ($this->data['connections'] as $id => $connection) {
			$caption = "Component: $id ($connection[class])";
			unset($connection['class']);
			foreach (explode('  ', $connection['info']) as $line) {
				list($key, $value) = explode(': ', $line, 2);
				$connection[$key] = $value;
			}
			unset($connection['info']);
			$content .= $this->renderDetail($caption, $connection);
		}
		return $content;
	}

	private $_timings;

	/**
	 * Группировка времени выполнения sql-запросов
	 * @return array
	 */
	protected function calculateTimings()
	{
		if ($this->_timings !== null) {
			return $this->_timings;
		}
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
		return $this->_timings = $timings;
	}

	/**
	 * Выделение sql-запроса из лога и подстановка параметров
	 * @param string $message
	 * @return string
	 */
	protected function formatSql($message)
	{
		$sqlStart = strpos($message, '(') + 1;
		$sqlEnd = strrpos($message , ')');
		$sql = substr($message, $sqlStart, $sqlEnd - $sqlStart);
		if (strpos($sql, '. Bound with ') !== false) {
			list($query, $params) = explode('. Bound with ', $sql);
			$sql = strtr($query, $this->parseParamsSql($params));
		}
		return $sql;
	}

	/**
	 * Парсинг строки с параметрами
	 * @param string $params
	 * @return array key/value
	 */
	private function parseParamsSql($params)
	{
		$binds = array();
		$pos = 0;
		while (preg_match('/(\:[a-z0-9\.\_\-]+)\s*\=\s*/', $params, $m, PREG_OFFSET_CAPTURE, $pos)) {
			$start = $m[0][1] + strlen($m[0][0]);
			$key = $m[1][0];
			if (($params{$start} == '"') || ($params{$start} == "'")) {
				$quote = $params{$start};
				$pos = $start;
				while (($pos = strpos($params, $quote, $pos + 1)) !== false) {
					$slashes = 0;
					while ($params{$pos - $slashes - 1} == '\\') $slashes++;
					if ($slashes % 2 == 0) {
						$binds[$key] = substr($params, $start, $pos - $start + 1);
						$pos++;
						break;
					}
				}
			} elseif (($end = strpos($params, ',', $start + 1)) !== false) {
				$binds[$key] = substr($params, $start, $end - $start);
				$pos = $end + 1;
			} else {
				$binds[$key] = substr($params, $start, strlen($params) - $start);
				break;
			}
		}
		return $binds;
	}

	/**
	 * @var CTextHighlighter
	 */
	private $_hl;

	/**
	 * Подсветка sql-кода
	 * @param string $sql
	 * @return string
	 */
	protected function highlightSql($sql)
	{
		if ($this->_hl === null) {
			$this->_hl = Yii::createComponent(array(
				'class' => 'CTextHighlighter',
				'language' => 'sql',
				'showLineNumbers' => false,
			));
		}
		$html = $this->_hl->highlight($sql);
		return strip_tags($html, '<div>,<span>');
	}

	public function save()
	{
		$messages = Yii::getLogger()->getLogs(CLogger::LEVEL_PROFILE, 'system.db.CDbCommand.*');

		$connections = array();
		foreach (Yii::app()->getComponents() as $id => $component) {
			if ($component instanceof CDbConnection) {
				/* @var CDbConnection $component */
				$connections[$id] = array(
					'class' => get_class($component),
					'driver' => $component->getDriverName(),
					'server' => $component->getServerVersion(),
					'info' => $component->getServerInfo(),
				);
			}
		}

		return array(
			'messages' => $messages,
			'connections' => $connections,
		);
	}
}