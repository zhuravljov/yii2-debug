<?php

/**
 * @author kikimor <i@kikimor.ru>
 * @package Yii2Debug
 */
class Yii2DebugLogRoute extends CLogRoute
{
	/**
	 * @var array
	 */
	private $localLogs = array();

	/**
	 * @return array
	 */
	public function getLogs()
	{
		return $this->localLogs;
	}

	/**
	 * Save logs to local variable.
	 * @param array $logs list of log messages.
	 */
	protected function processLogs($logs)
	{
		$this->localLogs = array_merge($this->localLogs, $logs);
	}
}
