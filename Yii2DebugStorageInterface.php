<?php

interface Yii2DebugStorageInterface
{

	/**
	 * @param string $tag
	 * @param int $maxRetry
	 * @return array
	 */
	public function loadTag($tag, $maxRetry = 0);

	/**
	 * @param string $tag
	 * @param array $data
	 */
	public function saveTag($tag, $data);

	/**
	 * @param string $tag
	 * @return bool
	 */
	public function getLock($tag);

	/**
	 * @param string $tag
	 * @param bool $value
	 */
	public function setLock($tag, $value);

	/**
	 * @param bool $forceReload
	 * @return array
	 */
	public function getManifest($forceReload = false);

	/**
	 * Add tag summary to manifest
	 *
	 * @param string $tag
	 * @param array $summary summary log data
	 * @throws Exception
	 */
	public function addToManifest($tag, $summary);
}
