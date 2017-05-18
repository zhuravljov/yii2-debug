<?php

class Yii2DebugStorage implements Yii2DebugStorageInterface
{
	/**
	 * @var Yii2Debug
	 */
	protected $owner;
	/**
	 * @var string
	 */
	protected $logPath;
	/**
	 * @var array
	 */
	protected $locks;
	/**
	 * @var array
	 */
	protected $manifest;

	/**
	 * Yii2DebugStorage constructor.
	 * @param $owner
	 */
	public function __construct($owner)
	{
		$this->owner = $owner;
		$this->logPath = $owner->logPath;
	}

	/**
	 * @param string $tag
	 * @param array $data
	 */
	public function saveTag($tag, $data)
	{
		if (!is_dir($this->logPath)) {
			mkdir($this->logPath);
		}

		file_put_contents($this->getTagFilePath($tag), serialize($data));
	}

	/**
	 * @param string $tag
	 * @return string
	 */
	protected function getTagFilePath($tag)
	{
		return "{$this->logPath}/$tag.data";
	}

	/**
	 * @return string
	 */
	protected function getManifestFilePath()
	{
		return "{$this->logPath}/index.data";
	}

	/**
	 * @param string $tag
	 * @param int $maxRetry
	 * @return array
	 */
	public function loadTag($tag, $maxRetry = 0)
	{
		for ($retry = 0; $retry <= $maxRetry; ++$retry) {
			$manifest = $this->getManifest($retry > 0);
			if (isset($manifest[$tag])) {
				return unserialize(file_get_contents($this->getTagFilePath($tag)));
			}
			sleep(1);
		}

		return array();
	}

	/**
	 * Updates index file with summary log data
	 *
	 * @param string $tag
	 * @param array $summary summary log data
	 * @throws Exception
	 */
	public function addToManifest($tag, $summary)
	{
		$indexFile = $this->getManifestFilePath();
		touch($indexFile);
		if (($fp = @fopen($indexFile, 'rb+')) === false) {
			throw new Exception("Unable to open debug data index file: $indexFile");
		}
		@flock($fp, LOCK_EX);
		$manifest = '';
		while (($buffer = fgets($fp)) !== false) {
			$manifest .= $buffer;
		}
		if (empty($manifest) || !feof($fp)) {
			// error while reading index data, ignore and create new
			$manifest = array();
		} else {
			$manifest = unserialize($manifest);
		}

		$manifest[$tag] = $summary;
		$this->resizeHistory($manifest);

		ftruncate($fp, 0);
		rewind($fp);
		fwrite($fp, serialize($manifest));

		@flock($fp, LOCK_UN);
		@fclose($fp);
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
			if (!$this->getLock($tag)) {
				$count++;
			}
		}
		if ($count > $this->owner->historySize + 10) {
			$n = $count - $this->owner->historySize;
			foreach ($tags as $tag) {
				if (!$this->getLock($tag)) {
					@unlink($this->getTagFilePath($tag));
					unset($manifest[$tag]);
					if (--$n <= 0) {
						break;
					}
				}
			}
		}
	}

	/**
	 * @return string
	 */
	protected function getLockFilePath()
	{
		return $this->logPath . '/locks.data';
	}

	/**
	 * @param string $tag
	 * @return bool
	 */
	public function getLock($tag)
	{
		if ($this->locks === null) {
			$locksFile = $this->getLockFilePath();
			$this->locks = array();
			if (is_file($locksFile)) {
				$this->locks = array_flip(unserialize(file_get_contents($locksFile)));
			}
		}
		return isset($this->locks[$tag]);
	}

	/**
	 * @param string $tag
	 * @param bool $value
	 */
	public function setLock($tag, $value)
	{
		$value = (bool)$value;
		if ($this->getLock($tag) !== $value) {
			if ($value) {
				$this->locks[$tag] = true;
			} else {
				unset($this->locks[$tag]);
			}
			file_put_contents($this->getLockFilePath(), serialize(array_keys($this->locks)));
		}
	}

	public function getManifest($forceReload = false)
	{
		if ($this->manifest === null || $forceReload) {
			if ($forceReload) {
				clearstatcache();
			}
			$indexFile = $this->getManifestFilePath();
			$content = '';
			if (($fp = @fopen($indexFile, 'rb')) !== false) {
				@flock($fp, LOCK_SH);
				$content = fread($fp, filesize($indexFile));
				@flock($fp, LOCK_UN);
				fclose($fp);
			}

			$this->manifest = array();
			if ($content !== '') {
				$this->manifest = array_reverse(unserialize($content), true);
			}
		}

		return $this->manifest;
	}
}
