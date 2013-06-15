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

	public function __construct($options = null)
	{
		parent::__construct($options);
		$this->_connect();
	}

	private function _connect()
	{
		$options = $this->getOptions();
		ErrorHandler::start();
		$this->_simqel = Simqel::createByDSN($options->getDsn());
		$err = ErrorHandler::stop();
	}

	protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
	{
		$result = $this->_simqel->value("SELECT value FROM cache WHERE key = ?", array($normalizedKey));
		//var_dump($normalizedKey, $result);
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
		$this->_simqel->save('cache', $params);
	}

	protected function internalRemoveItem(& $normalizedKey)
	{
		$this->_simqel->delete('cache', $normalizedKey, 'key');
	}

	public function flush()
	{
		$this->createDatabase();
	}

	public function setTags($key, array $tags)
	{
		foreach ($tags as $tag) {
			$this->_simqel->save('tags', array(
				'tag' => $tag,
				'key' => $key
			));
		}
	}

	public function getTags($key)
	{
		return $this->_simqel->flat("SELECT tag FROM tags WHERE key = ?", array($key));
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
				"DELETE FROM cache WHERE key IN (SELECT key FROM tags WHERE tag IN ?)",
				array($tags)
			);
			$this->_simqel->query("DELETE FROM tags WHERE tag IN ?", array($tags));
		}
		else {
			$count = count($tags);
			$keys = $this->_simqel->flat("
				SELECT c.key
				FROM cache c
				JOIN tags t ON c.key = t.key
				GROUP BY c.key
				HAVING COUNT(t.tag) == $count"
			);
			if (!empty($keys)) {
				$this->_simqel->query("DELETE FROM cache WHERE key IN ?", array($keys));
				$this->_simqel->query("DELETE FROM tags WHERE key IN ?", array($keys));
			}
		}
	}

	public function createDatabase()
	{
		$this->_simqel->query('DROP TABLE IF EXISTS cache');
		$this->_simqel->query("
			CREATE TABLE cache (
				key TEXT NOT NULL PRIMARY KEY,
				value TEXT
			)");
		$this->_simqel->query('DROP TABLE IF EXISTS tags');
		$this->_simqel->query("
			CREATE TABLE tags (
				tag TEXT NOT NULL,
				key TEXT NOT NULL
			)");
		$this->_simqel->query('CREATE INDEX "tags_tag_index" on tags (tag ASC)');
		$this->_simqel->query('CREATE INDEX "tags_key_index" on tags (key ASC)');
	}

}
