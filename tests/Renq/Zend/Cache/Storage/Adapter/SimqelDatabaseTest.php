<?php

namespace Tests\Renq\Zend\Cache\Storage\Adapter;

use Renq\Zend\Cache\Storage\Adapter\SimqelDatabase;
use Renq\Zend\Cache\Storage\Adapter\SimqelDatabaseOptions;

class SimqelDatabaseTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @return SimqelDatabase
	 */
	private function getCacheAdapter()
	{
		return new SimqelDatabase(new SimqelDatabaseOptions(array(
			'dsn' => DB_DSN,
		)));
	}

	public function testConstructor()
	{
		$this->getCacheAdapter();
	}

	public function testSetAndGetItem()
	{
		$cache = $this->getCacheAdapter();
		$cache->createDatabase();
		$cache->setItem('some_key', 'some data');
		$this->assertEquals('some data', $cache->getItem('some_key'));
	}

	public function testSetAndGetItemAFewTimes()
	{
		$cache = $this->getCacheAdapter();
		$cache->createDatabase();
		$cache->setItem('mykey', 'Palma de Mallorca, Barcelona');
		$this->assertEquals('Palma de Mallorca, Barcelona', $cache->getItem('mykey'));
		$cache->setItem('mykey', 'Madrid, Playa De Las Americas');
		$this->assertEquals('Madrid, Playa De Las Americas', $cache->getItem('mykey'));
	}

	public function testGetWithNoData()
	{
		$this->assertNull($this->getCacheAdapter()->getItem('nokey'));
	}

	public function testRemove()
	{
		$cache = $this->getCacheAdapter();
		$cache->createDatabase();
		$cache->removeItem('nokey');

		$cache->setItem('cities', 'Mexico City, New York, London');
		$this->assertNotNull($cache->getItem('cities'));
		$cache->removeItem('cities');
		$this->assertNull($cache->getItem('cities'));
	}

	public function testFlush()
	{
		$cache = $this->getCacheAdapter();
		$cache->createDatabase();
		$cache->setItem('frutas', 'Platano, Manzana, Fresa');
		$cache->flush();
		$this->assertNull($cache->getItem('frutas'));
	}

	public function testTags()
	{
		$cache = $this->getCacheAdapter();
		$cache->createDatabase();
		$cache->setItem('frutas', 'Platano, Manzana, Fresa');
		$tags = array('A', 'B', 'C');
		$cache->setTags('frutas', $tags);

		$readedTags = $cache->getTags('frutas');
		sort($readedTags);
		$this->assertEquals($tags, $readedTags);
	}

	public function testClearByTagsDisjunction()
	{
		$cache = $this->getCacheAdapter();
		$cache->createDatabase();
		$cache->setItem('lugares', 'Dormitorio, Plaza, Playa, Campo');
		$cache->setTags('lugares', array('A', 'B', 'C'));

		$cache->clearByTags(array('A'), true);
		$this->assertNull($cache->getItem('lugares'));
	}

	public function testClearByTagsNoDisjunction()
	{
		$cache = $this->getCacheAdapter();
		$cache->createDatabase();
		$cache->setItem('lugares', 'Dormitorio, Plaza, Playa, Campo');
		$cache->setTags('lugares', array('A', 'B', 'C'));

		$cache->clearByTags(array('A'), false);
		$this->assertNotNull($cache->getItem('lugares'));

		$cache->clearByTags(array('A', 'B'), false);
		$this->assertNotNull($cache->getItem('lugares'));

		$cache->clearByTags(array('A', 'B', 'C'), false);
		$this->assertNull($cache->getItem('lugares'));
	}

}

