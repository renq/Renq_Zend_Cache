<?php

namespace Renq\Zend\Cache\Storage\Adapter;

use Zend\Cache\Storage\AvailableSpaceCapableInterface;
use Zend\Cache\Storage\ClearByNamespaceInterface;
use Zend\Cache\Storage\ClearByPrefixInterface;
use Zend\Cache\Storage\ClearExpiredInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\IterableInterface;
use Zend\Cache\Storage\OptimizableInterface;
use Zend\Cache\Storage\TaggableInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Simqel\Simqel;
use Zend\Stdlib\ErrorHandler;

class SimqelDatabase extends AbstractAdapter implements
	StorageInterface,
	//ClearByNamespaceInterface,
	//ClearByPrefixInterface,
	//ClearExpiredInterface,
	FlushableInterface,
	///IterableInterface,
	// OptimizableInterface,
	TaggableInterface
	// TotalSpaceCapableInterface
{

	private $_simqel;
	private $_prefix = 'cache_';

	public function __construct($options = null)
	{
		parent::__construct($options);
	}

	public function setOptions($options)
	{
		if (!$options instanceof SimqelDatabaseOptions) {
			$options = new SimqelDatabaseOptions($options);
		}

		$options = parent::setOptions($options);
		$this->_connect();
		return $options;
	}

	private function _connect()
	{
		$options = $this->getOptions();
		$this->_prefix = $options->getTablePrefix();
		ErrorHandler::start();
		$this->_simqel = Simqel::createByDSN($options->getDsn());
		try {
			$this->_simqel->get("SELECT * FROM {$this->_prefix}cache", array(), 1, 0);
		}
		catch (\Exception $e) {
			$this->createDatabase();
		}
		$err = ErrorHandler::stop();
	}

	protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
	{
		$result = $this->_simqel->value("SELECT value FROM {$this->_prefix}cache WHERE key = ?", array($normalizedKey));
		if ($result !== false) {
			$success = true;
			return $result;
		}
		else {
			$success = true;
			return null;
		}
	}

	protected function internalSetItem(& $normalizedKey, & $value)
	{
		$this->internalRemoveItem($normalizedKey);
		$params = array(
			'key' => $normalizedKey,
			'value' => $value,
		);
		$this->_simqel->save("{$this->_prefix}cache", $params);
	}

	protected function internalRemoveItem(& $normalizedKey)
	{
		$this->_simqel->delete("{$this->_prefix}cache", $normalizedKey, 'key');
	}

	public function flush()
	{
		$this->createDatabase();
	}

	public function setTags($key, array $tags)
	{
		foreach ($tags as $tag) {
			$this->_simqel->save("{$this->_prefix}tags", array(
				'tag' => $tag,
				'key' => $key
			));
		}
	}

	public function getTags($key)
	{
		return $this->_simqel->flat("SELECT tag FROM {$this->_prefix}tags WHERE key = ?", array($key));
	}

	/**
	 * Remove items matching given tags.
	 *
	 * If $disjunction only one of the given tags must match
	 * else all given tags must match.
	 *
	 * @param string[] $tags
	 * @param  bool  $disjunction
	 * @return bool
	*/
	public function clearByTags(array $tags, $disjunction = false)
	{
		if (empty($tags)) {
			return;
		}

		if ($disjunction) {
			$this->_simqel->query(
				"DELETE FROM {$this->_prefix}cache WHERE key IN (SELECT key FROM {$this->_prefix}tags WHERE tag IN ?)",
				array($tags)
			);
			$this->_simqel->query("DELETE FROM {$this->_prefix}tags WHERE tag IN ?", array($tags));
		}
		else {
			$count = count($tags);
			$keys = $this->_simqel->flat("
				SELECT c.key
				FROM {$this->_prefix}cache c
				JOIN {$this->_prefix}tags t ON c.key = t.key
				GROUP BY c.key
				HAVING COUNT(t.tag) == $count"
			);
			if (!empty($keys)) {
				$this->_simqel->query("DELETE FROM {$this->_prefix}cache WHERE key IN ?", array($keys));
				$this->_simqel->query("DELETE FROM {$this->_prefix}tags WHERE key IN ?", array($keys));
			}
		}
	}

	public function createDatabase()
	{
		$this->_simqel->query("DROP TABLE IF EXISTS {$this->_prefix}cache");
		$this->_simqel->query("
			CREATE TABLE {$this->_prefix}cache (
				key TEXT NOT NULL PRIMARY KEY,
				value TEXT
			)");
		$this->_simqel->query("DROP TABLE IF EXISTS {$this->_prefix}tags");
		$this->_simqel->query("
			CREATE TABLE {$this->_prefix}tags (
				tag TEXT NOT NULL,
				key TEXT NOT NULL
			)");

		$this->_simqel->query("DROP INDEX IF EXISTS \"tags_tag_index\"");
		$this->_simqel->query("CREATE INDEX \"tags_tag_index\" on {$this->_prefix}tags (tag ASC)");
		$this->_simqel->query("DROP INDEX IF EXISTS \"tags_key_index\"");
		$this->_simqel->query("CREATE INDEX \"tags_key_index\" on {$this->_prefix}tags (key ASC)");
	}

}
