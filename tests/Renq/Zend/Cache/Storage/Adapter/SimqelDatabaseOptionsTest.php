<?php

namespace Tests\Renq\Zend\Cache\Storage\Adapter;

use Renq\Zend\Cache\Storage\Adapter\SimqelDatabaseOptions;

class SimqelDatabaseOptionsTest extends \PHPUnit_Framework_TestCase
{

	public function testBadMethod()
	{
		$this->setExpectedException('Zend\Stdlib\Exception\BadMethodCallException');
		$new = new SimqelDatabaseOptions(array(
		    'no_such_option' => 'test'
		));
	}

	public function testOptions()
	{
		$dsn = 'sqlite:///tmp/filename.sql';
		$options = new SimqelDatabaseOptions(array(
			'dsn' => $dsn,
			'table_prefix' => 'cache_',
		));
		$this->assertEquals($dsn, $options->getDsn());
	}

}

