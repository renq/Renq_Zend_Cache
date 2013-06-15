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

	public function testDsn()
	{
		$dsn = 'sqlite:///tmp/filename.sql';
		$options = new SimqelDatabaseOptions(array(
			'dsn' => $dsn
		));
		$this->assertEquals($dsn, $options->getDsn());
	}

	public function testSetItem()
	{

	}

}

